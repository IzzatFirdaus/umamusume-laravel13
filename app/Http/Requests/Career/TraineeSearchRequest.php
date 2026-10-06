<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The trainee roster's search, filters and sort (`SCREEN-003`, PRD FR-A-1, `ADR-0020` §1).
 *
 * **Every facet here is a real column.** `surface`, `distance` and `style` each name a group of
 * `umamusume.aptitude_*` columns, and a facet's value is one of the seven letters that group can hold.
 * The domain is not this file's invention: `GametoraCharacterParser.php:143` fixes it with
 * `preg_match('/^[SABCDEFG]$/')` before a letter is ever written, so nothing outside the seven can be in
 * the database and nothing outside the seven is accepted here. `S` is offered although the seeded roster
 * peaks at `A`: the parser can write it, so a filter that refused it would be a filter on this tool's
 * data rather than on the game's.
 *
 * Growth rate and scenario suitability, both in the SCREEN-003 brief, are absent because no column holds
 * either. `docs/research-scratch/GOVERNANCE.md:600` defers growth-rate storage ("No column added now")
 * and `docs/research-scratch/DESIGN-CORPUS.md:727-728` bans the figure from display; a `scenario_links`
 * suitability filter would read a cast list as an opinion. `TraineeSelectController`'s docblock carries
 * both citations, and a validation rule for a column that does not exist is not written.
 *
 * **Sort degrades; filters are refused.** A wrong letter or an unknown skill id is refused, because a
 * roster that quietly ignored a filter reads as "these are your matches" and a Trainer picks a trainee off
 * a list they believe was narrowed (`CatalogSearchRequest` states the same reasoning for `status`). A
 * wrong sort key is a *presentation* choice, and `?sortBy=` usually arrives from a pasted URL or a
 * restored tab, so it falls back to the default the way `PageSize::clamp` degrades rather than refuses.
 * The allowlist below is the reason the fallback is not an injection surface either: a key outside it
 * never reaches `orderBy()`.
 */
class TraineeSearchRequest extends FormRequest
{
    /**
     * The seven aptitude letters, best to worst, as the parser constrains them.
     */
    public const LETTERS = ['S', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];

    /**
     * The aptitude facets, each mapped to the `umamusume` columns it reads. A trainee matches a facet
     * when *any* column in the group holds the requested letter, because a filter on "Long" and one on
     * "Turf" answer different questions and neither is a subset of the other.
     */
    public const FACETS = [
        'surface' => ['aptitude_turf', 'aptitude_dirt'],
        'distance' => ['aptitude_sprint', 'aptitude_mile', 'aptitude_medium', 'aptitude_long'],
        'style' => ['aptitude_front_runner', 'aptitude_pace_chaser', 'aptitude_late_surger', 'aptitude_end_closer'],
    ];

    /**
     * The sort allowlist: the public key mapped to the column it orders. `name` and the ten aptitude
     * columns and nothing else, which is what makes an unknown key safe to degrade rather than dangerous
     * to pass through.
     */
    public const SORTS = [
        'name' => 'name',
        'turf' => 'aptitude_turf',
        'dirt' => 'aptitude_dirt',
        'sprint' => 'aptitude_sprint',
        'mile' => 'aptitude_mile',
        'medium' => 'aptitude_medium',
        'long' => 'aptitude_long',
        'front_runner' => 'aptitude_front_runner',
        'pace_chaser' => 'aptitude_pace_chaser',
        'late_surger' => 'aptitude_late_surger',
        'end_closer' => 'aptitude_end_closer',
    ];

    public const DEFAULT_SORT = 'name';

    /**
     * @var string
     */
    protected $redirectRoute = 'career.trainee';

    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $letters = [
            'nullable',
            'string',
            Rule::in(self::LETTERS),
        ];

        return [
            'search' => ['nullable', 'string', 'max:255'],
            'surface' => $letters,
            'distance' => $letters,
            'style' => $letters,
            // The skill's own export id, which is the value `character_cards.skills_unique` stores. An
            // id no skill row carries is refused rather than answered with an empty roster.
            'skill' => ['nullable', 'integer', Rule::exists('skills', 'export_id')],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'surface.in' => 'Aptitude letters are S, A, B, C, D, E, F and G.',
            'distance.in' => 'Aptitude letters are S, A, B, C, D, E, F and G.',
            'style.in' => 'Aptitude letters are S, A, B, C, D, E, F and G.',
            'skill.exists' => 'That skill id is not in this catalog, so no trainee could be filtered by it.',
        ];
    }

    /**
     * An empty facet is "no filter chosen", the same answer as omitting the key, so `?distance=` passes
     * as null instead of failing the letter list as the empty string. `CatalogSearchRequest` does this
     * for `status` for exactly this reason.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(array_reduce(
            ['search', 'surface', 'distance', 'style', 'skill', 'sortBy', 'direction'],
            fn (array $only, string $key): array => $only + [$key => $this->input($key) === '' ? null : $this->input($key)],
            [],
        ));
    }

    /**
     * The sort column this request resolves to, or null when the key is not on the allowlist. Null is
     * the degrade signal `TraineeSelectController` reads; it is never passed to `orderBy()` as given.
     */
    public function sortColumn(): ?string
    {
        $key = is_string($this->input('sortBy')) ? $this->input('sortBy') : null;

        return $key === null ? null : (self::SORTS[$key] ?? null);
    }

    /**
     * The requested sort key, degraded to the default. This is the value the page echoes back into the
     * select control, so the option the Trainer sees on screen is the ordering they actually got.
     */
    public function sortKey(): string
    {
        $key = is_string($this->input('sortBy')) ? $this->input('sortBy') : null;

        return $key === null || ! array_key_exists($key, self::SORTS)
            ? self::DEFAULT_SORT
            : $key;
    }
}
