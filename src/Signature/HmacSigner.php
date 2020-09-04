<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

class HmacSigner implements Signer
{
    private const METHOD = 'HMAC-SHA1';

    /** @var ClientCredentials */
    protected $clientCredentials;

    /** @var BaseStringBuilder */
    protected $baseStringBuilder;

    public function __construct(ClientCredentials $clientCredentials, BaseStringBuilder $baseStringBuilder = null)
    {
        $this->clientCredentials = $clientCredentials;
        $this->baseStringBuilder = $baseStringBuilder ?: new BaseStringBuilder();
    }

    public function getMethod(): string
    {
        return self::METHOD;
    }

    /**
     * @param array<string, string|int> $oauthParameters
     *
     * @see https://tools.ietf.org/html/rfc5849#section-3.4.2 HMAC-SHA1
     */
    public function sign(RequestInterface $request, array $oauthParameters, Credentials $contextCredentials = null): string
    {
        $signature = hash_hmac(
            'sha1',
            $this->baseStringBuilder->forRequest($request, $oauthParameters),
            $this->getKey($contextCredentials),
            true
        );

        return base64_encode($signature);
    }

    private function getKey(?Credentials $contextCredentials): string
    {
        return sprintf(
            '%s&%s',
            rawurlencode($this->clientCredentials->getSecret()),
            $contextCredentials ? rawurlencode($contextCredentials->getSecret()) : ''
        );
    }
}
