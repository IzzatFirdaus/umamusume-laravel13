<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A catalog skill available to attach to runs (PRD FR-D-1).
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_ja
 * @property string|null $match_key
 * @property int|null $sp_cost
 * @property string|null $type
 * @property bool $is_unique
 * @property-read RunSkill $pivot Present only when loaded through the run_skills relation.
 */
#[Fillable(['name', 'name_ja', 'match_key', 'sp_cost', 'type', 'is_unique'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sp_cost' => 'integer',
            'is_unique' => 'boolean',
        ];
    }
}
