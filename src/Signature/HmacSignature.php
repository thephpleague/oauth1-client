<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;
use Psr\Http\Message\RequestInterface;

class HmacSignature extends BaseSignature
{
    private const METHOD = 'HMAC-SHA1';

    public function getMethod(): string
    {
        return self::METHOD;
    }

    public function sign(RequestInterface $request, string $realm = null): RequestInterface
    {
        $oauthParameters = $this->generateOAuthParameters();

        $baseString = $this->baseStringBuilder->build($request, $oauthParameters);

        $key = sprintf(
            '%s&%s',
            rawurlencode($this->clientCredentials->getSecret()),
            rawurlencode($this->contextCredentials->getSecret())
        );

        $oauthParameters['oauth_signature'] = base64_encode(hash_hmac('sha1', $baseString, $key, true));

        return $request->withHeader(...$this->createAuthorizationHeader($oauthParameters, $realm));
    }
}
