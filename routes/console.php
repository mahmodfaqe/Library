<?php

use Illuminate\Support\Facades\Schedule;

// Visitor messages are kept for the period quoted in the privacy notice and
// then deleted. See config/library.php.
Schedule::command('feedback:prune')->dailyAt('03:00');

// A daily copy of the database, keeping two weeks of history — and then a
// look inside it, because a backup nobody has opened is only assumed to be one.
Schedule::command('backup:database')->dailyAt('02:30');
Schedule::command('backup:verify')->dailyAt('02:45');

// The disk is shared with another project, and a library that fills it takes
// that one down too. Weekly, and only inside this application's own storage.
Schedule::command('library:tidy --apply')->weeklyOn(0, '04:00');

// Hourly, but it only speaks when something changes: silence means well.
// An outside monitor covers the case where the server itself is gone and this
// cannot run at all.
Schedule::command('health:report')->hourly();
