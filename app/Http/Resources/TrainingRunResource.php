<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Skill;
use App\Models\TrainingRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Run detail: nested turns and per-skill pivot status; skill rows come from
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
        ];
    }
}
