<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The ten aptitude letters GameTora publishes per costume card, in the export's
 * own element order (ADR-0004, PRD FR-A-5): turf, dirt, then the four distance
 * bands and the four running styles.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'aptitude_turf', 'aptitude_dirt',
        'aptitude_sprint', 'aptitude_mile', 'aptitude_medium', 'aptitude_long',
        'aptitude_front_runner', 'aptitude_pace_chaser', 'aptitude_late_surger', 'aptitude_end_closer',
    ];

    public function up(): void
    {
        Schema::table('umamusume', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                $table->char($column, 1)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('umamusume', function (Blueprint $table): void {
            $table->dropColumn(self::COLUMNS);
        });
    }
};
