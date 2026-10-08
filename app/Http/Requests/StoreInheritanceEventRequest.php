<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Records one observed inheritance event on a run (`SCR-CAR-015`, D12).
 *
 * This is the write the Inheritance screen posts through. It carried its rules inline in
 * `InheritanceEventController::store()` until KI-68 moved them here, unchanged: an inline
 * `request()->validate()` is a Floor breach (`AGENTS.md` §5) and left the boundary with two
 * implementations to keep in step. `event_type` is deliberately not a field of this request —
 * the controller sets it to `TurnEventType::Inheritance` itself, which is why
 * `StoreTurnEventRequest::SOURCES` (the four choice cases) is neither used nor widened here.
 */
class StoreInheritanceEventRequest extends FormRequest
{
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
            'turn' => ['required', 'integer', 'min:1', 'max:72'],
            'source_name' => ['required', 'string', 'max:100'],
            'choice_label' => ['nullable', 'string', 'max:200'],
            'deltas' => ['nullable', 'array'],
            'origin_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
