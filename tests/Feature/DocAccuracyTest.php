<?php

declare(strict_types=1);

it('pins every file path named in AGENTS.md §6 repository structure block', function (): void {
    $agents = file_get_contents(base_path('AGENTS.md'));
    preg_match('/## 6\. Repository structure\s*\n```text\n(.*?)```\n/s', $agents, $block);
    $structure = $block[1] ?? '';
    foreach (explode("\n", $structure) as $line) {
        if (preg_match('/^([A-Za-z0-9][A-Za-z0-9_.\/\\-]+?)\s{2,}/', $line, $match)) {
            $path = base_path(rtrim($match[1], '/'));
            expect(is_dir($path) || is_file($path))->toBeTrue();
        }
    }
});

it('pins every route name in SCREEN_SPEC.md §3 screen matrix', function (): void {
    $spec = file_get_contents(base_path('SCREEN_SPEC.md'));
    $routes = file_get_contents(base_path('routes/web.php'));
    preg_match('/## 3\. Screen Matrix\s*\n(.*?)(?=\n## |\Z)/s', $spec, $matrix);
    $section = $matrix[1] ?? '';
    preg_match_all('/\(`([a-z][a-z0-9_.]*)`\)/', $section, $names);
    foreach (array_unique($names[1]) as $name) {
        expect(str_contains($routes, "->name('{$name}')"))->toBeTrue();
    }
});

it('pins every implementation reference page named in SCREEN_SPEC.md', function (): void {
    $spec = file_get_contents(base_path('SCREEN_SPEC.md'));
    preg_match_all('/Page:\s*`([^`]+\.vue)`/', $spec, $pages);
    foreach (array_unique($pages[1]) as $page) {
        expect(is_file(base_path($page)))->toBeTrue();
    }
});
