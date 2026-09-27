<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'opcache.enable' => '1',
            'opcache.enable_cli' => '1',
            'opcache.jit_buffer_size' => '100M',
            'opcache.jit' => 'tracing',
            'memory_limit' => '512M',
        ];
    }
}
