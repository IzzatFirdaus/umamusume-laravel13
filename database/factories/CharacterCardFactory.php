<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterCard>
 */
class CharacterCardFactory extends Factory
{
    protected $model = CharacterCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'card_id' => fake()->unique()->numberBetween(900000, 999999),
            'umamusume_id' => Umamusume::factory(),
            'title' => '[Sample Form]',
            'rarity' => CardRarity::ThreeStar,
            'global_release_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'is_debut_form' => false,
            'unconfirmed' => false,
            // Amendment A1: source_url is NOT NULL in the migration, so a factory that
            // omits it cannot insert. These four say "a fetch wrote this row"; the
            // manual() state below is the human case.
            'source_url' => 'https://gametora.test/character-cards.json',
            'snapshot_path' => null,
            'fetched_at' => now(),
            'source_timezone' => 'Asia/Tokyo',
            'is_manual' => false,
        ];
    }

    public function manual(): static
    {
        return $this->state(fn (): array => ['is_manual' => true]);
    }

    public function debut(): static
    {
        return $this->state(fn (): array => ['is_debut_form' => true]);
    }

    public function unconfirmed(): static
    {
        return $this->state(fn (): array => ['unconfirmed' => true]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => ['unconfirmed' => false]);
    }
}
