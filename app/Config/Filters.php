<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        // CI built-ins
        'toolbar'       => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'      => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars'  => \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,

        // Project filters
        'devopenaccess' => \App\Filters\DevOpenAccess::class,
        'adminauth'     => \App\Filters\AdminAuth::class,
        'admintoolbar'  => \App\Filters\AdminToolbar::class,
        'dmydate'       => \App\Filters\DmyDate::class,
    ];

    // Fastest unblock during dev — empty globals so nothing blocks pre-route
    public array $globals = [
        'before' => [
            // keep empty in dev to avoid pre-route blocks
        ],
        'after' => [
            // 'toolbar', // enable if you want CI toolbar
        ],
    ];

    public array $methods = [];

    public array $filters = [
        // keep route-specific filters only
    ];
}
