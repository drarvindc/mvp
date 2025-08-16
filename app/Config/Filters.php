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

        // Project filters (aliases must be single names, not comma-joined)
        'devopenaccess' => \App\Filters\DevOpenAccess::class,
        'adminauth'     => \App\Filters\AdminAuth::class,
        'admintoolbar'  => \App\Filters\AdminToolbar::class,
        'dmydate'       => \App\Filters\DmyDate::class,
    ];

    // Disable global filters during dev to avoid pre-route blocks
    public array $globals = [
        'before' => [
            // Example if you want them later:
            // 'adminauth',
            // 'admintoolbar',
            // 'dmydate',
        ],
        'after' => [
            // 'toolbar',
        ],
    ];

    public array $methods = [];

    // Route-specific filters go here, always as arrays if multiple
    public array $filters = [
        // 'admin' => ['before' => ['adminauth','admintoolbar','dmydate']],
    ];
}
