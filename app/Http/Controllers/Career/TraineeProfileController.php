<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use App\Services\DataPipeline\ArtworkMirror;
use Closure;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Trainee Profile, `SCREEN-004` (PRD FR-A-1, FR-A-4, `ADR-0020` §1). The read screen SCR-CAR-003's
 * forward door opens, and the middle of the wizard's second step rather than a third copy of the catalog.
 *
 * **What this screen is for.** SCREEN-004's purpose in `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` §7 is to give
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
 *   trainee_goals". `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section "KNOWN-ISSUES.md (defect
 *   register)", records KI-34 as that filing's reservation: a number held for it, never an entry written.
 *   Rendering a heading
 *   with nothing under it would advertise a fact the tool is known not to have, so there is no heading.
 *   *Dated correction 2026-10-14 (`ADR-0026`):* the table, the model and the factory now exist (`24ab0eb`
 *   and the slices after it), the ladder is fetched per character, and KI-34's reservation is spent on it.
 *   The section stays omitted here for the reason on `TraineeProfile.vue`: the published rungs read on her
 *   catalog detail page, where no run is needed, and against a run's records in the Cockpit's Trainee goals
 *   group, and a third copy of one list is a third place for it to drift.
 * - **Growth rates** are a stated absence on the page: the catalogue holds no `stat_bonus` column, so the
 *   row keeps its five cells and prints `N/A` under one visible reason. **Version** left with the Basic
 *   facts card, which no wireframe draws; the catalog detail page still states it.
 * - **Hint skills** are not on a trainee at all: `character_cards` has no `skills_hint` column, and hints
 *   live only on `support_cards`.
 * - **Evolution skills** are stored on `skills_evo` but `SCREEN_SPEC.md` SCR-CAT-002's own ruling refuses
 *   to list them, so this screen does not either.
 * - **"Ideal distances" and "suitable running styles"** are printed as **aptitude**. The ten letters are
 *   the only trainee-side signal in the schema; whether one of them is "ideal" is a judgement no column
 *   holds, and labelling it as one would be the invented claim `AGENTS.md` §5 forbids.
 * - **"Recommended stat distribution", "useful inheritance", "useful support types"** are not rendered at
 *   all: the Build analysis section that held them was deleted in slice 3.4. `support_cards` deliberately
 *   carries no `umamusume_id`
 *   (`database/migrations/2026_09_30_151945_correct_support_card_schema_and_constraints.php:36-38`: "None
 *   is added here: nothing in this slice reads a support card's trainee through the local catalogue"), so
 *   a useful-support-types list would have to join on `char_name` *text* — a name match, not data.
 *   "Useful inheritance" is the inheritance computation `ADR-0020` §3 bans, and a recommended stat
 *   distribution is a recommendation, which the Planner's no-prediction rule (PRD §6) also declines.
 *
 * **Not cached** (`AGENTS.md` §8). One indexed lookup on the primary key plus a bounded card load and one
 * skill lookup.
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
        // The profile is step 2's middle, so opening it is walking the step: the shell's chips read the
        // same `visited`/`saved` lists every other step sends.
        SetupDraft::markVisited(2);

        $umamusume->loadMissing(['cards' => $this->cardScope()]);

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
            'visited' => SetupDraft::visited(),
            'choices' => SetupDraft::summary(),
            'saved' => SetupDraft::saved(),
            'trainee' => [
                'id' => $umamusume->id,
                'name' => $umamusume->name,
                'name_ja' => $umamusume->name_ja,
                'aptitudes' => $this->aptitudes($umamusume),
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
            // Continuing from this screen writes the viewed trainee, so the form carries the costume form
            // only when the draft already names this same trainee: a switch to another trainee must clear
            // the old form (R3-06), and the store's exists-join would refuse a card from the old one.
            'selectedCardId' => $draft['umamusume_id'] === $umamusume->id ? SetupDraft::characterCardId() : null,
            // The wireframe's linked-scenarios pills. No per-trainee link list exists on this payload
            // (the wireframe's own note says the same), so the labels are the scenarios this tool can
            // run, read from config in display order.
            'scenarioLinks' => $this->scenarioLabels(),
            'scenarioLabel' => SetupDraft::scenarioLabel(),
            'scenarioPending' => $draft['scenario'] === null,
            // The roster, named server-side because the page's back door must not hard-code a route.
            'rosterUrl' => route('career.trainee'),
            // The catalog detail's select action posts to the same store route as the roster. The URL is
            // supplied server-side the way CareerLayout.vue:11 and UnityCupEditor.vue:20 already receive
            // URLs. No Ziggy dependency is introduced.
            'action' => route('career.trainee.store'),
            // Two named absences, one reason each. The catalogue stores neither figure: the Growth Rate
            // row is `stat_bonus`, absent from every table this tool owns, and the base five are the
            // export's starting allocation, which the catalogue also does not carry. A reason per block
            // rather than per cell keeps five identical disclosures from taxing the reader.
            'absences' => [
                'growth' => [
                    'value' => null,
                    'absence' => 'No growth figure is stored for any costume form, so no percentage is known for any axis. The client prints the row as five cells and a real zero as +0%; here each cell states N/A so an absent figure is never read as a zero.',
                ],
                'baseStats' => [
                    'value' => null,
                    'absence' => "No base-stat figure is stored, so none of the five is known before a run exists. The export's five base values sum to 450 for a three-star card, 425 for two stars and 400 for one; those are starting totals, not a ceiling.",
                ],
            ],
        ]);
    }

    /**
     * The scenario labels this tool can run, in config order, for the linked-scenarios pills. The
     * labels live in `config/scenarios.php`; a component or page that hard-coded a scenario name would
     * be the defect `AGENTS.md` §7 names.
     *
     * @return list<string>
     */
    private function scenarioLabels(): array
    {
        $scenarios = config('scenarios.scenarios');

        if (! is_array($scenarios)) {
            return [];
        }

        $labels = [];

        foreach ($scenarios as $scenario) {
            if (is_array($scenario) && is_string($scenario['label'] ?? null)) {
                $labels[] = $scenario['label'];
            }
        }

        return $labels;
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
