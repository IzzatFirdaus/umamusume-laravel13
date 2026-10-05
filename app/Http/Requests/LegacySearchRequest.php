<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The Veteran library's filter set for the Legacy Lab browse surface (PRD FR-G-2, `ADR-0020` §3).
 *
 * These are exactly the three filters `ListVeterans` implements: `trainee`, `scenario` and `tags`.
 * The screen spec's own candidate list names six more (blue / pink / green / white Sparks, running
 * style, skills, race history, scenario factor, Affinity) and C3 answers none of them, so none of them
 * appear here. A facet the query cannot answer is a facet that would answer every row, which is a
 * filter that does not filter. Distance, surface, style and Spark type are searchable — they are the
 * Trainer's own tag vocabulary (`ListVeterans`'s docblock, `design-2.0` SCREEN-020) and arrive through
 * `tags`; they are not columns and no Spark is derived from a tag.
 *
 * An unknown `scenario` is refused rather than ignored, on the `CatalogSearchRequest` precedent
 * (`ADR-0018`): a Trainer who types a scenario that does not exist gets the canonical list back with
 * the field named, not a page that looks filtered and was filtered by nothing they asked for.
 */
class LegacySearchRequest extends FormRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'legacy.index';

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
            'trainee' => ['nullable', 'integer', 'exists:umamusume,id'],
            'scenario' => ['nullable', 'string', Rule::in(array_keys(config('scenarios.scenarios')))],
            // One free-text tag. `ListVeterans` is an all-of query over a json list, so this is one
            // tag per submit rather than a multi-select the query would then have to widen for.
            'tag' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scenario.in' => 'That is not one of the four Global scenarios.',
        ];
    }

    /**
     * Empty means "no filter chosen", the same answer as omitting the key, so each one becomes null
     * rather than failing its rule as the empty string (`CatalogSearchRequest::prepareForValidation`).
     */
    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach (['trainee', 'scenario', 'tag'] as $key) {
            $value = $this->input($key);

            $clean[$key] = is_string($value) && trim($value) === '' ? null : $value;
        }

        $this->merge($clean);
    }

    /**
     * The filter array `ListVeterans::handle()` takes. A blank tag is no tag, and the action treats an
     * empty list as no filter, so a cleared field does not narrow the library to nothing.
     *
     * @return array{trainee: int|null, scenario: string|null, tags: list<string>}
     */
    public function filters(): array
    {
        /** @var array{trainee?: int|null, scenario?: string|null, tag?: string|null} $validated */
        $validated = $this->validated();

        $tag = $validated['tag'] ?? null;

        return [
            'trainee' => isset($validated['trainee']) ? (int) $validated['trainee'] : null,
            'scenario' => $validated['scenario'] ?? null,
            'tags' => $tag === null ? [] : [trim((string) $tag)],
        ];
    }
}
