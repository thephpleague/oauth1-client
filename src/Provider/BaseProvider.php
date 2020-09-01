<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use LogicException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

abstract class BaseProvider implements Provider
{
    public function createTemporaryCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface {
        if (false === filter_var($uri = $this->getTemporaryCredentialsUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid temporary credentials URI');
        }

        return $requestFactory->createRequest('POST', $uri);
    }

    public function prepareTemporaryCredentialsRequest(
        RequestInterface $request,
        ClientCredentials $clientCredentials
    ): RequestInterface {
        // TODO: Implement prepareTemporaryCredentialsRequest() method.

        return $request;
    }

    public function createAuthorizationRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getAuthorizationUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid authorization URI');
        }

        return $requestFactory->createRequest('GET', $uri);
    }

    public function prepareAuthorizationRequest(
        RequestInterface $request,
        Credentials $temporaryCredentials
    ): RequestInterface {
        // TODO: Implement prepareAuthorizationRequest() method.

        return $request;
    }

    public function createTokenCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getTokenCredentialsUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid token credentials URI');
        }

        return $requestFactory->createRequest('POST', $uri);
    }

    public function prepareTokenCredentialsRequest(
        RequestInterface $request,
        Credentials $temporaryCredentials,
        string $verifier
    ): RequestInterface {
        // TODO: Implement prepareTokenCredentialsRequest() method.

        return $request;
    }

    public function createUserDetailsRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getUserDetailsUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid user details URI');
        }

        return $requestFactory->createRequest('GET', $uri);
    }

    public function prepareAuthenticatedRequest(
        RequestInterface $request,
        Credentials $tokenCredentials
    ): RequestInterface {
        // TODO: Implement prepareAuthenticatedRequest() method.

        return $request;
    }

    /**
     * Get the URI for fetching temporary credentials from.
     */
    abstract protected function getTemporaryCredentialsUri(): string;

    /**
     * Get the URI for starting the authorization process at.
     */
    abstract protected function getAuthorizationUri(): string;

    /**
     * Get the URI for fetching token credentials from.
     */
    abstract protected function getTokenCredentialsUri(): string;

    /**
     * Get the URI for user details from.
     */
    abstract protected function getUserDetailsUri(): string;
}