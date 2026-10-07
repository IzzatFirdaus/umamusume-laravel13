<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Veteran;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The career selector on the Veteran comparison (`GET /veterans/compare`, PRD FR-G-2, `SCREEN-022`,
 * `design-2.0` §46).
 *
 * **Why this is a second selector rather than `LegacyCompareRequest` again.** That one refuses a run with no
 * Legacy read-back, and its docblock gives the reason: on a surface about configurations, a column of
 * absences looks compared and is not. The comparison the library offers is about *careers*, and the career a
 * Trainer most wants to look at beside another is the one they just filed and have not built a Legacy on —
 so a rule that drops it would remove the subject of the screen. Different eligibility, different request.
 * The shared part, four columns and no more, is `design-2.0` §46's own limit and is restated as a constant
 * the page prints rather than a second number the page could disagree with.
 *
 * Selection is by `veterans` ids, not run ids: this screen's units are library rows, and `Veteran` is the
 * row. A Veteran is unique per run (`create_veterans_table`), so the two never disagree about which career is
 * on screen.
 */
class VeteranCompareRequest extends FormRequest
{
    public const MAX_VETERANS = 4;

    /**
     * A refused selection goes back to the library, where the Trainer picks again.
     *
     * @var string
     */
    protected $redirectRoute = 'veterans.index';

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
            'veterans' => ['nullable', 'array', 'max:'.self::MAX_VETERANS],
            'veterans.*' => ['integer', 'distinct', 'exists:veterans,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'veterans.max' => 'Compare at most '.self::MAX_VETERANS.' careers at once.',
            'veterans.*.exists' => 'That career is not in the library.',
            'veterans.*.integer' => 'A comparison names a career in the library, not a value invented for it.',
        ];
    }

    /**
     * The requested ids in the order the URL named them.
     *
     * @return list<int>
     */
    public function veteranIds(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        /** @var list<int|string> $ids */
        $ids = (array) ($validated['veterans'] ?? []);

        return array_map(static fn (int|string $id): int => (int) $id, $ids);
    }

    /**
     * The selected careers in that order, each with the relations the columns print.
     *
     * The re-key is the point: `whereIn` answers in whatever order the database finds cheapest, so a URL
     * naming 9, 3, 7 would print shuffled columns, and a comparison of the wrong two things reads as a
     * correct answer. `FR-G-4` holds here too, so nothing sorts the result on a computed value.
     *
     * The eager-load list is the one `ShowVeteran` declares, repeated here as a query constraint rather than
     * a per-row call: four careers with their run, trainee, turns, skills and races is three queries, and
     * mapping each row through `ShowVeteran` instead would be one per career.
     *
     * @return list<Veteran>
     */
    public function veteransInOrder(): array
    {
        $ids = $this->veteranIds();

        if ($ids === []) {
            return [];
        }

        $found = Veteran::query()
            ->with([
                'trainingRun.umamusume',
                'trainingRun.turnEntries',
                'trainingRun.skills',
                'trainingRun.raceEntries',
                'trainingRun.inheritanceParentA',
                'trainingRun.inheritanceParentB',
            ])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $ordered = [];

        foreach ($ids as $id) {
            $veteran = $found->get($id);

            if ($veteran !== null) {
                $ordered[] = $veteran;
            }
        }

        return $ordered;
    }
}
