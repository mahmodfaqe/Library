<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Where the PDF lives on this server, relative to the books disk.
            // Null means the book is still only reachable through `url`.
            $table->string('file_path', 500)->nullable()->after('url');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_path');
            $table->unsignedInteger('downloads')->default(0)->after('file_size');

            // `url` becomes optional once a file is held locally.
            $table->string('url', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_size', 'downloads']);
        });
    }
};
