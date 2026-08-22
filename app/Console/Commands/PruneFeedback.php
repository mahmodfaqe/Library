<?php

namespace App\Console\Commands;

use App\Models\Feedback;
use Illuminate\Console\Command;

class PruneFeedback extends Command
{
    protected $signature = 'feedback:prune';

    protected $description = 'Delete visitor feedback older than the published retention period';

    public function handle(): int
    {
        $days = (int) config('library.feedback_retention_days');

        if ($days < 1) {
            $this->error('library.feedback_retention_days must be at least 1.');

            return self::FAILURE;
        }

        $deleted = Feedback::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Deleted {$deleted} message(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
