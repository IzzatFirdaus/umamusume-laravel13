<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ReleaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The release-status facet of the catalog (PRD FR-B-1; SCREEN_SPEC.md §7-9, ADR-0018).
 *
 * §7-9's inconsistency was that this surface *ignored* an unknown `status` and answered with the
 * GlobalReleased default, while skills and support cards refused theirs. Defaulting is the right
 * answer to "no filter chosen" and the wrong answer to "filter by a status that does not exist":
 * the Trainer gets a page that looks filtered and was filtered by nothing they asked for, which is
 * how a typo survives a session. The value is validated here and a bad one is refused.
 *
 * The refusal lands on the canonical URL rather than `previous()`: support cards already names its
 * route for that reason, and an unnamed redirect resolves to the referer when there is one and to
 * `/` when there is not, which throws the Trainer off the screen whose field error they need.
 *
 * `all` is not a `ReleaseStatus` case. It is this surface's token for the unfiltered list, so it
 * joins the accepted vocabulary explicitly instead of being one of the values the rule would
 * otherwise reject.
 */
class CatalogSearchRequest extends FormRequest
{
    public const ALL = 'all';

    /**
     * The three aptitude facets, as the COLUMN each one filters (`catalog.html:75-77`).
     *
     * An aptitude is not a value on a lookup table here — it is ten columns on `umamusume`, each
     * holding a letter grade: `aptitude_turf`/`aptitude_dirt` (surface), `aptitude_sprint`/`_mile`/
     * `_medium`/`_long` (distance), and `aptitude_front_runner`/`_pace_chaser`/`_late_surger`/
     * `_end_closer` (style). So a facet's vocabulary is the set of KEYS below, and the value a
     * Trainer picks names a column rather than a grade. Filtering by grade would be a different
     * feature: the wireframe's rows read "Turf A / Medium A / Pace A", and the select chooses the
     * axis, not the letter.
     *
     * Declared as constants so `FilterContractTest` can assert the refusal against the same list
     * the rule uses, rather than restating it (ADR-0018: the vocabulary is read off the constant the
     * schema also reads).
     */
    public const SURFACES = ['turf', 'dirt'];

    public const DISTANCES = ['sprint', 'mile', 'medium', 'long'];

    public const STYLES = ['front_runner', 'pace_chaser', 'late_surger', 'end_closer'];

    /**
     * @var string
     */
    protected $redirectRoute = 'catalog.index';

    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in([
                self::ALL,
                ...array_map(static fn (ReleaseStatus $case): string => $case->value, ReleaseStatus::cases()),
            ])],
            // The three aptitude facets. Each is refused on an unknown value and lands on the
            // canonical catalog, which is the same contract `status` already carries — a filter that
            // silently answers with the unfiltered list is a filter that lies (ADR-0018).
            'surface' => ['nullable', Rule::in(self::SURFACES)],
            'distance' => ['nullable', Rule::in(self::DISTANCES)],
            'style' => ['nullable', Rule::in(self::STYLES)],
        ];
    }

    /**
     * An empty `status=` / `surface=` / … is "no filter chosen", the same answer as omitting the key,
     * so it becomes null and passes as nullable rather than failing the `in` list as the empty string.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status') === '' ? null : $this->input('status'),
            'surface' => $this->input('surface') === '' ? null : $this->input('surface'),
            'distance' => $this->input('distance') === '' ? null : $this->input('distance'),
            'style' => $this->input('style') === '' ? null : $this->input('style'),
        ]);
    }
}
