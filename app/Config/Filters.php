<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
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

    // Fastest unblock: disable global before/after filters in dev
    public array $globals = [
        'before' => [
            // leave empty during DEV_NO_AUTH to avoid pre-route blocks
        ],
        'after' => [
            // 'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [
        // keep route-specific filters only
    ];
}
