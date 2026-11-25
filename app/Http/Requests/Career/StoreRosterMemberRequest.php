<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use App\Enums\RosterDistance;
use App\Models\RosterMember;
use App\Models\TrainingRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The validated shape of one roster member write (D01).
 *
 * Used by both the create and the update door, so the two cannot disagree about what a member is.
 * The update path binds `{member}` and reads its `training_run_id` for the roster-size guard, because
 * the route nests the member under its run and a member posted to another run's roster is refused
 * before validation rather than after.
 *
 * **Only `name` is required.** Every figure is nullable because a member the Trainer added by name
 * has no reading yet, and a fabricated stat would be a claim about a record that does not exist
 * (D-220). The client's own stat line is what the Trainer reads off the member's card, so it is
 * entered rather than derived.
 *
 * **One ACE per distance column.** The client shows exactly one ACE per column (§1.8), and the model
 * guard enforces the same rule; this rule catches it at the boundary with a message the form prints
 * beside the field rather than as a runtime exception.
 *
 * @see RosterMember::MAX_MEMBERS
 */
class StoreRosterMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No authorization layer in this repository (PRD NFR-1, `.ai/rules/architecture.md`): the
        // run binding IS the boundary, and the controller refuses a member posted to another run.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            // Nullable: a Guest R card §1.9 names may have no row in this tool's catalogue.
            'umamusume_id' => ['nullable', 'integer', Rule::exists('umamusume', 'id')],
            // The client's own member grade letters, the same vocabulary the aptitudes use.
            'grade' => ['nullable', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            // The client's stat line, read off the member's card. Bounded by the engine's own
            // hard cap rather than a flat number, the same way `ScenarioCaps` bounds a run's stats.
            'speed' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'stamina' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'power' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'guts' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'wit' => ['nullable', 'integer', 'min:0', 'max:2000'],
            // The cap the client prints beside each stat (§1.9). Separate from the stat because a
            // member at her ceiling and a member with room left are different facts, and the same
            // 2000 bound applies: §1.9's highest recorded cap is 1140, so 2000 is slack rather
            // than a reading.
            'speed_cap' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'stamina_cap' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'power_cap' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'guts_cap' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'wit_cap' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'distance_assignment' => ['nullable', 'string', Rule::enum(RosterDistance::class)],
            'is_ace' => ['nullable', 'boolean'],
            'is_trainee' => ['nullable', 'boolean'],
            // Where the member came from (UC3.2). The four arms live on the model; `unknown` is
            // also the column default, so an omitted origin records the honest absence.
            'origin' => ['nullable', 'string', Rule::in(RosterMember::ORIGINS)],
            // The exact support card a support-derived member joined through (UC3.2). Character
            // identity is not card identity, so the FK points at `support_cards`, not at a name.
            'support_card_id' => ['nullable', 'integer', Rule::exists('support_cards', 'id')],
            // The ten observed aptitude letters (UC3.4), partial by nature: §1.9's lines name the
            // letters worth reading out, and every axis the Trainer did not observe stays null. The
            // letter vocabulary is the client's own, the same one `grade` uses.
            ...collect(RosterMember::APTITUDE_COLUMNS)
                ->mapWithKeys(static fn (string $column): array => [
                    $column => ['nullable', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'umamusume_id.exists' => 'That trainee is not in this tool’s catalogue.',
            'grade.in' => 'A member grade is one of G, F, E, D, C, B, A or S.',
        ];
    }

    /**
     * The roster this write belongs to, resolved from the nested binding.
     */
    public function rosterRun(): ?TrainingRun
    {
        $member = $this->route('member');

        return $member instanceof RosterMember ? $member->trainingRun : $this->route('run');
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        // `position` is the caller's, not the Trainer's: the create door appends, the update door
        // leaves a member's place alone. It is dropped here so a posted position cannot reorder the
        // roster behind the editor's back.
        unset($validated['position']);

        return $validated;
    }
}
