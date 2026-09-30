<?php

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
        Schema::create('support_effects', function (Blueprint $table) {
            $table->id();
            $table->integer('effect_id')->unique();
            $table->string('name_en');
            $table->string('name_ja')->nullable();
            $table->string('calc')->nullable()->check("calc IN ('flat', 'mult', 'add', 'level')");
            $table->string('symbol')->nullable();
            $table->text('description_en')->nullable();
            $table->string('source_url');
            $table->timestamp('fetched_at');
        });

        Schema::create('support_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('support_id')->unique();
            $table->unsignedBigInteger('char_id')->nullable();
            $table->string('name');
            $table->string('name_ja')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_ja')->nullable();
            $table->tinyInteger('rarity')->check('rarity IN (1, 2, 3)');
            $table->string('type')->check("type IN ('speed', 'stamina', 'power', 'guts', 'intelligence', 'friend', 'group')");
            $table->date('release_jp')->nullable();
            $table->date('release_global')->nullable();
            $table->string('release_status')->storedAs(
                "CASE
                    WHEN release_global IS NOT NULL THEN 'Global'
                    WHEN release_jp IS NOT NULL THEN 'JP-only'
                    ELSE 'Unreleased'
                END"
            );
            $table->json('effects')->default('[]');
            $table->string('source_url');
            $table->timestamp('fetched_at');
            $table->boolean('is_manual')->default(false);
            $table->timestamps();

            $table->foreign('char_id')->references('id')->on('umamusume')->nullOnDelete();
        });

        Schema::create('deck_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_run_id');
            $table->unsignedBigInteger('support_card_id');
            $table->tinyInteger('slot_position')->check('slot_position BETWEEN 1 AND 6');
            $table->timestamps();

            $table->foreign('training_run_id')->references('id')->on('training_runs')->cascadeOnDelete();
            $table->foreign('support_card_id')->references('id')->on('support_cards')->restrictOnDelete();
            $table->unique(['training_run_id', 'slot_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_slots');
        Schema::dropIfExists('support_cards');
        Schema::dropIfExists('support_effects');
    }
};
