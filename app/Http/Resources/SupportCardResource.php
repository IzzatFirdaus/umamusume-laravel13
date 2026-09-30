<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SupportCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A support card as the catalogue holds it, mirrored as sourced (no derived fields).
 *
 * Machine tokens throughout, matching the other three resources: `rarity` is the export's integer and
 * `type` the export's key, not the client's words for them. `SupportCard::rarityWord()` and
 * `typeLabel()` translate at the view boundary, and putting both spellings on the wire would give a
 * consumer two fields to reconcile with no rule for which wins.
 *
 * `effects` is the raw anchor vector rather than a levelled table. `release_status` is included because
 * it is a stored generated column the database already answers, not a label this layer computes.
 *
 * @mixin SupportCard
 */
class SupportCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supportId' => $this->support_id,
            'charId' => $this->char_id,
            'charName' => $this->char_name,
            'nameJa' => $this->name_ja,
            'titleEn' => $this->title_en,
            'titleJa' => $this->title_ja,
            'rarity' => $this->rarity->value,
            'type' => $this->type,
            'releaseJp' => $this->release_jp?->toDateString(),
            'releaseGlobal' => $this->release_global?->toDateString(),
            'releaseStatus' => $this->release_status,
            'effects' => $this->effects,
            'sourceUrl' => $this->source_url,
            'fetchedAt' => $this->fetched_at->toIso8601String(),
            'isManual' => $this->is_manual,
        ];
    }
}
