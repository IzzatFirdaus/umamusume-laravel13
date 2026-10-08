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
     * The ten aptitude axes, in the order the trainee screens print them.
     *
     * On the model because these are the model's own ten columns. It is the first shared owner of the list,
     * not the only copy: `CatalogController`, `Career\TraineeProfileController` and
     * `Career\TraineeSelectController` each still map the axes by hand, `components/AptitudeGrid.vue` holds
     * a fourth label list client-side, and `GametoraCharacterParser::APTITUDE_COLUMNS` fixes the ingest
     * order. Folding those four in is a refactor across landed screens and their tests, so it is its own
     * slice; two career screens reading one list is the reason it exists today.
     *
     * @var list<array{key: string, label: string}>
     */
    public const APTITUDE_AXES = [
        // ponytail: display strings do not belong on a model; read these labels from `uma.terms.style_*`
        // (`lang/en/uma.php`, their one owner) instead of holding them here.
        ['key' => 'turf', 'label' => 'Turf'],
        ['key' => 'dirt', 'label' => 'Dirt'],
        ['key' => 'sprint', 'label' => 'Sprint'],
        ['key' => 'mile', 'label' => 'Mile'],
        ['key' => 'medium', 'label' => 'Medium'],
        ['key' => 'long', 'label' => 'Long'],
        ['key' => 'front_runner', 'label' => 'Front Runner'],
        ['key' => 'pace_chaser', 'label' => 'Pace Chaser'],
        ['key' => 'late_surger', 'label' => 'Late surger'],
        ['key' => 'end_closer', 'label' => 'End closer'],
    ];

    /**
     * The trainee's own ten letters, or nothing at all when the source published none.
     *
     * The empty arm is the whole point: a trainee with no published aptitudes is not a trainee with ten
     * unknown letters, and printing ten `N/A` cells would report a fact the source never stated. The caller
     * gets `[]` and says so once.
     *
     * @return list<array{key: string, label: string, letter: string|null}>
     */
    public function aptitudeAxes(): array
    {
        if ($this->aptitude_turf === null) {
            return [];
        }

        return array_map(
            fn (array $axis): array => [
                'key' => $axis['key'],
                'label' => $axis['label'],
                'letter' => $this->{'aptitude_'.$axis['key']},
            ],
            self::APTITUDE_AXES,
        );
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
