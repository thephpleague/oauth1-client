<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\User;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Provider
{
    /**
     * Creates a request with the given factory for fetching temporary credentials from the provider.
     */
    public function createTemporaryCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface;

    /**
     * Prepares the given request for fetching temporary credentials from the provider.
     */
    public function prepareTemporaryCredentialsRequest(RequestInterface $request): RequestInterface;

    /**
     * Creates a request with the given factory for redirecting the user to the provider for authorization.
     */
    public function createAuthorizationRequest(RequestFactoryInterface $requestFactory): RequestInterface;

    /**
     * Prepares the given request for redirecting the user to the provider for authorization.
     */
    public function prepareAuthorizationRequest(
        RequestInterface $request,
        Credentials $temporaryCredentials
    ): RequestInterface;

    /**
     * Creates a request with the given factory for fetching token credentials from the provider.
     */
    public function createTokenCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface;

    /**
     * Prepares the given request for fetching token credentials from the provider.
     */
    public function prepareTokenCredentialsRequest(
        RequestInterface $request,
        Credentials $temporaryCredentials,
        string $verifier
    ): RequestInterface;

    /**
     * Creates a request with the given factory for user details from the provider.
     */
    public function createUserDetailsRequest(RequestFactoryInterface $requestFactory): RequestInterface;

    /**
     * Extracts user details from the given response.
     */
    public function extractUserDetails(ResponseInterface $response): User;

    /**
     * Prepares the given request for performing an authenticated request on the provider.
     */
    public function prepareAuthenticatedRequest(
        RequestInterface $request,
        Credentials $tokenCredentials
    ): RequestInterface;
}
