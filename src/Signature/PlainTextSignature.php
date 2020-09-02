<?php

namespace League\OAuth1\Client\Signature;

class PlainTextSignature extends Signature implements SignatureInterface
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
    public function sign(string $uri, array $parameters = [], string $method = 'POST'): string
    {
        return $this->key();
    }
}
