<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReleaseStatus;
use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A catalog skill available to attach to runs (PRD FR-D-1).
 *
 * Two columns store what a source states rather than what this tool concludes, and both exist because
 * the difference is the thing that has been got wrong here before:
 *
 * - `rarity` is the export's class code (1..6 = learnable / evolvable / unique variants / evolved), not
 *   the client's three rarities. `ADR-0011` §3 keeps it auditable and unlabelled, the way
 *   `race_catalog_slots` keeps `grade_code` beside a `tier` it could pin.
 * - `name` is the display string, and `name_is_client` says whether it came from the client at all.
 *   `enname` in the source is a literal rendering of the Japanese and differs from the client string on
 *   535 of the 623 Global rows; a surface that reads `name` without the flag can print a third-party
 *   label as client copy, which is `UMAMUSUME_REFERENCE.md` §2.7's ruling and D-20.
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_ja
 * @property string|null $match_key
 * @property int|null $sp_cost
 * @property string|null $type
 * @property list<array{base_time: int|null, condition: string|null, precondition: string|null,
 *                     effects: list<array{type: int, value: int}>}>|null $condition_groups
 *           The source's own activation predicate and effect vector, as it states them.
 * @property bool $is_unique
 * @property int|null $export_id The skill id in the declared source, not this table's primary key.
 * @property int|null $rarity The source's class code. Never rendered as a client rarity word.
 * @property ReleaseStatus|null $release_status Null while no source has stated availability.
 * @property bool $name_is_client True only when `name` is the string the client itself prints.
 * @property string|null $source_url
 * @property string|null $snapshot_path
 * @property Carbon|null $fetched_at
 * @property string|null $source_timezone
 * @property bool $is_manual
 * @property-read RunSkill $pivot Present only when loaded through the run_skills relation.
 */
#[Fillable([
    'name',
    'name_ja',
    'match_key',
    'sp_cost',
    'type',
    'condition_groups',
    'is_unique',
    'export_id',
    'rarity',
    'release_status',
    'name_is_client',
    'source_url',
    'snapshot_path',
    'fetched_at',
    'source_timezone',
    'is_manual',
])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * The rows a Global Trainer can actually meet in their client.
     *
     * `ADR-0011` §2 records availability at write time and applies it at read time, so every
     * Trainer-facing surface — the run screen's skill select, FR-D-2's search, the CSV/JSON export, the
     * `/api/v1` run resource — starts here. It is a scope rather than four `where` clauses because a
     * filter copied per screen is a filter that drifts on the screen nobody remembered.
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeAvailableOnGlobal(Builder $query): Builder
    {
        return $query
            ->where('release_status', ReleaseStatus::GlobalReleased->value)
            ->where('name_is_client', true);
    }

    protected function casts(): array
    {
        return [
            'sp_cost' => 'integer',
            'condition_groups' => 'array',
            'is_unique' => 'boolean',
            'export_id' => 'integer',
            'rarity' => 'integer',
            'release_status' => ReleaseStatus::class,
            'name_is_client' => 'boolean',
            'is_manual' => 'boolean',
            'fetched_at' => 'datetime',
        ];
    }
}
