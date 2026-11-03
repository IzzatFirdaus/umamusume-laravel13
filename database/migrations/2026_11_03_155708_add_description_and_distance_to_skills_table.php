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
        Schema::table('skills', function (Blueprint $table) {
            $table->text('description')->nullable()->after('condition_groups');
            $table->string('description_match_key')->nullable()->index()->after('description');
            $table->unsignedTinyInteger('distance_type')->nullable()->after('description_match_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn(['description', 'description_match_key', 'distance_type']);
        });
    }
};
