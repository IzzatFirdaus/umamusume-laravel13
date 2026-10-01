<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Records what the Trainer did with one calendar slot (US-10, ADR-0003), or
 * creates a manual slot for a race not on the calendar (R56, R61).
 *
 * Two paths share one endpoint: `entry_mode` selects between them. The calendar
 * path names a row of the career catalogue with `race_catalog_slot_id`, or a race
 * the Trainer typed earlier with `scenario_slot_id`; the manual path requires
 * `title`, `month` and `half`. All three produce a `race_entries` row, and the
 * manual path also produces a `scenario_slots` row with kind `free_race`.
 *
 * `turn_entry_id` (KI-17) belongs to neither branch: a race happened on a turn whichever way it
 * got onto the calendar, so the rule sits with the shared outcome fields above the split.
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
            // B1. Circles are a team race read, so the value belongs to a `team_race` slot and to
            // nothing else. That rule used to live only in `RaceEntry`, which enforces it by
            // throwing, while the panel that offers the control cannot offer a team race slot: its
            // picker is the career catalogue plus the run's hand-entered `free_race` rows. So the
            // one path a Trainer can take ended in a 500 instead of a refusal, and `0` ended there
            // too, because the model reads null as "not recorded" and every other value as a claim.
            // A refused field is recoverable and an exception is not, so the gate moves to where the
            // rest of this payload's rules already live. The model guard stays behind this one for
            // writes that never pass through a Form Request.
            'circles' => ['nullable', 'integer', Rule::in(range(0, RaceEntry::MAX_CIRCLES)), function (string $attribute, mixed $value, Closure $fail): void {
                if ($value === null) {
                    return; // "not read" is an answer, and it is the one the empty option sends
                }

                $named = $this->input('scenario_slot_id');
                $slot = $named === null || $named === '' ? null : ScenarioSlot::find((int) $named);

                if ($slot === null || $slot->kind !== 'team_race') {
                    $fail('Circles can only be read against a team race.');
                }
            }],
            'objective_index' => ['nullable', 'integer', Rule::in(range(1, RaceEntry::MAX_OBJECTIVE_INDEX))],
            // KI-17: the turn this race was run on, named by the Trainer. A turn logged on another
            // run is not a turn of this run, and the dropdown cannot be trusted to have kept them
            // apart — a stale tab or a hand-edited post reaches this validator either way.
            'turn_entry_id' => ['nullable', 'integer', function (string $attribute, mixed $value, Closure $fail): void {
                if ($value === null) {
                    return;
                }

                $run = $this->route('run');
                $turn = TurnEntry::find($value);

                if ($turn === null || ! $run instanceof TrainingRun || $turn->training_run_id !== $run->id) {
                    $fail('That turn was not logged on this run.');
                }
            }],
        ];

        if ($mode === 'manual') {
            $rules['title'] = ['required', 'string', 'min:1', 'max:255'];
            $rules['month'] = ['required', 'integer', 'min:1', 'max:12'];
            $rules['half'] = ['required', Rule::in(['Early', 'Late'])];
            $rules['tier'] = ['nullable', 'string', 'max:10'];
            $rules['scenario_slot_id'] = ['prohibited'];
        } else {
            // The calendar branch names a row of the shared career catalogue. The
            // scenario-slot link stays valid because a Trainer-typed free race lives
            // there and nowhere else, but one entry cannot carry both: it would put the
            // same race on the grid twice, once from each source. `exclude_with` is not
            // the rule for that — it drops the field from `validated()` silently, which
            // would pick a winner instead of refusing the answer.
            $rules['race_catalog_slot_id'] = [
                'nullable',
                'integer',
                Rule::exists('race_catalog_slots', 'id'),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    if ($this->filled('scenario_slot_id')) {
                        $fail('A race is either a calendar race or one you entered by hand, not both.');

                        return;
                    }

                    $run = $this->route('run');

                    $onCalendar = $run instanceof TrainingRun
                        && RaceCatalogSlot::query()
                            ->forScenario($run->scenarioKey())
                            ->whereKey((int) $value)
                            ->exists();

                    if (! $onCalendar) {
                        $fail('That race is not on this run\'s calendar.');
                    }
                },
            ];
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
        foreach (['scenario_slot_id', 'race_catalog_slot_id', 'placement', 'circles', 'objective_index', 'fans_gain', 'turn_entry_id'] as $key) {
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
