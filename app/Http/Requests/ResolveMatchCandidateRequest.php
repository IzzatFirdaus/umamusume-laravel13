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
            'status' => ['required', Rule::enum(CandidateStatus::class)],
            'umamusume_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
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
