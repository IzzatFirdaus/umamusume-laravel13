<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ScenarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One training scenario and the stat caps it enforces (PRD FR-A-5, ADR-0004).
 * Reference data only: nothing here computes a race or a training outcome.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $name_ja
 * @property int|null $order
 * @property Carbon|null $jp_start_date
 * @property Carbon|null $global_start_date
 * @property int $cap_speed
 * @property int $cap_stamina
 * @property int $cap_power
 * @property int $cap_guts
 * @property int $cap_wit
 * @property int|null $hard_cap
 * @property Carbon|null $caps_reworked_at
 * @property string|null $source_url
 * @property Carbon|null $fetched_at
 * @property bool $is_manual
 */
#[Table('scenarios')]
#[Fillable([
    'slug', 'name', 'name_ja', 'order', 'jp_start_date', 'global_start_date',
    'cap_speed', 'cap_stamina', 'cap_power', 'cap_guts', 'cap_wit', 'hard_cap',
    'caps_reworked_at', 'source_url', 'fetched_at', 'is_manual',
])]
class Scenario extends Model
{
    /** @use HasFactory<ScenarioFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jp_start_date' => 'date',
            'global_start_date' => 'date',
            'caps_reworked_at' => 'date',
            'fetched_at' => 'datetime',
            'cap_speed' => 'integer',
            'cap_stamina' => 'integer',
            'cap_power' => 'integer',
            'cap_guts' => 'integer',
            'cap_wit' => 'integer',
            'hard_cap' => 'integer',
            'is_manual' => 'boolean',
        ];
    }
}
