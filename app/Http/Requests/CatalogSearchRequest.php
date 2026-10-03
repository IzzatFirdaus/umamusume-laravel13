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
        ];
    }

    /**
     * An empty `status=` is "no filter chosen", the same answer as omitting the key, so it becomes
     * null and passes as nullable rather than failing the `in` list as the empty string.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['status' => $this->input('status') === '' ? null : $this->input('status')]);
    }
}
