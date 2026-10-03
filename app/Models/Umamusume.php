<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReleaseStatus;
use App\Services\DataPipeline\NameNormalizer;
use Database\Factories\UmamusumeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * One catalog character, identified to the engine by match_key and to humans
 * by slug + display names (PRD FR-A-1).
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $name_ja
 * @property string|null $match_key
 * @property ReleaseStatus $release_status
 * @property Carbon|null $jp_debut_date
 * @property Carbon|null $global_debut_date
 * @property bool $is_manual
 * @property string|null $aptitude_turf
 * @property string|null $aptitude_dirt
 * @property string|null $aptitude_sprint
 * @property string|null $aptitude_mile
 * @property string|null $aptitude_medium
 * @property string|null $aptitude_long
 * @property string|null $aptitude_front_runner
 * @property string|null $aptitude_pace_chaser
 * @property string|null $aptitude_late_surger
 * @property string|null $aptitude_end_closer
 * @property string|null $external_ref the source's own character id, `gametora:char:{id}` (ADR-0008)
 * @property-read Collection<int, UmamusumeAlias> $aliases
 * @property-read Collection<int, CharacterCard> $cards
 * @property-read UmamusumeProfile|null $profile the profile block, when a fetch has written one
 * @property-read Collection<int, DataSource> $dataSources
 * @property-read Collection<int, TrainingRun> $trainingRuns
 */
#[Table('umamusume')]
#[Fillable(['slug', 'name', 'name_ja', 'match_key', 'release_status', 'jp_debut_date', 'global_debut_date', 'is_manual', 'aptitude_turf', 'aptitude_dirt', 'aptitude_sprint', 'aptitude_mile', 'aptitude_medium', 'aptitude_long', 'aptitude_front_runner', 'aptitude_pace_chaser', 'aptitude_late_surger', 'aptitude_end_closer', 'external_ref'])]
class Umamusume extends Model
{
    /** @use HasFactory<UmamusumeFactory> */
    use HasFactory;

    /**
     * Test rows get `match_key` from the import's own normalizer, applied after the attribute overrides
     * have landed, so a fixture that states a name cannot carry another row's key. See `Skill::newFactory()`.
     */
    protected static function newFactory(): UmamusumeFactory
    {
        $normalizer = app(NameNormalizer::class);

        return UmamusumeFactory::new()->afterMaking(static function (Umamusume $umamusume) use ($normalizer): void {
            $umamusume->match_key ??= $normalizer->normalize($umamusume->name);
        });
    }

    /**
     * @return HasMany<UmamusumeAlias, $this>
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(UmamusumeAlias::class);
    }

    /**
     * @return HasMany<CharacterCard, $this>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(CharacterCard::class);
    }

    /**
     * The profile block, when the `characters` document has been fetched.
     *
     * A `HasOne` and not a `hasOne` behind a nullable join in the view: the row is optional (the
     * source is not declared in every deployment, and 28 of its 163 rows name trainees this
     * catalog does not track), so a trainee without one is a normal state the page renders in
     * words rather than an exception.
     *
     * @return HasOne<UmamusumeProfile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UmamusumeProfile::class);
    }

    /**
     * The name the Japanese client prints, preferring the profile document over the trainee column.
     *
     * Both columns hold the same fact about the same trainee, so reading one and falling back to
     * the other is not the cross-document substitution the profile block's other fields refuse: it
     * keeps the page's one piece of name data readable before `uma:fetch
     * gametora-character-profiles` has ever run, and after a partial source row leaves `name_ja`
     * null. Returns null only when neither carries it, which the view states in words (D-220).
     */
    public function japaneseName(): ?string
    {
        $profile = $this->profile;

        if ($profile === null) {
            return $this->name_ja;
        }

        return $profile->name_ja ?? $this->name_ja;
    }

    /**
     * @return HasMany<DataSource, $this>
     */
    public function dataSources(): HasMany
    {
        return $this->hasMany(DataSource::class);
    }

    /**
     * @return HasMany<TrainingRun, $this>
     */
    public function trainingRuns(): HasMany
    {
        return $this->hasMany(TrainingRun::class);
    }

    protected function casts(): array
    {
        return [
            'release_status' => ReleaseStatus::class,
            'jp_debut_date' => 'date',
            'global_debut_date' => 'date',
            'is_manual' => 'boolean',
        ];
    }
}
