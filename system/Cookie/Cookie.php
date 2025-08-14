<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie;

use ArrayAccess;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\I18n\Time;
use Config\Cookie as CookieConfig;
use DateTimeInterface;
use ReturnTypeWillChange;

/**
 * A `Cookie` class represents an immutable HTTP cookie value object.
 *
 * @template-implements ArrayAccess<string, bool|int|string>
 */
class Cookie implements ArrayAccess, CloneableCookieInterface
{
    /** @var string */
    protected $prefix = '';

    /** @var string */
    protected $name;

    /** @var string */
    protected $value;

    /** @var int Unix timestamp */
    protected $expires;

    /** @var string */
    protected $path = '/';

    /** @var string */
    protected $domain = '';

    /** @var bool */
    protected $secure = false;

    /** @var bool */
    protected $httponly = true;

    /** @var string */
    protected $samesite = self::SAMESITE_LAX;

    /** @var bool */
    protected $raw = false;

    /**
     * Default attributes (lowercase keys).
     *
     * @var array{
     *  prefix: string, expires: int, path: string, domain: string,
     *  secure: bool, httponly: bool, samesite: string, raw: bool
     * }
     */
    private static array $defaults = [
        'prefix'   => '',
        'expires'  => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => self::SAMESITE_LAX,
        'raw'      => false,
    ];

    /** @var string */
    private static string $reservedCharsList = "=,; \t\r\n\v\f()<>@:\\\"/[]?{}";

    /**
     * Set defaults from config or array, tolerant of missing/older props.
     *
     * @param array{prefix?:string,expires?:int,path?:string,domain?:string,secure?:bool,httponly?:bool,samesite?:string,raw?:bool}|CookieConfig $config
     * @return array{prefix:string,expires:int,path:string,domain:string,secure:bool,httponly:bool,samesite:string,raw:bool}
     */
    public static function setDefaults($config = [])
    {
        $oldDefaults = self::$defaults;
        $newDefaults = [];

        if ($config instanceof CookieConfig) {
            $newDefaults = [
                'prefix'   => isset($config->prefix)   && is_string($config->prefix)   ? $config->prefix   : $oldDefaults['prefix'],
                'expires'  => isset($config->expires)  ? (int) $config->expires        : $oldDefaults['expires'],
                'path'     => isset($config->path)     && is_string($config->path)     ? $config->path     : $oldDefaults['path'],
                'domain'   => isset($config->domain)   && is_string($config->domain)   ? $config->domain   : $oldDefaults['domain'],
                'secure'   => isset($config->secure)   ? (bool) $config->secure        : $oldDefaults['secure'],
                'httponly' => isset($config->httponly) ? (bool) $config->httponly      : $oldDefaults['httponly'],
                'samesite' => isset($config->samesite) && is_string($config->samesite) ? $config->samesite : $oldDefaults['samesite'],
                'raw'      => isset($config->raw)      ? (bool) $config->raw           : $oldDefaults['raw'],
            ];
        } elseif (is_array($config)) {
            $newDefaults = $config;
        }

        self::$defaults = $newDefaults + $oldDefaults;

        return $oldDefaults;
    }

    // =========================================================================
    // CONSTRUCTORS
    // =========================================================================

    /**
     * @return static
     * @throws CookieException
     */
    public static function fromHeaderString(string $cookie, bool $raw = false)
    {
        $data        = self::$defaults;
        $data['raw'] = $raw;

        $parts = preg_split('/\;[\s]*/', $cookie);
        $part  = explode('=', array_shift($parts), 2);

        $name  = $raw ? $part[0] : urldecode($part[0]);
        $value = isset($part[1]) ? ($raw ? $part[1] : urldecode($part[1])) : '';
        unset($part);

        foreach ($parts as $part) {
            if (str_contains($part, '=')) {
                [$attr, $val] = explode('=', $part);
            } else {
                $attr = $part;
                $val  = true;
            }
            $data[strtolower($attr)] = $val;
        }

        return new static($name, $value, $data);
    }

