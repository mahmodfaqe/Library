<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Title and author folded to one spelling per letter, so a search
            // typed on a Kurdish keyboard finds titles scanned with Arabic
            // letter forms and the other way round.
            $table->text('search_text')->nullable()->after('author');
        });

        // A plain index cannot serve a leading-wildcard LIKE, so give the
        // column a fulltext index where the driver supports one.
        if (in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
            Schema::table('books', function (Blueprint $table) {
                $table->fullText('search_text');
            });
        }
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
                $table->dropFullText(['search_text']);
            }

            $table->dropColumn('search_text');
        });
    }
};
