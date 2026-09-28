<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TurnEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A single logged turn, mirrored as entered (no derived fields).
 *
 * @mixin TurnEntry
 */
class TurnEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'turn' => $this->turn,
            'speed' => $this->speed,
            'stamina' => $this->stamina,
            'power' => $this->power,
            'guts' => $this->guts,
            'wit' => $this->wit,
            'sp' => $this->sp,
            'condition' => $this->condition,
            // Appended after the original keys so a consumer reading the leading fields
            // by position still gets the same ones. These three are logged by the guided
            // form and shown on the run screen; omitting them meant the exported run was
            // not the run the Trainer looked at (audit F-9).
            'energy' => $this->energy,
            'mood' => $this->mood?->value,
            'fans' => $this->fans,
        ];
    }
}
