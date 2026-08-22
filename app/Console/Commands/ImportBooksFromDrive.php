<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportBooksFromDrive extends Command
{
    protected $signature = 'books:import-drive
        {folder* : One or more Google Drive folder ids}
        {--download : Also fetch each PDF onto this server}
        {--min-free-gb=3 : Refuse to download once free disk would fall below this}
        {--dry-run : Report what would happen without writing anything}';

    protected $description = 'Import books from Google Drive folders organised into subject subfolders';

    private const API = 'https://www.googleapis.com/drive/v3/files';

    private const FOLDER_MIME = 'application/vnd.google-apps.folder';

    private int $created = 0;

    private int $skipped = 0;

    private int $failed = 0;

    public function handle(): int
    {
        $key = config('library.google_api_key');

        if (blank($key)) {
            $this->error('LIBRARY_GOOGLE_API_KEY is not set.');

            return self::FAILURE;
        }

        foreach ($this->argument('folder') as $root) {
            $this->line("Reading folder {$root}");

            foreach ($this->children($root, $key) as $entry) {
                if ($entry['mimeType'] !== self::FOLDER_MIME) {
                    // A PDF sitting loose in the root, with no subject folder.
                    $this->importFile($entry, null, $key);

                    continue;
                }

                $this->importCategory($entry, $key);
            }
        }

        $this->newLine();
        $this->info("Added {$this->created}, skipped {$this->skipped} already present, {$this->failed} failed.");

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function importCategory(array $folder, string $key): void
    {
        $name = $this->cleanTitle($folder['name']);

        $category = $this->option('dry-run')
            ? new Category(['name' => $name])
            : Category::firstOrCreate(
                ['drive_folder_id' => $folder['id']],
                ['name' => $name, 'sort_order' => $this->leadingNumber($folder['name']) ?? 0],
            );

        $this->line("  <info>{$name}</info>");

        // Books may sit directly in the subject folder or one level deeper.
        foreach ($this->children($folder['id'], $key) as $entry) {
            if ($entry['mimeType'] === self::FOLDER_MIME) {
                foreach ($this->children($entry['id'], $key) as $nested) {
                    if ($nested['mimeType'] !== self::FOLDER_MIME) {
                        $this->importFile($nested, $category, $key);
                    }
                }

                continue;
            }

            $this->importFile($entry, $category, $key);
        }
    }

    private function importFile(array $file, ?Category $category, string $key): void
    {
        if (! str_contains($file['mimeType'] ?? '', 'pdf')) {
            return;
        }

        if (Book::where('drive_file_id', $file['id'])->exists()) {
            $this->skipped++;

            return;
        }

        $title = $this->cleanTitle($file['name']);

        if ($this->option('dry-run')) {
            $this->created++;
            $this->line("    would add  {$title}");

            return;
        }

        $attributes = [
            'title' => $title,
            'category_id' => $category?->id,
            'drive_file_id' => $file['id'],
            'url' => "https://drive.google.com/file/d/{$file['id']}/view",
            'file_size' => isset($file['size']) ? (int) $file['size'] : null,
        ];

        if ($this->option('download') && ($stored = $this->download($file, $key))) {
            $attributes['file_path'] = $stored;
        }

        Book::create($attributes);
        $this->created++;
    }

    /**
     * Strip the ordering number the collection uses as a filename prefix or
     * suffix, and the extension: "50-أسس الكيمياء" and "ECG Mastering-٩" both
     * become the plain title.
     */
    private function cleanTitle(string $name): string
    {
        $digits = '0-9\x{0660}-\x{0669}\x{06F0}-\x{06F9}';

        $name = preg_replace('/\.pdf$/iu', '', $name);
        $name = preg_replace("/^[{$digits}]+\s*[-–—.]\s*/u", '', $name);
        $name = preg_replace("/\s*[-–—]\s*[{$digits}]+$/u", '', $name);

        return trim(preg_replace('/\s+/u', ' ', $name)) ?: $name;
    }

    private function leadingNumber(string $name): ?int
    {
        if (! preg_match('/^([0-9\x{0660}-\x{0669}]+)/u', $name, $m)) {
            return null;
        }

        // Arabic-Indic digits need folding to ASCII before casting.
        return (int) strtr($m[1], ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    }

    /**
     * @return array<int, array{id: string, name: string, mimeType: string, size?: string}>
     */
    private function children(string $folder, string $key): array
    {
        $out = [];
        $token = null;

        do {
            $response = Http::retry(3, 2000)->get(self::API, array_filter([
                'q' => "'{$folder}' in parents and trashed=false",
                'key' => $key,
                'fields' => 'nextPageToken,files(id,name,mimeType,size)',
                'pageSize' => 1000,
                'pageToken' => $token,
            ]));

            if ($response->failed()) {
                $this->error('  Drive API: '.($response->json('error.message') ?? $response->status()));
                $this->failed++;

                return $out;
            }

            $out = array_merge($out, $response->json('files', []));
            $token = $response->json('nextPageToken');
        } while ($token);

        return $out;
    }

    private function download(array $file, string $key): ?string
    {
        $free = disk_free_space(storage_path()) ?: 0;
        $floor = (float) $this->option('min-free-gb') * 1024 ** 3;

        // This server also hosts an unrelated site; filling the disk would
        // take that down too.
        if ($free - (int) ($file['size'] ?? 0) < $floor) {
            $this->warn('    stopping downloads: free disk would fall below the floor.');
            $this->failed++;

            return null;
        }

        $response = Http::timeout(600)->get(self::API."/{$file['id']}", ['alt' => 'media', 'key' => $key]);

        if ($response->failed()) {
            $this->warn("    could not download {$file['name']}");

            return null;
        }

        $path = Str::uuid()->toString().'.pdf';
        Storage::disk('books')->put($path, $response->body());

        return $path;
    }
}
