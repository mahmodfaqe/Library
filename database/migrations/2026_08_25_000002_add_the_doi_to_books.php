<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The identifier a publisher already gave the book.
 *
 * This is not for minting: a DOI is issued by whoever published the work, and
 * the library did not publish Fungal Biology. It is for recording the one the
 * publisher assigned, so that a reader citing from here cites the same book
 * the rest of the world does — and so that the day the university starts
 * issuing DOIs for its own theses, the field is already here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Long, because a DOI suffix has no length limit in the standard
            // and publishers have used some remarkable ones.
            $table->string('doi', 255)->nullable()->after('isbn');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index('doi');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['doi']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('doi');
        });
    }
};
