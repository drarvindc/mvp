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

        'devopenaccess' => \App\Filters\DevOpenAccess::class,
        'adminauth'     => \App\Filters\AdminAuth::class,
        'admintoolbar'  => \App\Filters\AdminToolbar::class,
        'dmydate'       => \App\Filters\DmyDate::class,
        'stableapiauth' => \App\Filters\StableApiAuth::class,

        'adminauth,admintoolbar,dmydate' => \App\Filters\DevOpenAccess::class,
    ];

    public array $globals = [
        'before' => [ ],
        'after'  => [ ],
    ];

    public array $methods = [ ];
    public array $filters = [ ];
}
