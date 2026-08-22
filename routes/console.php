<?php

use Illuminate\Support\Facades\Schedule;

// Visitor messages are kept for the period quoted in the privacy notice and
// then deleted. See config/library.php.
Schedule::command('feedback:prune')->dailyAt('03:00');
