<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

class HmacSigner extends BaseSigner
{
    private const METHOD = 'HMAC-SHA1';

    public function getMethod(): string
    {
        return self::METHOD;
    }

    /**
     * @param array<string, string|int> $oauthParameters
     *
     * @link https://tools.ietf.org/html/rfc5849#section-3.4.2 HMAC-SHA1
     */
    public function sign(RequestInterface $request, array $oauthParameters, Credentials $contextCredentials = null): string
    {
        $baseString = $this->baseStringBuilder->forRequest($request, $oauthParameters);

        $key = sprintf(
            '%s&%s',
            rawurlencode($this->clientCredentials->getSecret()),
            $contextCredentials ? rawurlencode($contextCredentials->getSecret()) : ''
        );

        return base64_encode(hash_hmac('sha1', $baseString, $key, true));
    }
}
