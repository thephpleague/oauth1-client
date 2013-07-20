<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;

class PlainTextSignature extends Signature implements SignatureInterface
{
    /**
     * Get the OAuth signature method.
     *
     * @return string
     */
    public function method()
    {
        return 'PLAINTEXT';
    }

    /**
     * Sign the given request for the client.
     *
     * @param  string  $uri
     * @param  array   $credentials
     * @param  string  $method
     * @return string
     * @see    OAuth 1.0 RFC 5849 Section 3.4.4
     */
    public function sign($uri, array $parameters = array(), $method = 'POST')
    {
        return $this->key();
    }
}