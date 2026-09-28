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
 * Records what the Trainer did with one calendar slot (US-10, ADR-0003), or
 * creates a manual slot for a race not on the calendar (R56, R61).
 *
 * Two paths share one endpoint: `entry_mode` selects between them. The calendar
 * path requires a `scenario_slot_id`; the manual path requires `title`, `month`
 * and `half`. Both produce a `race_entries` row. The manual path also produces
 * a `scenario_slots` row with kind `free_race`.
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
        $mode = $this->input('entry_mode', 'calendar');

        $rules = [
            'entry_mode' => ['required', Rule::in(['calendar', 'manual'])],
            'status' => ['required', Rule::enum(RaceEntryStatus::class)],
            'placement' => ['nullable', 'integer', 'min:1', 'max:99'],
            'fans_gain' => ['nullable', 'integer', 'min:0'],
            'circles' => ['nullable', 'integer', Rule::in(range(0, RaceEntry::MAX_CIRCLES))],
            'objective_index' => ['nullable', 'integer', Rule::in(range(1, RaceEntry::MAX_OBJECTIVE_INDEX))],
        ];

        if ($mode === 'manual') {
            $rules['title'] = ['required', 'string', 'min:1', 'max:255'];
            $rules['month'] = ['required', 'integer', 'min:1', 'max:12'];
            $rules['half'] = ['required', Rule::in(['Early', 'Late'])];
            $rules['tier'] = ['nullable', 'string', 'max:10'];
            $rules['scenario_slot_id'] = ['prohibited'];
        } else {
            $rules['scenario_slot_id'] = [
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
            ];
            $rules['tier_override'] = ['nullable', 'string', 'max:10'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        foreach (['scenario_slot_id', 'placement', 'circles', 'objective_index', 'fans_gain'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }

        if ($this->input('tier') === '') {
            $this->merge(['tier' => null]);
        }

        if ($this->input('tier_override') === '') {
            $this->merge(['tier_override' => null]);
        }

        if ($this->input('entry_mode') === null) {
            $this->merge(['entry_mode' => 'calendar']);
        }
    }

    public function isManualPath(): bool
    {
        return $this->validated('entry_mode') === 'manual';
    }
}
