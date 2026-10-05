<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\TrainingRun;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The two-run selector on the Legacy Lab compare surface (PRD FR-G-2, `ADR-0020` §3).
 *
 * `design-2.0` §46 asks for up to four columns aligned in rows. A row is a run, and the runs it can
 * name are the ones that carry a Legacy read-back, so this refuses a run that has none rather than
 * printing an all-`N/A` column: a column of absences is a screen that looks compared and is not.
 */
class LegacyCompareRequest extends FormRequest
{
    public const MAX_RUNS = 4;

    /**
     * @var string
     */
    protected $redirectRoute = 'legacy.index';

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
            'runs' => ['nullable', 'array', 'max:'.self::MAX_RUNS],
            'runs.*' => ['integer', 'distinct', 'exists:training_runs,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'runs.max' => 'Compare at most '.self::MAX_RUNS.' runs at once.',
            'runs.*.exists' => 'That run is not in this library.',
        ];
    }

    /**
     * The run ids in the order posted, so the columns keep the Trainer's own order rather than the
     * order the ids happen to sort into.
     *
     * @return list<int>
     */
    public function runIds(): array
    {
        /** @var list<int|string> $ids */
        $ids = (array) ($this->validated()['runs'] ?? []);

        return array_map(static fn (int|string $id): int => (int) $id, $ids);
    }

    /**
     * The selected runs in the order the request named them, each with the relations the columns print.
     *
     * Two decisions here, both about what a column is allowed to be.
     *
     * **The re-key is the point of the method.** `whereIn` answers in whatever order the database
     * finds cheapest, so a request naming runs 9, 3, 7 would print its columns shuffled — and a
     * comparison of the wrong two things reads as a correct answer.
     *
     * **The query is on `training_runs`, not on `veterans`.** A run can carry a Legacy selection and
     * never have been saved to the Veteran library; the selection is the payload on the run, and the
     * library row is a separate, optional record of it. Querying `veterans` and skipping a run with no
     * row would silently drop exactly the run a Trainer is most likely to be inspecting — the one they
     * are about to save. So every requested run becomes a column, and the library row (its tags and
     * notes) rides along when it exists and is `null` when it does not.
     *
     * `FR-G-4` holds here too: nothing sorts the result on a computed value.
     *
     * @param  list<int>  $ids
     * @return list<TrainingRun>
     */
    public function runsInOrder(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $found = TrainingRun::query()
            ->with(['umamusume', 'inheritanceParentA', 'inheritanceParentB', 'veteran'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $ordered = [];

        foreach ($ids as $id) {
            $run = $found->get($id);

            if ($run !== null) {
                $ordered[] = $run;
            }
        }

        return $ordered;
    }
}
