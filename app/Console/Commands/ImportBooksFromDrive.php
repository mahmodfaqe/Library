<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Department;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportBooksFromDrive extends Command
{
    protected $signature = 'books:import-drive
        {folder : The Google Drive folder id}
        {--department= : Attach every imported book to this department id}
        {--download : Fetch each PDF onto this server instead of only linking to it}
        {--dry-run : List what would be imported without writing anything}';

    protected $description = 'Create book records from the PDFs in a public Google Drive folder';

    private const API = 'https://www.googleapis.com/drive/v3/files';

    public function handle(): int
    {
        $key = config('library.google_api_key');

        if (blank($key)) {
            $this->error('LIBRARY_GOOGLE_API_KEY is not set. See the README.');

            return self::FAILURE;
        }

        $department = $this->option('department');

        if ($department && ! Department::whereKey($department)->exists()) {
            $this->error("No department with id {$department}.");

            return self::FAILURE;
        }

        $files = $this->listFolder($this->argument('folder'), $key);

        if ($files === null) {
            return self::FAILURE;
        }

        $this->info(count($files).' PDF(s) found.');

        $created = $skipped = 0;

        foreach ($files as $file) {
            $title = Str::of($file['name'])->replaceMatches('/\.pdf$/i', '')->trim()->value();

            if (Book::where('title', $title)->exists()) {
                $skipped++;
                $this->line("  skip   {$title}");

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  would add   {$title}");
                $created++;

                continue;
            }

            $attributes = [
                'title' => $title,
                'department_id' => $department ?: null,
                'url' => "https://drive.google.com/file/d/{$file['id']}/view",
            ];

            if ($this->option('download') && ($stored = $this->download($file, $key))) {
                $attributes['file_path'] = $stored['path'];
                $attributes['file_size'] = $stored['size'];
            }

            Book::create($attributes);
            $created++;
            $this->line("  added  {$title}");
        }

        $this->newLine();
        $this->info("Added {$created}, skipped {$skipped} already present.");

        return self::SUCCESS;
    }

    /**
     * Every PDF in the folder, following pagination.
     *
     * @return array<int, array{id: string, name: string, size?: string}>|null
     */
    private function listFolder(string $folder, string $key): ?array
    {
        $files = [];
        $pageToken = null;

        do {
            $response = Http::get(self::API, array_filter([
                'q' => "'{$folder}' in parents and mimeType='application/pdf' and trashed=false",
                'key' => $key,
                'fields' => 'nextPageToken,files(id,name,size)',
                'pageSize' => 200,
                'pageToken' => $pageToken,
            ]));

            if ($response->failed()) {
                $this->error('Drive API: '.($response->json('error.message') ?? $response->status()));

                return null;
            }

            $files = array_merge($files, $response->json('files', []));
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return $files;
    }

    /**
     * @param  array{id: string, name: string}  $file
     * @return array{path: string, size: int}|null
     */
    private function download(array $file, string $key): ?array
    {
        $response = Http::withOptions(['stream' => false])
            ->timeout(300)
            ->get(self::API."/{$file['id']}", ['alt' => 'media', 'key' => $key]);

        if ($response->failed()) {
            $this->warn("    could not download {$file['name']}: ".$response->status());

            return null;
        }

        $path = Str::uuid()->toString().'.pdf';
        Storage::disk('books')->put($path, $response->body());

        return ['path' => $path, 'size' => strlen($response->body())];
    }
}
