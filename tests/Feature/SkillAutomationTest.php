<?php

declare(strict_types=1);

use App\Services\SkillExecutor;
use App\Services\SkillMatcher;
use App\Services\SkillRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    if (! File::exists(base_path('.agents/skills.json'))) {
        $this->markTestSkipped(
            'SkillRegistry resolves its input to .agents/skills.json, which .gitignore:49 excludes, '
            .'so this suite can only run in a tree where some tool wrote that directory. A fresh clone '
            .'or worktree has no registry and these tests would fail for a reason unrelated to the '
            .'change under test. The general form of this trap: a test whose input lives in a gitignored '
            .'path passes in the developer tree and fails in every fresh clone. Detect it with '
            .'`git ls-files --error-unmatch .agents/skills.json` -- an untracked input makes the test '
            .'prove nothing anywhere. See KI-24b.'
        );
    }
});

it('discovers skills from the registry', function () {
    $registry = new SkillRegistry;

    expect($registry->count())->toBeGreaterThan(0)
        ->and($registry->countEnabled())->toBeLessThanOrEqual($registry->count());
});

it('matches skills to task descriptions by relevance', function () {
    $registry = new SkillRegistry;
    $matcher = new SkillMatcher($registry);

    $matches = $matcher->find('create a migration for users table');

    expect($matches)->not->toBeEmpty()
        ->and($matches[0]['skill']['id'])->toBe('migration_create')
        ->and($matches[0]['relevance'])->toBeGreaterThanOrEqual(0.6);
});

it('ranks the most relevant skill first', function () {
    $registry = new SkillRegistry;
    $matcher = new SkillMatcher($registry);

    $matches = $matcher->find('run the full test suite');

    expect($matches[0]['skill']['id'])->toBe('test_run');
});

it('builds an execution plan in dependency order', function () {
    $registry = new SkillRegistry;
    $matcher = new SkillMatcher($registry);

    $matches = $matcher->find('create a migration for users table');
    $plan = $matcher->plan($matches);

    expect($plan)->toContain('migration_create');
});

it('executes a skill without parameters', function () {
    $registry = new SkillRegistry;
    $executor = new SkillExecutor($registry);

    $result = $executor->execute('route_list');

    expect($result['status'])->toBe('success')
        ->and($result['error'])->toBeNull();
});

it('returns an error for an unknown skill', function () {
    $registry = new SkillRegistry;
    $executor = new SkillExecutor($registry);

    $result = $executor->execute('does_not_exist');

    expect($result['status'])->toBe('error')
        ->and($result['error'])->toContain('not found');
});

it('auto-executes skills for a task and discloses matches', function () {
    $registry = new SkillRegistry;
    $executor = new SkillExecutor($registry);

    $result = $executor->autoExecute('show me the application routes');

    expect($result['disclosure'])->toContain('Route Inspector')
        ->and($result['matches'])->not->toBeEmpty()
        ->and($result['results'])->not->toBeEmpty()
        ->and($result['results'][0]['status'])->toBe('success');
});
