<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;

class HmacSha1Signature extends Signature
{
    use NeedsBaseStringForSha1;

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
    public function sign($uri, array $parameters = [], $method = 'POST'): string
    {
        $url = $this->createUrl($uri);

        $baseString = $this->baseString($url, $method, $parameters);

        return base64_encode($this->hash($baseString));
    }

    /**
     * Create a Guzzle url for the given URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     */
    protected function createUrl(string $uri): UriInterface
    {
        return Psr7\uri_for($uri);
    }

    /**
     * Hashes a string with the signature's key.
     *
     * @param string $string
     *
     * @return string
     */
    protected function hash(string $string): string
    {
        return hash_hmac('sha1', $string, $this->key(), true);
    }
}
