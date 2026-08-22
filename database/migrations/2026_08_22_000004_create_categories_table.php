<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The library's own subject classification, mirroring the folders the
        // collection is organised into. Separate from `departments`, which are
        // the college's academic departments shown on the home page.
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('drive_folder_id', 100)->nullable()->unique();
            $table->timestamps();
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            // Lets a re-run recognise a book it already imported.
            $table->string('drive_file_id', 100)->nullable()->unique()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('drive_file_id');
        });

        Schema::dropIfExists('categories');
    }
};
