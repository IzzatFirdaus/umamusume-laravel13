<?php

declare(strict_types=1);

use Tests\TestCase;

it('check-electron-env.php exits 0 when ELECTRON_RUN_AS_NODE is unset', function () {
    $script = base_path('tools/check-electron-env.php');
    $command = "php " . escapeshellarg($script);
    
    $output = '';
    $exitCode = 0;
    exec($command, $output, $exitCode);
    
    expect($exitCode)->toBe(0);
});

it('check-electron-env.php exits 1 when ELECTRON_RUN_AS_NODE is set', function () {
    $script = base_path('tools/check-electron-env.php');
    
    // On Windows, use putenv via a wrapper
    $wrapper = base_path('tools/check-electron-env-wrapper.php');
    file_put_contents($wrapper, '<?php putenv("ELECTRON_RUN_AS_NODE=1"); require ' . var_export($script, true) . ';');
    
    $command = "php " . escapeshellarg($wrapper) . " 2>&1";
    
    $output = '';
    $exitCode = 0;
    exec($command, $output, $exitCode);
    
    @unlink($wrapper);
    
    expect($exitCode)->toBe(1);
    expect(implode("\n", $output))->toContain('ERROR: ELECTRON_RUN_AS_NODE is set');
});

it('check-electron-env.php exits 1 when ELECTRON_RUN_AS_NODE is set to any value', function () {
    $script = base_path('tools/check-electron-env.php');
    
    $wrapper = base_path('tools/check-electron-env-wrapper.php');
    file_put_contents($wrapper, '<?php putenv("ELECTRON_RUN_AS_NODE=true"); require ' . var_export($script, true) . ';');
    
    $command = "php " . escapeshellarg($wrapper) . " 2>&1";
    
    $output = '';
    $exitCode = 0;
    exec($command, $output, $exitCode);
    
    @unlink($wrapper);
    
    expect($exitCode)->toBe(1);
});

it('pre-native-build composer script is defined', function () {
    $composer = json_decode(file_get_contents(base_path('composer.json')), true);
    
    expect($composer['scripts']['pre-native-build'])->toBeArray()
        ->and($composer['scripts']['pre-native-build'])->toContain('@php tools/check-electron-env.php');
});