    /**
     * @param array{prefix?: string, max-age?: int|numeric-string, expires?: DateTimeInterface|int|string, path?: string, domain?: string, secure?: bool, httponly?: bool, samesite?: string, raw?: bool} $options
     * @throws CookieException
     */
    final public function __construct(string $name, string $value = '', array $options = [])
    {
        $options += self::$defaults;

        $options['expires'] = static::convertExpiresTimestamp($options['expires']);

        if (isset($options['max-age']) && is_numeric($options['max-age'])) {
            $options['expires'] = Time::now()->getTimestamp() + (int) $options['max-age'];
            unset($options['max-age']);
        }

        // backward-compat with array-based cookies in previous CI versions
        $prefix = ($options['prefix'] === '') ? self::$defaults['prefix'] : $options['prefix'];
        $path   = $options['path']   ?: self::$defaults['path'];
        $domain = $options['domain'] ?: self::$defaults['domain'];

        // ---- HARDENING: coerce non-string prefix to empty string ----
        if (!is_string($prefix)) {
            $prefix = '';
        }
        // -------------------------------------------------------------

        $samesite = $options['samesite'] ?: self::$defaults['samesite'];

        $raw      = $options['raw'];
        $secure   = $options['secure'];
        $httponly = $options['httponly'];

        $this->validateName($name, $raw);
        $this->validatePrefix($prefix, $secure, $path, $domain);
        $this->validateSameSite($samesite, $secure);

        $this->prefix   = $prefix;
        $this->name     = $name;
        $this->value    = $value;
        $this->expires  = static::convertExpiresTimestamp($options['expires']);
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httponly = $httponly;
        $this->samesite = ucfirst(strtolower($samesite));
        $this->raw      = $raw;
    }

    // =========================================================================
    // GETTERS
    // =========================================================================

    public function getId(): string
    {
        return implode(';', [$this->getPrefixedName(), $this->getPath(), $this->getDomain()]);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrefixedName(): string
    {
        $name = $this->getPrefix();

        if ($this->isRaw()) {
            $name .= $this->getName();
        } else {
            $search  = str_split(self::$reservedCharsList);
            $replace = array_map(rawurlencode(...), $search);
            $name   .= str_replace($search, $replace, $this->getName());
        }

        return $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpiresTimestamp(): int
    {
        return $this->expires;
    }

    public function getExpiresString(): string
    {
        return gmdate(self::EXPIRES_FORMAT, $this->expires);
    }

    public function isExpired(): bool
    {
        return $this->expires === 0 || $this->expires < Time::now()->getTimestamp();
    }

    public function getMaxAge(): int
    {
        $maxAge = $this->expires - Time::now()->getTimestamp();
        return $maxAge >= 0 ? $maxAge : 0;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function isHTTPOnly(): bool
    {
        return $this->httponly;
    }

    public function getSameSite(): string
    {
        return $this->samesite;
    }

    public function isRaw(): bool
    {
        return $this->raw;
    }

    public function getOptions(): array
    {
        return [
            'expires'  => $this->expires,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite ?: ucfirst(self::SAMESITE_LAX),
        ];
    }

    // =========================================================================
    // CLONING
    // =========================================================================

    public function withPrefix(string $prefix = '')
    {
        $this->validatePrefix($prefix, $this->secure, $this->path, $this->domain);
        $cookie = clone $this;
        $cookie->prefix = $prefix;
        return $cookie;
    }

    public function withName(string $name)
    {
        $this->validateName($name, $this->raw);
        $cookie = clone $this;
        $cookie->name = $name;
        return $cookie;
    }

    public function withValue(string $value)
    {
        $cookie = clone $this;
        $cookie->value = $value;
        return $cookie;
    }

    public function withExpires($expires)
    {
        $cookie = clone $this;
        $cookie->expires = static::convertExpiresTimestamp($expires);
        return $cookie;
    }

    public function withExpired()
    {
        $cookie = clone $this;
        $cookie->expires = 0;
        return $cookie;
    }

    public function withPath(?string $path)
    {
        $path = $path !== null && $path !== '' && $path !== '0' ? $path : self::$defaults['path'];
        $this->validatePrefix($this->prefix, $this->secure, $path, $this->domain);
        $cookie = clone $this;
        $cookie->path = $path;
        return $cookie;
    }

    public function withDomain(?string $domain)
    {
        $domain ??= self::$defaults['domain'];
        $this->validatePrefix($this->prefix, $this->secure, $this->path, $domain);
        $cookie = clone $this;
        $cookie->domain = $domain;
        return $cookie;
    }

    public function withSecure(bool $secure = true)
    {
        $this->validatePrefix($this->prefix, $secure, $this->path, $this->domain);
        $this->validateSameSite($this->samesite, $secure);
        $cookie = clone $this;
        $cookie->secure = $secure;
        return $cookie;
    }

    public function withHTTPOnly(bool $httponly = true)
    {
        $cookie = clone $this;
        $cookie->httponly = $httponly;
        return $cookie;
    }

    public function withSameSite(string $samesite)
    {
        $this->validateSameSite($samesite, $this->secure);
        $cookie = clone $this;
        $cookie->samesite = ucfirst(strtolower($samesite));
        return $cookie;
    }

    public function withRaw(bool $raw = true)
    {
        $this->validateName($this->name, $raw);
        $cookie = clone $this;
        $cookie->raw = $raw;
        return $cookie;
    }

    // =========================================================================
    // ARRAY ACCESS FOR BC
    // =========================================================================

    public function offsetExists($offset): bool
    {
        return $offset === 'expire' ? true : property_exists($this, $offset);
    }

    /** @return bool|int|string */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new InvalidArgumentException(sprintf('Undefined offset "%s".', $offset));
        }
        return $offset === 'expire' ? $this->expires : $this->{$offset};
    }

    public function offsetSet($offset, $value): void
    {
        throw new LogicException(sprintf('Cannot set values of properties of %s as it is immutable.', static::class));
    }

    public function offsetUnset($offset): void
    {
        throw new LogicException(sprintf('Cannot unset values of properties of %s as it is immutable.', static::class));
    }

    // =========================================================================
    // CONVERTERS
    // =========================================================================

    public function toHeaderString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        $cookieHeader = [];

        if ($this->getValue() === '') {
            $cookieHeader[] = $this->getPrefixedName() . '=deleted';
            $cookieHeader[] = 'Expires=' . gmdate(self::EXPIRES_FORMAT, 0);
            $cookieHeader[] = 'Max-Age=0';
        } else {
            $value = $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());
            $cookieHeader[] = sprintf('%s=%s', $this->getPrefixedName(), $value);

            if ($this->getExpiresTimestamp() !== 0) {
                $cookieHeader[] = 'Expires=' . $this->getExpiresString();
                $cookieHeader[] = 'Max-Age=' . $this->getMaxAge();
            }
        }

