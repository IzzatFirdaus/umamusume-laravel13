<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnergyState;
use App\Enums\MoodTier;
use Database\Factories\TurnEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One Trainer-entered turn of stats; the sole input to all run math (PRD
 * FR-C-2, CLAUDE.md planner rule: deterministic over entered turns).
 *
 * Energy, mood and fans are end-of-turn totals, not deltas, matching the five
 * stat columns (ADR-0003). All three are nullable because runs logged before
 * they existed must not be backfilled with guesses.
 *
 * `choice` and `decision_snapshot` are FD-4's two columns. Both are null on every row written before it:
 * the tool recorded what a turn gave, never what the Trainer chose, and never kept the advice that went
 * with the choice. Back-filling either difference would invent a record it did not hold (`AGENTS.md` §5),
 * so a row from before FD-4 shows no action and no forecast rather than an implied one. The snapshot is
 * written once by the server at the moment of the action and never recomputed; a client cannot supply one,
 * because the Form Request has no rule for the key and `validated()` cannot carry it.
 *
 * `corrected_from` and `superseded_by` are the two halves of one link: the row this row replaces, and the
 * row that replaced this one. Both are stored because the Timeline is read from either end.
 *
 * @property int $id
 * @property int $training_run_id
 * @property int $turn
 * @property int $speed
 * @property int $stamina
 * @property int $power
 * @property int $guts
 * @property int $wit
 * @property int|null $sp
 * @property string|null $condition
 * @property int|null $energy
 * @property EnergyState|null $energy_state
 * @property string|null $energy_band
 * @property MoodTier|null $mood
 * @property int|null $fans
 * @property int|null $facility_speed
 * @property int|null $facility_stamina
 * @property int|null $facility_power
 * @property int|null $facility_guts
 * @property int|null $facility_wit
 * @property int|null $failure_rate
 * @property array<string, int>|null $preview_gains
 * @property string|null $choice
 * @property array<string, mixed>|null $decision_snapshot
 * @property int|null $corrected_from
 * @property int|null $superseded_by
 * @property-read TrainingRun $trainingRun
 */
#[Fillable(['training_run_id', 'turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'energy_state', 'energy_band', 'mood', 'fans', 'facility_speed', 'facility_stamina', 'facility_power', 'facility_guts', 'facility_wit', 'failure_rate', 'preview_gains', 'choice', 'decision_snapshot', 'corrected_from', 'superseded_by'])]
class TurnEntry extends Model
{
    /** @use HasFactory<TurnEntryFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    /**
     * The Trainer's own reading of what the turn gave, in the order the client shows it.
     *
     * These are entered figures, not yields this tool computed: `preview_gains` is the row the Trainer read
     * off the client while recording. Null when the turn has no reading, which is the same absence the
     * Timeline renders as "no gains recorded" and the Cockpit renders as an actual result still pending.
     * A member stored without a figure is dropped rather than shown as zero: zero is a preview of no change,
     * which is a reading, and an absent one is not.
     *
     * @return list<array{label: string, value: int}>|null
     */
    public function previewGainRows(): ?array
    {
        $gains = $this->preview_gains;

        if (! is_array($gains) || $gains === []) {
            return null;
        }

        $labels = [
            'speed' => 'Speed',
            'stamina' => 'Stamina',
            'power' => 'Power',
            'guts' => 'Guts',
            'wit' => 'Wit',
            'sp' => 'Skill Points',
        ];

        $rows = [];

        foreach ($labels as $key => $label) {
            if (isset($gains[$key])) {
                $rows[] = ['label' => $label, 'value' => (int) $gains[$key]];
            }
        }

        return $rows === [] ? null : $rows;
    }

    /**
     * One stat column read back by its matrix name ("Speed" … "Wit"), the name the client and the scenario
     * matrix use. A name the matrix does not carry, or a turn with no such column, returns null rather than a
     * zero: an absent reading is not a reading of no change (`AGENTS.md` §5). This is the single owner of the
     * matrix-name lookup the four career controllers each kept a private copy of (F2).
     */
    public function statValue(string $matrixKey): ?int
    {
        return match ($matrixKey) {
            'Speed' => $this->speed,
            'Stamina' => $this->stamina,
            'Power' => $this->power,
            'Guts' => $this->guts,
            'Wit' => $this->wit,
            default => null,
        };
    }

    protected function casts(): array
    {
        return [
            'turn' => 'integer',
            'speed' => 'integer',
            'stamina' => 'integer',
            'power' => 'integer',
            'guts' => 'integer',
            'wit' => 'integer',
            'sp' => 'integer',
            'energy' => 'integer',
            'energy_state' => EnergyState::class,
            'mood' => MoodTier::class,
            'fans' => 'integer',
            'failure_rate' => 'integer',
            'preview_gains' => 'array',
            'decision_snapshot' => 'array',
            'corrected_from' => 'integer',
            'superseded_by' => 'integer',
        ];
    }

    /**
     * The turn's Energy as one reading, resolved from the number and the band pair together.
     *
     * `energy` holds a figure the Trainer read; `energy_state` plus `energy_band` hold the coarse level
     * the client showed instead of a number. They are separate columns, so a reader that looks at only
     * one of them prints a recorded band as an absence (R3-01). The state wins over the number because a
     * band is what the client actually showed, and `Unknown` outranks both: a turn explicitly marked not
     * recorded has no reading even if a stale figure is still on the row.
     *
     * `text` is what a screen prints; it stays null when there is nothing to print, so no surface has to
     * re-decide what an absence looks like.
     *
     * @return array{kind: 'exact'|'band'|'none', value: int|null, band: string|null, text: string|null}
     */
    public function energyReading(): array
    {
        if ($this->energy_state === EnergyState::Band && $this->energy_band !== null) {
            return [
                'kind' => 'band',
                'value' => null,
                'band' => $this->energy_band,
                'text' => ucfirst($this->energy_band).' band',
            ];
        }

        if ($this->energy_state !== EnergyState::Unknown && $this->energy !== null) {
            return ['kind' => 'exact', 'value' => $this->energy, 'band' => null, 'text' => (string) $this->energy];
        }

        return ['kind' => 'none', 'value' => null, 'band' => null, 'text' => null];
    }
}
