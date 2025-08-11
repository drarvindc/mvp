<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cookie extends BaseConfig
{
    /** String prefix for all cookie names (must be a string, empty is fine) */
    public $prefix   = '';

    /** Cookie domain (empty = current host) */
    public $domain   = '';

    /** Cookie path */
    public $path     = '/';

    /** Only send cookies over HTTPS */
    public $secure   = true;

    /** Prevent JS access */
    public $httponly = true;

    /**
     * SameSite policy (NOTE: lower-case property name is required by your CI core)
     * Allowed: 'Lax', 'Strict', 'None'
     */
    public $samesite = 'Lax';

    /**
     * Default cookie lifetime in seconds.
     * 0 = session cookie (expires when browser closes).
     * Your “Remember me” logic will set an explicit expiry.
     */
    public $expires  = 0;
}
