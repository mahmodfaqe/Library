<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Thesis;
use App\Support\DriveApi;
use App\Support\Zenodo;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DepositTheses extends Command
{
    protected $signature = 'theses:deposit
                            {--thesis= : One thesis, by id}
                            {--confirm : Actually deposit. Without this nothing is sent}
                            {--live : Deposit to Zenodo itself rather than the sandbox}
                            {--limit=0 : Stop after this many}';

    protected $description = 'Give published theses a DOI by depositing them with Zenodo';

    public function handle(): int
    {
        $zenodo = new Zenodo(live: (bool) $this->option('live'));

        if (! $zenodo->configured()) {
            $this->error($this->option('live')
                ? 'ZENODO_TOKEN is not set.'
                : 'ZENODO_SANDBOX_TOKEN is not set.');

            return self::FAILURE;
        }

        $theses = $this->targets();

        if ($theses->isEmpty()) {
            $this->info('There is nothing to deposit.');

            return self::SUCCESS;
        }

        $this->line('Depositing to '.$zenodo->base());
        $this->newLine();

        if (! $this->option('confirm')) {
            return $this->rehearse($theses);
        }

        if ($zenodo->isLive() && ! $this->reallyMeansIt($theses->count())) {
            return self::FAILURE;
        }

        $done = 0;

        foreach ($theses as $thesis) {
            if ($this->deposit($zenodo, $thesis)) {
                $done++;
            }
        }

        $this->newLine();
        $this->info("Deposited {$done} of {$theses->count()}.");

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, Thesis>
     */
    private function targets()
    {
        $query = Thesis::published()->whereNull('zenodo_id')->orderBy('id');

        if ($id = $this->option('thesis')) {
            $query = Thesis::where('id', $id);
        }

        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Say what would happen, and why anything is being left out. This is the
     * default because the alternative is permanent.
     */
    private function rehearse($theses): int
    {
        $rows = [];

        foreach ($theses as $thesis) {
            $refusal = Zenodo::refuse($thesis);

            $rows[] = [
                $thesis->id,
                mb_strimwidth($thesis->title_en ?: $thesis->title, 0, 46, '…'),
                $thesis->license ?: '—',
                $refusal ? '✗ '.$refusal : '✓ would deposit',
            ];
        }

        $this->table(['id', 'Thesis', 'Licence', ''], $rows);
        $this->warn('Nothing was sent. Add --confirm to deposit.');

        return self::SUCCESS;
    }

    /**
     * A published Zenodo record cannot be deleted. Ever. So the live run says
     * so, and waits.
     */
    private function reallyMeansIt(int $count): bool
    {
        $this->newLine();
        $this->warn('This deposits to Zenodo itself, not the sandbox.');
        $this->warn('A published record CANNOT be deleted afterwards, by anyone.');
        $this->warn("It will mint a permanent DOI for {$count} thesis/theses.");
        $this->newLine();

        return $this->confirm('Are you sure?', false);
    }

    private function deposit(Zenodo $zenodo, Thesis $thesis): bool
    {
        $name = mb_strimwidth($thesis->title_en ?: $thesis->title, 0, 50, '…');

        if ($refusal = Zenodo::refuse($thesis)) {
            $this->line("  skipped  {$name} — {$refusal}");

            return false;
        }

        $file = null;
        $draft = null;

        try {
            $file = $this->fetchFile($thesis);

            if ($file === null) {
                $this->line("  skipped  {$name} — there is no file to deposit");

                return false;
            }

            $draft = $zenodo->begin();
            $zenodo->attach($draft['bucket'], $file, $this->filename($thesis));
            $zenodo->describe($draft['id'], Zenodo::metadata($thesis));
            $published = $zenodo->publish($draft['id']);

            $thesis->forceFill([
                'doi' => $published['doi'],
                'zenodo_id' => $draft['id'],
                'zenodo_url' => $published['url'],
                'deposited_at' => now(),
            ])->save();

            Activity::record('thesis.deposited', $thesis->title.' → '.$published['doi']);

            $this->line("  <info>done</info>     {$name} — {$published['doi']}");

            return true;
        } catch (\Throwable $e) {
            // Do not leave a half-made draft behind in the account.
            if ($draft !== null) {
                $zenodo->discard($draft['id']);
            }

            $this->line("  <error>failed</error>   {$name} — ".$e->getMessage());

            return false;
        } finally {
            // Whatever happened, the copy does not stay on the server: the
            // disk is shared and small, and this is a few hundred megabytes
            // per run.
            if ($file !== null && str_starts_with($file, sys_get_temp_dir())) {
                @unlink($file);
            }
        }
    }

    /**
     * The thesis as a file on this machine, fetched from Drive if that is
     * where it lives, or null when there is none.
     */
    private function fetchFile(Thesis $thesis): ?string
    {
        if ($thesis->hasFile() && Storage::disk('books')->exists($thesis->file_path)) {
            return Storage::disk('books')->path($thesis->file_path);
        }

        $driveId = $thesis->drive_file_id ?: $this->driveIdFromUrl($thesis->url);

        if (! $driveId) {
            return null;
        }

        // tempnam creates the file; appending to its name would leave that
        // first one behind on every run. The name Zenodo sees comes from
        // filename() anyway, so this one needs no extension.
        $temporary = tempnam(sys_get_temp_dir(), 'deposit');
        $contents = DriveApi::request(180)->get(
            'https://www.googleapis.com/drive/v3/files/'.$driveId,
            ['alt' => 'media']
        );

        if ($contents->failed()) {
            @unlink($temporary);

            throw new \RuntimeException('the file could not be fetched from Drive ('.$contents->status().')');
        }

        file_put_contents($temporary, $contents->body());

        return $temporary;
    }

    private function driveIdFromUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return preg_match('#/d/([\w-]{20,})#', $url, $found) ? $found[1] : null;
    }

    private function filename(Thesis $thesis): string
    {
        $stem = Str::of($thesis->title_en ?: $thesis->title)->limit(60, '')->slug();

        return ($stem->isEmpty() ? 'thesis-'.$thesis->id : (string) $stem).'.pdf';
    }
}
