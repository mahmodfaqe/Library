<?php

use Illuminate\Support\Facades\Schedule;

// Visitor messages are kept for the period quoted in the privacy notice and
// then deleted. See config/library.php.
Schedule::command('feedback:prune')->dailyAt('03:00');

// A daily copy of the database, keeping two weeks of history.
Schedule::command('backup:database')->dailyAt('02:30');
