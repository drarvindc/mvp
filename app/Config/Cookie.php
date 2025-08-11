<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cookie extends BaseConfig
{
    /** Prefix to prepend to all cookie names. MUST be a string (empty string is fine). */
    public string $prefix = '';

    /** Cookie domain (keep empty to default to current host). */
    public string $domain = '';

    /** Cookie path. */
    public string $path = '/';

    /** Secure (true because you’re on HTTPS). */
    public bool $secure = true;

    /** HttpOnly flag. */
    public bool $httponly = true;

    /** SameSite policy: 'Lax' is good for auth. */
    public string $sameSite = 'Lax';

    /**
     * Default lifetime (in seconds) for “remember me” cookies.
     * 0 means “session cookie” (expires when browser closes).
     * Your login code should set an explicit expiry when Remember Me is checked.
     */
    public int $expires = 0;
}
