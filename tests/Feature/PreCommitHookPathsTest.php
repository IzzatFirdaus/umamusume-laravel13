<?php

declare(strict_types=1);

use Tests\TestCase;

it('pre-commit pathspec script exists', function () {
    $pathspec = base_path('tools/pre-commit-pathspec');

    expect($pathspec)->toBeFile();
});

it('pre-commit pathspec script is executable on POSIX or readable on Windows', function () {
    $pathspec = base_path('tools/pre-commit-pathspec');

    // On Windows, check readability instead of executable bit
    if (DIRECTORY_SEPARATOR === '\\') {
        expect(is_readable($pathspec))->toBeTrue();
    } else {
        expect(fileperms($pathspec) & 0111)->toBeTruthy();
    }
});

it('pre-commit hook delegates to the pathspec script', function () {
    // In a worktree, hooks are in the common dir (main repo's .git/hooks)
    $gitFile = base_path('.git');
    $gitDirContent = rtrim(file_get_contents($gitFile), "\n");
    
    if (str_starts_with($gitDirContent, 'gitdir: ')) {
        $worktreeGitDir = substr($gitDirContent, 8);
        $commondirFile = $worktreeGitDir . '/commondir';
        if (file_exists($commondirFile)) {
            $commondir = rtrim(file_get_contents($commondirFile), "\n");
            // Resolve relative path from worktree git dir
            $mainGitDir = realpath($worktreeGitDir . '/' . $commondir);
            $hook = $mainGitDir . '/hooks/pre-commit';
        } else {
            $hook = $worktreeGitDir . '/hooks/pre-commit';
        }
    } else {
        $hook = base_path('.git/hooks/pre-commit');
    }

    expect($hook)->toBeFile();

    $content = file_get_contents($hook);
    expect($content)->toContain('pre-commit-pathspec');
});