        if ($this->getPath() !== '')   { $cookieHeader[] = 'Path=' . $this->getPath(); }
        if ($this->getDomain() !== '') { $cookieHeader[] = 'Domain=' . $this->getDomain(); }
        if ($this->isSecure())         { $cookieHeader[] = 'Secure'; }
        if ($this->isHTTPOnly())       { $cookieHeader[] = 'HttpOnly'; }

        $samesite = $this->getSameSite();
        if ($samesite === '') { $samesite = self::SAMESITE_LAX; }
        $cookieHeader[] = 'SameSite=' . ucfirst(strtolower($samesite));

        return implode('; ', $cookieHeader);
    }

    public function toArray(): array
    {
        return [
            'name'   => $this->name,
            'value'  => $this->value,
            'prefix' => $this->prefix,
            'raw'    => $this->raw,
        ] + $this->getOptions();
    }

    /**
     * @param DateTimeInterface|int|string $expires
     */
    protected static function convertExpiresTimestamp($expires = 0): int
    {
        if ($expires instanceof DateTimeInterface) {
            $expires = $expires->format('U');
        }

        if (!is_string($expires) && !is_int($expires)) {
            throw CookieException::forInvalidExpiresTime(gettype($expires));
        }

        if (!is_numeric($expires)) {
            $expires = strtotime($expires);
            if ($expires === false) {
                throw CookieException::forInvalidExpiresValue();
            }
        }

        return $expires > 0 ? (int) $expires : 0;
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    protected function validateName(string $name, bool $raw): void
    {
        if ($raw && strpbrk($name, self::$reservedCharsList) !== false) {
            throw CookieException::forInvalidCookieName($name);
        }
        if ($name === '') {
            throw CookieException::forEmptyCookieName();
        }
    }

    protected function validatePrefix(string $prefix, bool $secure, string $path, string $domain): void
    {
        if (str_starts_with($prefix, '__Secure-') && !$secure) {
            throw CookieException::forInvalidSecurePrefix();
        }
        if (str_starts_with($prefix, '__Host-') && (!$secure || $domain !== '' || $path !== '/')) {
            throw CookieException::forInvalidHostPrefix();
        }
    }

    protected function validateSameSite(string $samesite, bool $secure): void
    {
        if ($samesite === '') {
            $samesite = self::$defaults['samesite'];
        }
        if ($samesite === '') {
            $samesite = self::SAMESITE_LAX;
        }
        if (!in_array(strtolower($samesite), self::ALLOWED_SAMESITE_VALUES, true)) {
            throw CookieException::forInvalidSameSite($samesite);
        }
        if (strtolower($samesite) === self::SAMESITE_NONE && !$secure) {
            throw CookieException::forInvalidSameSiteNone();
        }
    }
}
