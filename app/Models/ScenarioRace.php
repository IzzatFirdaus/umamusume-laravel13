<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ScenarioRaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One slot of a scenario's fixed race calendar (ADR-0003 scenario_races).
 * Reference data: it arrives through the fetch pipeline with provenance and is
 * never hand-seeded. `tier` stores the recorded label, never a grade-code
 * derivation.
 *
 * @property int $id
 * @property string $scenario_key
 * @property string $slot_label
 * @property string $race_name
 * @property string|null $tier
 * @property int|null $fans_needed
 * @property bool $mandatory
 * @property bool $maiden_gated
 * @property string|null $source_url
 * @property string|null $snapshot_path
 * @property Carbon|null $fetched_at
 * @property string|null $source_timezone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, RaceEntry> $raceEntries
 */
#[Table('scenario_races')]
#[Fillable([
    'scenario_key', 'slot_label', 'race_name', 'tier', 'fans_needed',
    'mandatory', 'maiden_gated', 'source_url', 'snapshot_path', 'fetched_at',
    'source_timezone',
])]
class ScenarioRace extends Model
{
    /** @use HasFactory<ScenarioRaceFactory> */
    use HasFactory;

    /**
     * @return HasMany<RaceEntry, $this>
     */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }

    protected function casts(): array
    {
        return [
            'fans_needed' => 'integer',
            'mandatory' => 'boolean',
            'maiden_gated' => 'boolean',
            'fetched_at' => 'datetime',
        ];
    }
}
