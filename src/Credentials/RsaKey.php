<?php

namespace League\OAuth1\Client\Credentials;

use OpenSSLAsymmetricKey;

class RsaKey
{
    /** @var resource|OpenSSLAsymmetricKey */
    private $raw;

    /**
     * @param resource|OpenSSLAsymmetricKey $raw
     */
    public function __construct($raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return resource|OpenSSLAsymmetricKey
     */
    public function getRaw()
    {
        return $this->raw;
    }

    public function __destruct()
    {
        // In PHP 8, this function is deprecated and is no longer required
        if (PHP_MAJOR_VERSION < 8) {
            openssl_pkey_free($this->getRaw());
        }
    }
}
