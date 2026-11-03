<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('run_skills', function (Blueprint $table) {
            $table->unsignedTinyInteger('hint_level')->nullable()->after('turn_acquired');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('run_skills', function (Blueprint $table) {
            $table->dropColumn('hint_level');
        });
    }
};
