<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

class PlainTextSigner implements Signer
{
    private const METHOD = 'PLAINTEXT';

    /** @var ClientCredentials */
    protected $clientCredentials;

    public function __construct(ClientCredentials $clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;
    }

    public function getMethod(): string
    {
        return self::METHOD;
    }

    public function sign(RequestInterface $request, array $oauthParameters, Credentials $contextCredentials = null): string
    {
        return sprintf(
            '%s&%s',
            rawurlencode($this->clientCredentials->getSecret()),
            $contextCredentials ? rawurlencode($contextCredentials->getSecret()) : ''
        );
    }
}
