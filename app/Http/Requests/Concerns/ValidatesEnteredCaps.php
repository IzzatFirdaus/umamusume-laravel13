<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Models\TrainingRun;
use App\Services\ScenarioCaps;
use Illuminate\Validation\Validator;

/**
 * The ceilings a Trainer enters for one run (`ADR-0025`), shared by the two writes that may set them: the
 * cockpit's identity form and the build target step, in both its run-scoped and wizard shapes.
 *
 * One definition rather than three, because a stored ceiling that the turn validator then contradicts is
 * the exact disagreement `ADR-0015` was written to end.
 */
trait ValidatesEnteredCaps
{
    /**
     * Each entered column takes a whole number up to the engine hard cap, or nothing at all.
     *
     * The foot is 1, not the scenario's own base: the tool holds no source claiming a client ceiling can
     * never sit below the scenario figure, and inventing that claim is the error this decision exists to
     * avoid. The hard cap is a bound it does hold, and it is read from the ceiling owner rather than
     * written out here.
     *
     * @return array<string, mixed>
     */
    protected function enteredCapRules(): array
    {
        $rules = [];

        foreach (ScenarioCaps::ENTERED_CAPS as $column) {
            $rules[$column] = ['nullable', 'integer', 'min:1', 'max:'.ScenarioCaps::hardCap()];
        }

        return $rules;
    }

    /**
     * The ceilings keyed by their columns, ready for a model write.
     *
     * A column the request carries answers an int or null (null is the Trainer clearing it); a column it
     * does not carry is left out entirely, so a write that names no ceiling cannot blank one the run
     * already holds. That is what lets the status, scenario and period forms save without touching these.
     *
     * @return array<string, int|null>
     */
    public function postedCaps(): array
    {
        $caps = [];

        foreach (ScenarioCaps::ENTERED_CAPS as $column) {
            if (! $this->has($column)) {
                continue;
            }

            $value = $this->input($column);
            $caps[$column] = $value === null || $value === '' ? null : (int) $value;
        }

        return $caps;
    }

    /**
     * The run as this request would leave it, ceilings included, so the ceiling owner answers with the
     * numbers being saved rather than the ones saved before the save.
     *
     * A copy, never the route-bound model: nothing here is written until the request's own caller says so.
     */
    protected function ceilingView(?TrainingRun $run): ?TrainingRun
    {
        $caps = $this->postedCaps();

        if ($caps === []) {
            return $run;
        }

        $view = $run === null ? new TrainingRun : clone $run;
        $view->forceFill($caps);

        return $view;
    }

    /**
     * Refuse a ceiling that would leave a recorded stat above its own bound.
     *
     * Both halves of that sentence matter: entering a lower figure and clearing an entered one are the
     * same statement about the run, because a cleared ceiling reverts to the scenario bound and a stored
     * row can sit above that too. Nothing derives or rewrites the recorded row; the ceiling is what moves.
     */
    protected function assertCapsCoverTheRecordedStats(Validator $validator, ?TrainingRun $run): void
    {
        $caps = $this->postedCaps();

        if ($caps === [] || $run === null || ! $run->exists) {
            return;
        }

        $bound = ScenarioCaps::forRun($this->ceilingView($run));

        foreach (ScenarioCaps::ENTERED_CAPS as $stat => $column) {
            if (! array_key_exists($column, $caps)) {
                continue;
            }

            $recorded = $run->turnEntries()->max(strtolower($stat));

            if (is_numeric($recorded) && (int) $recorded > $bound[$stat]) {
                // D-56: the refusal names both figures, so the Trainer sees which reading blocks the
                // ceiling rather than only that something was too low.
                $validator->errors()->add(
                    $column,
                    "{$stat} has a turn recorded at ".(int) $recorded.', so its ceiling cannot sit below that.',
                );
            }
        }
    }
}
