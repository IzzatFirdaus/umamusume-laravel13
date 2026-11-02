<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->string('track_name')->nullable();
            $table->integer('entries')->nullable();
            $table->integer('course')->nullable();
            $table->integer('direction')->nullable();
            $table->integer('season')->nullable();
            $table->string('factor_effect_1')->nullable();
            $table->string('factor_effect_2')->nullable();
            $table->text('reward_summary')->nullable();
            $table->string('direction_label')->nullable();
            $table->string('course_label')->nullable();
            $table->string('season_label')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->dropColumn([
                'track_name',
                'entries',
                'course',
                'direction',
                'season',
                'factor_effect_1',
                'factor_effect_2',
                'reward_summary',
                'direction_label',
                'course_label',
                'season_label',
            ]);
        });
    }
};
