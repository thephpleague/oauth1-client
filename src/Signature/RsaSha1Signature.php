<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use League\OAuth1\Client\Signature\Signature;
use League\OAuth1\Client\Signature\SignatureInterface;

class RsaSha1Signature extends Signature implements SignatureInterface
{
    use EncodesQuery;

    /**
     * {@inheritdoc}
     */
    public function method()
    {
        return 'RSA-SHA1';
    }

    /**
     * {@inheritdoc}
     */
    public function sign($uri, array $parameters = [], $method = 'POST')
    {
        $url        = $this->createUrl($uri);
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
     * @return Url
     */
    protected function createUrl($uri)
    {
        return Psr7\uri_for($uri);
    }
}
