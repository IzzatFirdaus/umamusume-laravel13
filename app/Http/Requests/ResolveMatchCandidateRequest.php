<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a review verdict (PRD FR-B-3, US-5). Aliased requires a language;
 * umamusume_id overrides the engine's fuzzy suggestion as the merge target.
 */
class ResolveMatchCandidateRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1', 'max:2147483647'],
            // `Pending` is not a verdict. It used to be accepted: `ResolveMatchCandidate::handle()`
            // mapped it to `null` work and then stamped the row `Pending` and saved, so the endpoint
            // answered success and flashed "Candidate resolved." while changing nothing. Reopen is the
            // door back to Pending, and the queue exposes it as its own action, so accepting the word
            // here was an offer the queue never needed and could not honour.
            'status' => [
                'required',
                Rule::enum(CandidateStatus::class)->except([CandidateStatus::Pending]),
            ],
            'umamusume_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            // Aliased needs a target to hang the alias on. `alias()` returned early when the target was
            // null while `handle()` stamped the row regardless, so a suggestion-less candidate could
            // leave the queue marked Aliased with no alias recorded anywhere: the one non-Reject door
            // available to those rows did nothing at all, silently. The target is either named in the
            // post or the row already carries a suggestion, which is what `withValidator` checks.
            'alias_language' => ['nullable', Rule::enum(AliasLanguage::class), 'required_if:status,Aliased'],
        ];
    }

    /**
     * The validated verdict, narrowed to the shape `ResolveMatchCandidate::handle()` reads.
     *
     * @return array{status: string, umamusume_id?: int|null, alias_language?: string|null}
     */
    public function verdict(): array
    {
        $validated = $this->validated();

        $verdict = ['status' => (string) $validated['status']];

        if (array_key_exists('umamusume_id', $validated)) {
            $verdict['umamusume_id'] = $validated['umamusume_id'] === null ? null : (int) $validated['umamusume_id'];
        }

        if (array_key_exists('alias_language', $validated)) {
            $verdict['alias_language'] = $validated['alias_language'] === null ? null : (string) $validated['alias_language'];
        }

        return $verdict;
    }
}
