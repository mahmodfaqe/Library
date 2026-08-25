<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a catalogue record needs beyond a title.
 *
 * Title, author, year, language and subject are enough to find a book. They
 * are not enough to cite one: a bibliography wants the publisher and the
 * edition, a reader deciding whether to open it wants the length and a
 * summary, and a search wants the words a librarian would file it under.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('publisher', 190)->nullable()->after('author');

            // Stored without hyphens so that 978-0-8153-4432-2 and
            // 9780815344322 are one number, which is what they are.
            $table->string('isbn', 17)->nullable()->after('publisher');

            // Free text, not a number: "2nd ed.", "چاپی سێیەم", "3. baskı".
            $table->string('edition', 60)->nullable()->after('isbn');

            $table->unsignedSmallInteger('pages')->nullable()->after('edition');

            $table->text('abstract')->nullable()->after('pages');

            // Comma separated, in the language of the book. A table of its own
            // would be the tidier shape, and is worth doing on the day someone
            // wants to browse by keyword rather than search by it.
            $table->text('keywords')->nullable()->after('abstract');

            // Where a record came from, so a librarian can tell what was
            // typed in by a person from what a catalogue lookup guessed.
            $table->string('metadata_source', 40)->nullable()->after('keywords');
            $table->timestamp('metadata_checked_at')->nullable()->after('metadata_source');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index('isbn');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['isbn']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'publisher',
                'isbn',
                'edition',
                'pages',
                'abstract',
                'keywords',
                'metadata_source',
                'metadata_checked_at',
            ]);
        });
    }
};
