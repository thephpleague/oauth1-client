<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;

class RsaSha1Signature extends Signature
{
    use NeedsBaseStringFromRsaSha1;

    /**
     * {@inheritdoc}
     */
    public function method(): string
    {
        return 'RSA-SHA1';
    }

    /**
     * {@inheritdoc}
     */
    public function sign($uri, array $parameters = [], $method = 'POST'): string
    {
        $url = $this->createUrl($uri);
        $baseString = $this->baseString($url, $method, $parameters);

        $privateKey = $this->clientCredentials->getRsaPrivateKey();

        openssl_sign($baseString, $signature, $privateKey);

        return base64_encode($signature);
    }

    /**
     * Create a Guzzle url for the given URI.
     *
     * @param string $uri
     *
     * @return \Psr\Http\Message\UriInterface
     */
    protected function createUrl($uri): UriInterface
    {
        return Psr7\uri_for($uri);
    }
}
