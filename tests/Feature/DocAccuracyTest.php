<?php

declare(strict_types=1);

it('pins every file path named in AGENTS.md §6 repository structure block', function (): void {
    $agents = file_get_contents(base_path('AGENTS.md'));
    // The fence is bare (```), not ```text, so the language-tagged pattern matched nothing and the
    // loop asserted zero times: a gate that passes because it read nothing is the failure mode the
    // LineEndingGuard floor exists to prevent, so the same floor is asserted here.
    preg_match('/## 6\. Repository structure\s*?\r?\n```(?:text)?\r?\n(.*?)```\r?\n/s', $agents, $block);
    $structure = $block[1] ?? '';
    $checked = 0;

    foreach (explode("\n", $structure) as $line) {
        if (preg_match('/^([A-Za-z0-9][A-Za-z0-9_.\/\\-]+?)\s{2,}/', $line, $match)) {
            $path = base_path(rtrim($match[1], '/'));
            expect(is_dir($path) || is_file($path))->toBeTrue("AGENTS.md §6 names a path that does not exist: {$match[1]}");
            $checked++;
        }
    }

    // The floor, not a budget: a structure block that yielded no paths means the parse broke,
    // not that the tree is tidy.
    expect($checked)->toBeGreaterThan(10, 'the AGENTS.md §6 path sweep matched no rows, so it proves nothing');
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
