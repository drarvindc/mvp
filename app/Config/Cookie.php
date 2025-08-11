<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cookie extends BaseConfig
{
    // MUST be a string (empty string is fine)
    public $prefix   = '';

    // Domain and path
    public $domain   = '';
    public $path     = '/';

    // Security flags
    public $secure   = true;   // HTTPS only
    public $httponly = true;   // not accessible to JS

    // SameSite policy (string) — your CI core expects lower-case property "samesite"
    public $samesite = 'Lax';  // 'Lax' | 'Strict' | 'None'

    // Whether to send cookies without URL-encoding their values
    public $raw      = false;

    // Default lifetime in seconds (0 = session cookie)
    public $expires  = 0;
}
