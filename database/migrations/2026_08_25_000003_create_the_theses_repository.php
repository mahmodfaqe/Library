<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The university's own work, which is a different thing from its library.
 *
 * The books are other people's: the catalogue records them and links to them.
 * A thesis is written here, examined here, and published here — the university
 * is its publisher. That is what makes this a repository rather than a second
 * catalogue, and it is why the columns are not the same ones:
 *
 *  - a supervisor and a degree, because that is how a thesis is found and
 *    judged;
 *  - an English title and abstract beside the original, because a thesis
 *    written in Kurdish is invisible to the world without them;
 *  - a state, because a thesis is submitted and approved before it is public,
 *    unlike a book which simply exists;
 *  - an embargo, because a thesis awaiting publication or a patent may have
 *    its record open while its file is not.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theses', function (Blueprint $table) {
            $table->id();

            // As written, in the language it was written in.
            $table->string('title', 500);
            // And in English, without which it cannot be found from outside.
            $table->string('title_en', 500)->nullable();

            $table->string('author', 190);
            $table->string('supervisor', 190)->nullable();
            $table->string('co_supervisor', 190)->nullable();

            $table->string('degree', 20);

            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            $table->smallInteger('year');
            $table->date('defended_on')->nullable();

            $table->string('language', 40)->nullable();
            $table->unsignedSmallInteger('pages')->nullable();

            $table->text('abstract')->nullable();
            $table->text('abstract_en')->nullable();
            $table->text('keywords')->nullable();

            // Issued by the university, once it is a member of a registration
            // agency. Until then the thesis page's own address is the
            // permanent one.
            $table->string('doi', 255)->nullable();

            // The same arrangement as the books: the file lives on Drive, so
            // the server's disk is not the limit on how much the university
            // can keep.
            $table->string('url', 500)->nullable();
            $table->string('drive_file_id', 100)->nullable()->unique();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Submitted, examined, published — or withdrawn, which happens.
            $table->string('status', 20)->default('draft');

            // While this is in the future the record is public and the file is
            // not: metadata is never embargoed, only access.
            $table->date('embargo_until')->nullable();

            $table->string('license', 40)->nullable();

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedInteger('downloads')->default(0);

            // Folded copy of everything worth searching, as the books have.
            $table->text('search_text')->nullable();

            $table->timestamps();

            $table->index(['status', 'year']);
            $table->index('degree');
            $table->index('doi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theses');
    }
};
