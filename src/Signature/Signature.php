<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

interface Signature
{
    public static function withTemporaryCredentials(
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials
    ): Signature;

    public static function withTokenCredentials(
        ClientCredentials $clientCredentials,
        Credentials $tokenCredentials
    ): Signature;

    /**
     * Returns the OAuth signature method.
     */
    public function getMethod(): string;

    /**
     * Signs the given Request.
     */
    public function sign(RequestInterface $request, string $realm = null): RequestInterface;
}
