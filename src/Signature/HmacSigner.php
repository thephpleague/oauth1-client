<?php

namespace League\OAuth1\Client\Signature;

use Psr\Http\Message\RequestInterface;

class HmacSigner extends BaseSigner
{
    private const METHOD = 'HMAC-SHA1';

    public function getMethod(): string
    {
        return self::METHOD;
    }

    public function sign(RequestInterface $request, array $oauthParameters): string
    {
        $baseString = $this->baseStringBuilder->build($request, $oauthParameters);

        $key = sprintf(
            '%s&%s',
            rawurlencode($this->clientCredentials->getSecret()),
            rawurlencode($this->contextCredentials->getSecret())
        );

        return base64_encode(hash_hmac('sha1', $baseString, $key, true));
    }
}
