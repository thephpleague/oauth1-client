<?php

namespace League\OAuth1\Client\Signature;

class PlainTextSignature extends Signature
{
    /**
     * {@inheritDoc}
     */
    public function method(): string
    {
        return 'PLAINTEXT';
    }

    /**
     * {@inheritDoc}
     */
    public function sign($uri, array $parameters = [], $method = 'POST'): string
    {
        return $this->key();
    }
}
