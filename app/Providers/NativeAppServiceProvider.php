<?php

declare(strict_types=1);

namespace App\Providers;

use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

/**
 * NativePHP's desktop bootstrap provider.
 *
 * `config('nativephp.provider')` names this class, and NativePHP resolves it on
 * every `native:*` command (`LoadPHPConfigurationCommand`). Without it the
 * packaged app aborts with "Target class [...] does not exist" before the PHP
 * server binds a port.
 *
 * Deliberately not registered in `bootstrap/providers.php`: NativePHP resolves it
 * on demand, and booting it on every web request would open a desktop window.
 */
class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Open the desktop window once the native application has booted.
     */
    public function boot(): void
    {
        Window::open();
    }

    /**
     * @return array<string, string>
     */
    public function phpIni(): array
    {
        return [];
    }
}
