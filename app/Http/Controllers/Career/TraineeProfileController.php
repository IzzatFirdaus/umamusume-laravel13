<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use App\Services\Career\SetupDraft;
use App\Services\DataPipeline\ArtworkMirror;
use Closure;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Trainee Profile, `SCREEN-004` (PRD FR-A-1, FR-A-4, `ADR-0020` §1). The read screen behind SCR-CAR-003's
 * "View Profile" action, and the wizard's second step rather than a third copy of the catalog.
 *
 * **What this screen is for.** SCREEN-004's purpose in `docs/proposals/screen-spec-2.0.md` §7 is to give
 * the Trainer build-relevant information *before* committing to a trainee, so it prints what the catalog
 * actually holds about her, states the wizard's stored choice, and offers the same `Select` action as the
 * roster. It is not a duplicate of `Catalog/Show`: that page owns aliases, run history and provenance, and
 * none of those bear on which trainee to pick.
 *
 * **Rarity is at card grain, and there is no trainee-level rarity to print.** `character_cards.rarity` is
 * the only rarity column in the schema, so each costume form carries its own and no aggregate badge is
 * offered; a "her rarity" line would state a trainee property the data does not carry. The same reason
 * puts the ten aptitude letters on `umamusume` once rather than repeated per form (ADR-0008).
 *
 * **Sections the brief asks for that this repository cannot honour, and where that is written down.**
 *
 * - **Career goals: the section is omitted, not emptied.** No `trainee_goals` table, model or factory
 *   exists, and `database/migrations/2026_09_28_180350_create_race_catalog_slots_table.php:70-72` says the
 *   obligation rows it does hold are scenario-scoped and "deliberately not the per-character Goal; see
 *   trainee_goals". `KNOWN-ISSUES.md:10` records KI-34 as that filing's reservation. Rendering a heading
 *   with nothing under it would advertise a fact the tool is known not to have, so there is no heading.
 * - **Growth rates and "Version"** are absent for the reasons on `TraineeSelectController` and on the
 *   `version` slot below, which is a named absence with its reason rather than a blank.
 * - **Hint skills** are not on a trainee at all: `character_cards` has no `skills_hint` column, and hints
 *   live only on `support_cards`.
 * - **Evolution skills** are stored on `skills_evo` but `SCREEN_SPEC.md` SCR-CAT-002's own ruling refuses
 *   to list them, so this screen does not either.
 * - **"Ideal distances" and "suitable running styles"** are printed as **aptitude**. The ten letters are
 *   the only trainee-side signal in the schema; whether one of them is "ideal" is a judgement no column
 *   holds, and labelling it as one would be the invented claim `AGENTS.md` §5 forbids.
 * - **"Recommended stat distribution", "useful inheritance", "useful support types"** are named absences.
 *   `support_cards` deliberately carries no `umamusume_id`
 *   (`database/migrations/2026_09_30_151945_correct_support_card_schema_and_constraints.php:36-38`: "None
 *   is added here: nothing in this slice reads a support card's trainee through the local catalogue"), so
 *   a useful-support-types list would have to join on `char_name` *text* — a name match, not data.
 *   "Useful inheritance" is the inheritance computation `ADR-0020` §3 bans, and a recommended stat
 *   distribution is a recommendation, which the Planner's no-prediction rule (PRD §6) also declines.
 *
 * **Not cached** (`AGENTS.md` §8). One indexed lookup on the primary key plus four bounded loads.
 */
class TraineeProfileController extends Controller
{
    /**
     * An unknown id is the binding's 404, which is what a stale bookmarked draft deserves. A trainee with
     * no confirmed costume form is *not* a 404: she is a catalog row whose forms have not been confirmed
     * by two sources, and the page says so in words, the same way SCR-CAT-002 handles it.
     */
    public function show(Umamusume $umamusume, ArtworkMirror $mirror): Response
    {
        $umamusume->loadMissing(['profile', 'cards' => $this->cardScope()]);

        /** @var Collection<int, CharacterCard> $cards */
        $cards = $umamusume->cards;

        // Resolve every skill id on every form in one query, then read the map back per list. Global
        // availability is `Skill::scopeAvailableOnGlobal()`'s answer, not this file's: an id that does
        // not survive that scope prints as a counted gap rather than as a name guessed from the export.
        $skillIds = $cards->flatMap(fn (CharacterCard $card): array => $this->cardSkillIds($card))->unique()->values();

        $resolved = $skillIds->isEmpty()
            ? collect()
            : Skill::query()->availableOnGlobal()->whereIn('export_id', $skillIds)->get()->keyBy('export_id');

        $draft = SetupDraft::read();
        $headerCard = $cards->first(static fn (CharacterCard $card): bool => $card->is_debut_form) ?? $cards->first();

        return Inertia::render('Career/TraineeProfile', [
            'trainee' => [
                'id' => $umamusume->id,
                'slug' => $umamusume->slug,
                'name' => $umamusume->name,
                'name_ja' => $umamusume->name_ja,
                'release_status_label' => $umamusume->release_status->label(),
                'is_manual' => $umamusume->is_manual,
                'aptitudes' => $this->aptitudes($umamusume),
                'profile' => $this->profileShape($umamusume->profile),
                'artworkURL' => $headerCard === null
                    ? null
                    : $mirror->url('card_portrait', (int) $headerCard->card_id),
            ],
            'forms' => $cards->map(function (CharacterCard $card) use ($mirror, $resolved): array {
                $lists = [
                    ['key' => 'skills_unique', 'label' => 'Unique skill', 'ids' => $card->skills_unique ?? []],
                    ['key' => 'skills_innate', 'label' => 'Starting skills', 'ids' => $card->skills_innate ?? []],
                    ['key' => 'skills_awakening', 'label' => 'Awakening skills', 'ids' => $card->skills_awakening ?? []],
                    ['key' => 'skills_event', 'label' => 'Event skills', 'ids' => $card->skills_event ?? []],
                ];

                return [
                    'id' => $card->id,
                    'title' => $card->title,
                    // Rarity is this form's own fact, printed beside the form's name and nowhere else.
                    'rarity_label' => $card->rarity->label(),
                    'rarity_stars' => $card->rarity->stars(),
                    'rarity_word' => $card->rarity->word(),
                    'is_debut_form' => $card->is_debut_form,
                    'global_release_date' => $card->global_release_date->toDateString(),
                    'global_release_date_display' => $card->global_release_date->format('M j, Y'),
                    'artworkURL' => $mirror->url('card_portrait', (int) $card->card_id),
                    'skillLists' => array_map(function (array $list) use ($resolved): array {
                        $skills = collect($list['ids'])
                            ->map(static fn ($id) => $resolved->get($id))
                            ->filter()
                            ->map(static fn (Skill $skill): array => [
                                'name' => $skill->name,
                                'sp_cost' => $skill->sp_cost,
                                'is_unique' => $skill->is_unique,
                                // `availableOnGlobal()` is exactly the predicate the skill detail route
                                // serves (`ADR-0011` §2), so every skill on this list has a link that
                                // resolves and none has to be printed linkless.
                                'url' => route('skills.show', $skill),
                            ])
                            ->values()
                            ->all();

                        return [
                            'key' => $list['key'],
                            'label' => $list['label'],
                            'skills' => $skills,
                            // Disclosed, not dropped: the id is on the source's form, and six of the
                            // catalog's 237 awakening ids name no Global skill row. Silence would read
                            // as "this form has no more skills".
                            'unresolved' => count($list['ids']) - count($skills),
                        ];
                    }, $lists),
                ];
            })->values()->all(),
            'selected' => $draft['umamusume_id'],
            'scenarioLabel' => SetupDraft::scenarioLabel(),
            'scenarioPending' => $draft['scenario'] === null,
            // The three Build-analysis figures, named with the reason each one is absent. Values are null
            // so the page renders `N/A` with the title rather than a blank or a guess (`AGENTS.md` §5).
            'absences' => [
                'version' => [
                    'value' => null,
                    'title' => 'No column records a version for a trainee, and app.ruleset is null by ruling.',
                ],
                'stat_distribution' => [
                    'value' => null,
                    'title' => 'Recommending a stat distribution is a prediction this tool does not make (PRD §6).',
                ],
                'inheritance' => [
                    'value' => null,
                    'title' => 'Inheritance is computed at the Legacy step from the run\'s own parents, never stored per trainee (ADR-0020 §3).',
                ],
                'support_types' => [
                    'value' => null,
                    'title' => 'support_cards carries no umamusume_id by ruling, so no stored link names a useful support type for her.',
                ],
            ],
        ]);
    }

    /**
     * The trainee's ten aptitude letters, or null when the source published none. `aptitude_turf` is the
     * sentinel the parser's all-or-nothing write makes reliable, and the grid is the same read
     * `CatalogController` makes, so the two screens cannot disagree about the same trainee.
     *
     * @return array<string, string>|null
     */
    private function aptitudes(Umamusume $umamusume): ?array
    {
        if ($umamusume->aptitude_turf === null) {
            return null;
        }

        return [
            'turf' => $umamusume->aptitude_turf,
            'dirt' => $umamusume->aptitude_dirt,
            'sprint' => $umamusume->aptitude_sprint,
            'mile' => $umamusume->aptitude_mile,
            'medium' => $umamusume->aptitude_medium,
            'long' => $umamusume->aptitude_long,
            'front_runner' => $umamusume->aptitude_front_runner,
            'pace_chaser' => $umamusume->aptitude_pace_chaser,
            'late_surger' => $umamusume->aptitude_late_surger,
            'end_closer' => $umamusume->aptitude_end_closer,
        ];
    }

    /**
     * The profile block as an explicit array, or null when no profile fetch has written one.
     *
     * Both `hasFullBirthday()` and `hasThreeSizes()` are honoured rather than the raw columns: a
     * partially-present birthday must not be printed as a date, and a partially-present measurement must
     * not be printed as a whole one. There is no weight column on `umamusume_profiles`, which is why the
     * block carries height and stops there.
     *
     * @return array<string, mixed>|null
     */
    private function profileShape(?UmamusumeProfile $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'va_ja' => $profile->va_ja,
            'va_en' => $profile->va_en,
            'height' => $profile->height,
            'birthday' => $this->birthday($profile),
            'three_sizes' => $profile->hasThreeSizes()
                ? ['b' => $profile->three_sizes_b, 'h' => $profile->three_sizes_h, 'w' => $profile->three_sizes_w]
                : null,
        ];
    }

    /**
     * The birthday, split so a missing year reads as a missing year and never as a January 1st. Same
     * shape and same reason as `CatalogController::birthday()`; `birth_year` is the only part the
     * document ever omits (D-220).
     *
     * @return array{iso: string|null, display: string}|null
     */
    private function birthday(UmamusumeProfile $profile): ?array
    {
        if ($profile->hasFullBirthday()) {
            return [
                'iso' => sprintf('%04d-%02d-%02d', $profile->birth_year, $profile->birth_month, $profile->birth_day),
                'display' => Carbon::create($profile->birth_year, $profile->birth_month, $profile->birth_day)->format('M j, Y'),
            ];
        }

        if ($profile->birth_month !== null && $profile->birth_day !== null) {
            return [
                'iso' => null,
                'display' => Carbon::create(2000, $profile->birth_month, $profile->birth_day)->format('M j').' · year not published',
            ];
        }

        return null;
    }

    /**
     * The four skill id lists one form publishes, in the source's own order. `skills_evo` is deliberately
     * not among them: SCR-CAT-002 refuses to list evolved pairs, and a second surface quietly listing
     * them would be the drift.
     *
     * @return list<int>
     */
    private function cardSkillIds(CharacterCard $card): array
    {
        return [
            ...($card->skills_unique ?? []),
            ...($card->skills_innate ?? []),
            ...($card->skills_awakening ?? []),
            ...($card->skills_event ?? []),
        ];
    }

    /**
     * Which forms this screen shows and in what order: debut first, then the Global release date, then
     * the source's card id, with solo-sourced forms hidden. The same rule `CatalogController::cardScope()`
     * owns, restated because that method is `private`.
     *
     * @return Closure(HasMany<CharacterCard, Umamusume>): void
     */
    private function cardScope(): Closure
    {
        return static function (HasMany $cards): void {
            $cards->where('unconfirmed', false)
                ->orderBy('is_debut_form', 'desc')
                ->orderBy('global_release_date')
                ->orderBy('card_id');
        };
    }
}
