<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DataSource;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Catalog row in the API contract's camelCase shape; aliases and sources
 * appear only when eager-loaded (ARCHITECTURE §4).
 *
 * @mixin Umamusume
 */
class UmamusumeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'nameJa' => $this->name_ja,
            'releaseStatus' => $this->release_status->value,
            'jpDebutDate' => $this->jp_debut_date?->toDateString(),
            'globalDebutDate' => $this->global_debut_date?->toDateString(),
            'isManual' => $this->is_manual,
            'aliases' => $this->whenLoaded('aliases', fn () => $this->aliases->map(fn (UmamusumeAlias $alias): array => [
                'alias' => $alias->alias,
                'language' => $alias->language->value,
            ])),
            'sources' => $this->whenLoaded('dataSources', fn () => $this->dataSources->map(fn (DataSource $source): array => [
                'sourceKey' => $source->source_key,
                'url' => $source->url,
                'fetchedAt' => $source->fetched_at->toIso8601String(),
                'confidence' => $source->confidence,
            ])),
        ];
    }
}
