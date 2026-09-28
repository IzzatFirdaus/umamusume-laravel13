<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Records what the Trainer did with one calendar slot (US-10, ADR-0003).
 *
 * Every field is entered. The tier a finish is priced against lives on the slot, so a
 * race is entered against a slot and never given a grade of its own; the circles and
 * the period index are the two facts the client shows that no slot can speak for.
 */
class StoreRaceEntryRequest extends FormRequest
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
            // A Trackblazer run has no calendar to enter against, so a slot is optional
            // at the boundary and the model's own guard decides what a slot-less entry can
            // ever be priced as. An unknown id is rejected outright.
            'scenario_slot_id' => [
                'nullable',
                'integer',
                Rule::exists('scenario_slots', 'id'),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $slot = ScenarioSlot::find($value);
                    $run = $this->route('run');

                    if ($slot === null || ! $run instanceof TrainingRun || $slot->scenario_key !== $run->scenarioKey()) {
                        $fail('That race is not on this run\'s calendar.');
                    }
                },
            ],
            'status' => ['required', Rule::enum(RaceEntryStatus::class)],
            'placement' => ['nullable', 'integer', 'min:1', 'max:99'],
            'fans_gain' => ['nullable', 'integer', 'min:0'],
            // 0..5 is this slice's validation bound, not a published maximum; the model
            // refuses the same range and also refuses circles on a non-team slot.
            'circles' => ['nullable', 'integer', Rule::in(range(0, RaceEntry::MAX_CIRCLES))],
            'objective_index' => ['nullable', 'integer', Rule::in(range(1, RaceEntry::MAX_OBJECTIVE_INDEX))],
        ];
    }

    protected function prepareForValidation(): void
    {
        // An empty select posts "", which would otherwise arrive as a zero and silently
        // become "period 0" or "no circles read as zero" (D-220).
        foreach (['scenario_slot_id', 'placement', 'circles', 'objective_index', 'fans_gain'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }
}
