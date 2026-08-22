<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            // A book's title and author are its own, not something the site
            // translates — only the surrounding interface is localised.
            $table->string('title');
            $table->string('author')->nullable();
            $table->year('year')->nullable();
            $table->string('language', 40)->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url', 500);
            $table->string('cover_url', 500)->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('author');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
