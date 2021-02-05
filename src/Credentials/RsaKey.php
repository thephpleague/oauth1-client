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
}
