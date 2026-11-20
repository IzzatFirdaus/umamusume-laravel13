<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CandidateStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a bulk review verdict (N2.8).
 *
 * Reject-only is a contract, not a UI convention: `ResolveMatchCandidate` refuses a non-Reject
 * verdict for a race candidate, and the 304-row backlog this endpoint exists to dispose of is all
 * race rows. Accepting a promotion here would offer a button that throws.
 */
class BulkRejectRequest extends FormRequest
{
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
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', Rule::exists('match_candidates', 'id')],
            'status' => ['required', Rule::in([CandidateStatus::Rejected->value])],
        ];
    }

    /**
     * @return list<int>
     */
    public function candidateIds(): array
    {
        return array_map('intval', (array) $this->validated('ids'));
    }
}
