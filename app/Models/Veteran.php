<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VeteranFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A completed run kept in the Trainer's Veteran library (PRD FR-G, ADR-0020 §3).
 *
 * The record is a pointer plus the Trainer's own metadata: the run it was built from, a tag list and
 * free-text notes. Everything else a Veteran shows — the trainee, final stats, skills, sparks and race
 * record — is read back through `trainingRun()` from the run that recorded it. Nothing is derived and
 * nothing is computed here; the computation ban is FR-G-4 and ADR-0020 §3.
 *
 * The stored `tags` are the Trainer's spelling, unmodified. `tags_normalized` is their case-folded
 * twin and exists only so the library's tag filter can match either casing (KI-72); it is written by
 * `RecordVeteran` beside the tags, never by a Trainer.
 *
 * @property int $id
 * @property int $training_run_id
 * @property array<array-key, mixed>|null $tags the Trainer's own facets, a flat list of strings
 *                                              (design-2.0 SCREEN-020's suggested tag vocabulary);
 *                                              null when they tagged nothing
 * @property array<array-key, mixed>|null $tags_normalized the same list folded to lowercase, the
 *                                                     column the tag filter matches (KI-72)
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 */
#[Fillable(['training_run_id', 'tags', 'tags_normalized', 'notes'])]
class Veteran extends Model
{
    /** @use HasFactory<VeteranFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<TrainingRun, $this>
     */
    public function trainingRun(): BelongsTo
    {
        return $this->belongsTo(TrainingRun::class);
    }

    /**
     * The `umamusume` id behind a library row, or null when the id is absent or no longer resolves.
     *
     * A Veteran is a run whose trainee is the Umamusume the client shows in a parent slot, so the identity
     * is read through the run rather than stored a second time (`ADR-0010` Decision). Two writes turn
     * picked library ids into the run's two `inheritance_parent_*` columns — the Legacy Lab's
     * `Confirm Inheritance` and Preflight's `Start Career` — so the resolution lives here rather than in
     * either caller.
     */
    public static function traineeId(?int $id): ?int
    {
        if ($id === null) {
            return null;
        }

        return self::query()->with('trainingRun')->find($id)?->trainingRun?->umamusume_id;
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'tags_normalized' => 'array',
        ];
    }
}
