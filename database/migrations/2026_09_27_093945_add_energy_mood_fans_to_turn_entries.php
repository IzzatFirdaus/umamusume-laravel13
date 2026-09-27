<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            // No ->after(): this repo is SQLite-only (PRD §6.8) and the positional
            // clause is MySQL DDL the grammar discards here.
            $table->unsignedSmallInteger('energy')->nullable();
            $table->string('mood', 20)->nullable();
            $table->unsignedInteger('fans')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->dropColumn(['energy', 'mood', 'fans']);
        });
    }
};
