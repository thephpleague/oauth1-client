<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;

class PlainTextSignature extends Signature implements SignatureInterface
{
    /**
     * {@inheritDoc}
     */
    public function method()
    {
        return 'PLAINTEXT';
    }

    /**
     * {@inheritDoc}
     */
    public function sign($uri, array $parameters = array(), $method = 'POST')
    {
        return $this->key();
    }
}
