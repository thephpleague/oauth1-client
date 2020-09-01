<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

interface Signer
{
    /**
     * Returns the OAuth signature method.
     */
    public function getMethod(): string;

    /**
     * Returns a signature for hte given request.
     */
    public function sign(RequestInterface $request, array $oauthParameters, Credentials $contextCredentials = null): string;
}
