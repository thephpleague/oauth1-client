<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use LogicException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

abstract class BaseProvider implements Provider
{
    protected const TEMPORARY_CREDENTIALS_URI = null;
    protected const AUTHORIZATION_URI         = null;
    protected const TOKEN_CREDENTIALS_URI     = null;
    protected const USER_DETAILS_URI          = null;

    public function createTemporaryCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface {
        if (false === filter_var(self::TEMPORARY_CREDENTIALS_URI, FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid temporary credentials URI');
        }

        return $requestFactory->createRequest('POST', self::TEMPORARY_CREDENTIALS_URI);
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
        if (false === filter_var(self::AUTHORIZATION_URI, FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid authorization URI');
        }

        return $requestFactory->createRequest('GET', self::AUTHORIZATION_URI);
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
        if (false === filter_var(self::TOKEN_CREDENTIALS_URI, FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid token credentials URI');
        }

        return $requestFactory->createRequest('POST', self::TOKEN_CREDENTIALS_URI);
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
        if (false === filter_var(self::USER_DETAILS_URI, FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid user details URI');
        }

        return $requestFactory->createRequest('GET', self::USER_DETAILS_URI);
    }

    public function prepareAuthenticatedRequest(
        RequestInterface $request,
        Credentials $tokenCredentials
    ): RequestInterface {
        // TODO: Implement prepareAuthenticatedRequest() method.

        return $request;
    }
}