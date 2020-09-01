<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

interface Signer
{
    public static function withClientCredentials(ClientCredentials $clientCredentials): Signer;

    public static function withTemporaryCredentials(
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials
    ): Signer;

    public static function withTokenCredentials(
        ClientCredentials $clientCredentials,
        Credentials $tokenCredentials
    ): Signer;

    /**
     * Returns the OAuth signature method.
     */
    public function getMethod(): string;

    /**
     * Returns a signature for hte given request.
     */
    public function sign(RequestInterface $request, array $oauthParameters): string;
}
