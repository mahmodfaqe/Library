<?php

use Illuminate\Support\Facades\Schedule;

// Visitor messages are kept for the period quoted in the privacy notice and
// then deleted. See config/library.php.
Schedule::command('feedback:prune')->dailyAt('03:00');

// A daily copy of the database, keeping two weeks of history.
Schedule::command('backup:database')->dailyAt('02:30');

// Hourly, but it only speaks when something changes: silence means well.
// An outside monitor covers the case where the server itself is gone and this
// cannot run at all.
Schedule::command('health:report')->hourly();
