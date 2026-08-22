<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'admin' may manage staff accounts; 'staff' may only manage content.
            $table->string('role', 20)->default('staff')->after('email');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });

        // Who changed what, so an institutional system can be audited.
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name');          // kept even if the account is later removed
            $table->string('action', 60);          // e.g. department.created
            $table->string('subject')->nullable(); // what it was done to
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'last_login_at']);
        });
    }
};
