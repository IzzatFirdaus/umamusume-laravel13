<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CardRarity;
use App\Models\SupportCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportCard>
 */
class SupportCardFactory extends Factory
{
    protected $model = SupportCard::class;

    /**
     * Field-for-field the shape `support-cards.json` publishes: a character name and a bracketed
     * epithet rather than a single card name, `char_id` in GameTora's character space, and eleven level
     * anchors per effect. The defaults are Special Week's first record, read off the export, so a test
     * that never overrides anything is still asserting against a row the source actually has.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_id' => fake()->unique()->numberBetween(10001, 99999),
            'char_id' => 1001,
            'char_name' => 'Special Week',
            'name_ja' => 'スペシャルウィーク',
            'title_en' => '[Tracen Academy]',
            'title_ja' => '[トレセン学園]',
            'rarity' => CardRarity::OneStar,
            'type' => 'guts',
            'release_jp' => '2021-02-24',
            'release_global' => '2025-06-26',
            'effects' => [[1, 5, -1, -1, 10, 10, -1, -1, 15, -1, -1, -1]],
            // The same record's two skill lists, verbatim. Neither is null: the document carries both
            // keys on all 559 records, so null is a state only a hand-seeded row reaches, and a factory
            // default that made the common case look like the rare one would hide the distinction the
            // detail page renders.
            'hint_skills' => [200162, 200232, 200512, 200612, 200732, 201352, 201542],
            'event_skills' => [200762],
            'source_url' => 'https://gametora.com/data/umamusume/support-cards.json',
            'fetched_at' => now(),
            'is_manual' => false,
        ];
    }

    public function ssr(): static
    {
        return $this->state(fn (array $attributes) => ['rarity' => CardRarity::ThreeStar]);
    }

    public function sr(): static
    {
        return $this->state(fn (array $attributes) => ['rarity' => CardRarity::TwoStar]);
    }

    public function speed(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'speed']);
    }

    public function stamina(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'stamina']);
    }

    public function power(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'power']);
    }

    public function guts(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'guts']);
    }

    public function wit(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'intelligence']);
    }

    /**
     * A Pal card: the 9000-block `char_id` the export gives staff, which has no row in the trainable
     * catalogue. A state rather than a default because that absence is the finding, not an accident, and
     * the deck panel has to render these with no trainee to link to.
     */
    public function friend(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'friend',
            'char_id' => 9001,
            'char_name' => 'Tazuna Hayakawa',
            'name_ja' => null,
        ]);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'group',
            'rarity' => CardRarity::ThreeStar,
            'char_id' => 1017,
            'char_name' => 'Team Sirius',
        ]);
    }

    /**
     * A card Global has not received: `release_en` absent, so the generated `release_status` reads
     * JP-only.
     */
    public function jpOnly(): static
    {
        return $this->state(fn (array $attributes) => ['release_global' => null]);
    }

    /**
     * A character on Unity Cup's linked list, for the Scenario Link badge that is derived on read.
     */
    public function unityCupLink(): static
    {
        return $this->state(fn (array $attributes) => ['char_name' => 'Rice Shower', 'char_id' => 1013]);
    }
}
