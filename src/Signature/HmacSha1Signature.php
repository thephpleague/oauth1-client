<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;

class HmacSha1Signature extends Signature
{
    use EncodesQuery;

    /**
     * {@inheritDoc}
     */
    public function method(): string
    {
        return 'HMAC-SHA1';
    }

    /**
     * {@inheritDoc}
     */
    public function sign(string $uri, array $parameters = [], string $method = 'POST'): string
    {
        $url = $this->createUri($uri);

        $baseString = $this->baseString($url, $method, $parameters);

        return base64_encode($this->hash($baseString));
    }

    /**
     * Create a URI object for the given string URI.
     */
    protected function createUri(string $uri): UriInterface
    {
        return Psr7\uri_for($uri);
    }

    /**
     * Hashes a string with the signature's key.
     */
    protected function hash(string $string): string
    {
        return hash_hmac('sha1', $string, $this->key(), true);
    }
}
