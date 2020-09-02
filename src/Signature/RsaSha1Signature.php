<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use League\OAuth1\Client\Credentials\RsaClientCredentials;
use Psr\Http\Message\UriInterface;

class RsaSha1Signature extends Signature
{
    use EncodesQuery;

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
    public function sign(string $uri, array $parameters = [], string $method = 'POST'): string
    {
        $url = $this->createUri($uri);
        $baseString = $this->baseString($url, $method, $parameters);

        /** @var RsaClientCredentials $clientCredentials */
        $clientCredentials = $this->clientCredentials;

        $privateKey = $clientCredentials->getRsaPrivateKey();

        openssl_sign($baseString, $signature, $privateKey);

        return base64_encode($signature);
    }

    /**
     * Create a URI object for the given string URI.
     */
    protected function createUri(string $uri): UriInterface
    {
        return Psr7\uri_for($uri);
    }
}
