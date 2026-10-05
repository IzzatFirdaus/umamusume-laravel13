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
 * @property int $id
 * @property int $training_run_id
 * @property array<array-key, mixed>|null $tags the Trainer's own facets, a flat list of strings
 *                                              (design-2.0 SCREEN-020's suggested tag vocabulary);
 *                                              null when they tagged nothing
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingRun $trainingRun
 */
#[Fillable(['training_run_id', 'tags', 'notes'])]
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

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }
}
