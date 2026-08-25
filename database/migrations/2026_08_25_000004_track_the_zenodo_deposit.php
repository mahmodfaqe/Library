<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a thesis was deposited, so it is never deposited twice.
 *
 * A published Zenodo record cannot be deleted — only a new version can be
 * added beside it. Depositing the same thesis a second time therefore leaves
 * two permanent records of one piece of work, each with its own DOI, and
 * nothing can be done about it afterwards. This column is what makes the
 * second attempt refuse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theses', function (Blueprint $table) {
            $table->unsignedBigInteger('zenodo_id')->nullable()->after('doi');
            $table->string('zenodo_url', 255)->nullable()->after('zenodo_id');
            $table->timestamp('deposited_at')->nullable()->after('zenodo_url');
        });
    }

    public function down(): void
    {
        Schema::table('theses', function (Blueprint $table) {
            $table->dropColumn(['zenodo_id', 'zenodo_url', 'deposited_at']);
        });
    }
};
