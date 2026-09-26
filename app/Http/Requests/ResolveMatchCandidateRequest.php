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
}
