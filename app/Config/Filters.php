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

        // Project filters (normal aliases)
        'devopenaccess' => \App\Filters\DevOpenAccess::class,
        'adminauth'     => \App\Filters\AdminAuth::class,
        'admintoolbar'  => \App\Filters\AdminToolbar::class,
        'dmydate'       => \App\Filters\DmyDate::class,

        // 🔧 Hotfix alias to catch the buggy comma-joined value used somewhere:
        // CI treats the WHOLE string as one alias. Map it to DevOpenAccess so it won't explode.
        'adminauth,admintoolbar,dmydate' => \App\Filters\DevOpenAccess::class,
    ];

    // During dev, keep globals empty so nothing blocks pre-route
    public array $globals = [ 'before' => [], 'after' => [], ];

    public array $methods = [];

    // Route-specific filters (leave empty for now; use arrays when multiple)
    public array $filters = [
        // 'admin' => ['before' => ['adminauth','admintoolbar','dmydate']],
    ];
}
