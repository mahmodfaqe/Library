<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL's YEAR type holds 1901 to 2155 and nothing else.
     *
     * The catalogue holds books older than that — the admin form has always
     * accepted 1400 onwards — and SQLite, which the tests run on, stores a
     * YEAR column as a plain integer. So a year like 1890 saved cleanly on a
     * developer's machine and failed on the server with "Out of range value
     * for column 'year'", which is the worst way for the two to differ.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->smallInteger('year')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->year('year')->nullable()->change();
        });
    }
};
