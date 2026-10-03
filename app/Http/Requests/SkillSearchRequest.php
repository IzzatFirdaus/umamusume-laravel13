<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataPipeline\Parsers\GametoraSkillsParser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The Screen D query (PRD FR-D-2, DESIGN.md §8.4, CONSTRAINTS.md D-62 to D-65).
 *
 * Read-only, so there is nothing to authorize: this tool has no auth surface (`PRD.md` NFR-1) and the
 * screen shows reference data the fetch engine wrote.
 *
 * **An unknown facet value is refused rather than ignored.** Dropping it would answer a question nobody
 * asked with the whole catalog, and a Trainer reads a full list as the answer to the facet they picked —
 * which is how a filter lies. `page` and `pageSize` are the exception and are coerced in the controller:
 * a page past the end is an empty page, and neither can mislead anyone about the data.
 *
 * `type` accepts only the three words `GametoraSkillsParser` can derive, plus {@see self::UNSPECIFIED},
 * because the null is a state the data holds: 288 of the 623 Global rows carry no type at all under the
 * sign rule (`ADR-0011` §4, G-SK-18), and a filter that cannot reach them implies they are missing
 * something. `rarity` is deliberately absent: it is the export's class code with no client label
 * (`ADR-0011` §3), so a control over it would ask a Trainer to choose between numbers they cannot read.
 */
class SkillSearchRequest extends FormRequest
{
    /**
     * The URL token for "the derivation produced no word". Not a `skills.type` value — no row stores it.
     */
    public const UNSPECIFIED = 'Unspecified';

    /**
     * Where a refused facet lands (SCREEN_SPEC.md §7-9, ADR-0018). Support cards already named its
     * route for this reason; leaving it unset sends the refusal to `previous()`, which is the
     * referer when the browser supplies one and `/` when it does not, so the field error would
     * arrive on the run list instead of beside the picker that caused it.
     *
     * @var string
     */
    protected $redirectRoute = 'skills.index';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in([...GametoraSkillsParser::CATEGORIES, self::UNSPECIFIED])],
            'unique' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // A GET form submits an empty text field as `""`, and an empty search means "no query", not "find
        // the empty string" — which as a LIKE pattern would match every row.
        $this->merge([
            'search' => $this->input('search') === '' ? null : $this->input('search'),
            'type' => $this->input('type') === '' ? null : $this->input('type'),
            'unique' => $this->input('unique') === '' ? null : $this->input('unique'),
        ]);
    }
}
