<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;

class HmacSha1Signature extends Signature implements SignatureInterface
{
    use EncodesQuery;

    /**
     * {@inheritDoc}
     */
    public function method()
    {
        return 'HMAC-SHA1';
    }

    /**
     * {@inheritDoc}
     */
    public function sign($uri, array $parameters = [], $method = 'POST')
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
     * @return Url
     */
    protected function createUrl($uri)
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
    protected function hash($string)
    {
        return hash_hmac('sha1', $string, $this->key(), true);
    }
}
