<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'adminauth'=> \App\Filters\AdminAuth::class,
		'apiauth' => \App\Filters\ApiAuthFilter::class,
		'stableapiauth' => \App\Filters\StableApiAuthFilter::class,
		'adminauth' => \App\Filters\AdminAuth::class,
		'devopenaccess' => \App\Filters\DevOpenAccess::class,



    
        'admintoolbar' => \App\Filters\AdminToolbar::class,
        'dmydate' => \App\Filters\DmyDateFilter::class,];

    public $globals = [
        'before' => [
            // 'csrf',
        ],
        'after'  => [
            'toolbar',
        ],
    ];

    public $methods = [];
    public $filters = [];
}

