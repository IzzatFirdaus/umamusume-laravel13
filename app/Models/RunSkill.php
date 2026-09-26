<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot row for run_skills (ARCHITECTURE §3). Columns are read as stored strings;
 * the enum lives on the write side (TrainingRun::setSkillStatus).
 *
 * @property string $status
 * @property int|null $turn_acquired
 */
class RunSkill extends Pivot
{
    protected $table = 'run_skills';

    protected $primaryKey = null;

    public $incrementing = false;
}
