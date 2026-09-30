<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DeckSlot;
use App\Models\Skill;
use App\Models\TrainingRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Run detail: nested turns, per-skill pivot status and the equipped support deck; skill rows come from
 * the run_skills pivot via the Skill::pivot annotation.
 *
 * @mixin TrainingRun
 */
class TrainingRunResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'umamusume' => new UmamusumeResource($this->whenLoaded('umamusume')),
            'scenario' => $this->scenario,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'turns' => TurnEntryResource::collection($this->whenLoaded('turnEntries')),
            'skills' => $this->whenLoaded('skills', fn () => $this->skills->map(fn (Skill $skill): array => [
                'id' => $skill->id,
                'name' => $skill->name,
                'status' => $skill->pivot->status,
                'turnAcquired' => $skill->pivot->turn_acquired,
            ])),
            // Ordered by slot_position at the relation, so the consumer reads the deck in the order the
            // client lays it out. A run with no recorded deck yields an empty list, which is a true
            // statement about that run; a run whose deck was not loaded omits the key entirely, so an
            // absent deck is never read as an unassigned one.
            'deck' => $this->whenLoaded('deckSlots', fn () => $this->deckSlots->map(
                fn (DeckSlot $slot): array => [
                    'slotPosition' => $slot->slot_position,
                    'isFriendSlot' => $slot->isFriendSlot(),
                    'supportCard' => new SupportCardResource($slot->supportCard),
                ]
            )),
        ];
    }
}
