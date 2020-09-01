<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Request\OAuthParametersBuilder;
use League\OAuth1\Client\Request\OAuthParametersInjector;
use League\OAuth1\Client\Signature\HmacSigner;
use League\OAuth1\Client\Signature\Signer;
use LogicException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

abstract class BaseProvider implements Provider
{
    public function createTemporaryCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getTemporaryCredentialsUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid temporary credentials URI');
        }

        return $requestFactory->createRequest('POST', $uri);
    }

    public function prepareTemporaryCredentialsRequest(
        RequestInterface $request,
        ClientCredentials $clientCredentials
    ): RequestInterface {
        $signer = $this->getSigner('withClientCredentials', $clientCredentials);

        $oauthParameters = (new OAuthParametersBuilder($signer))
            ->forTemporaryCredentialsRequest($clientCredentials);

        $signature = $signer->sign($request, $oauthParameters);

        return (new OAuthParametersInjector())->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getTemporaryCredentialsParametersLocation()
        );
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
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials
    ): RequestInterface {
        $signer = $this->getSigner('withTemporaryCredentials', $clientCredentials, $temporaryCredentials);

        $oauthParameters = (new OAuthParametersBuilder($signer))
            ->forAuthorizationRequest($clientCredentials);

        $signature = $signer->sign($request, $oauthParameters);

        return (new OAuthParametersInjector())->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getAuthorizationParametersLocation()
        );
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
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials,
        string $verifier
    ): RequestInterface {
        $signer = $this->getSigner('withTemporaryCredentials', $clientCredentials, $temporaryCredentials);

        $oauthParameters = (new OAuthParametersBuilder($signer))
            ->forTokenCredentialsRequest(
                $clientCredentials,
                $temporaryCredentials,
                $verifier
            );

        $signature = $signer->sign($request, $oauthParameters);

        return (new OAuthParametersInjector())->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getTokenCredentialsParametersLocation()
        );
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
        ClientCredentials $clientCredentials,
        Credentials $tokenCredentials
    ): RequestInterface {
        $signer = $this->getSigner('withTokenCredentials', $clientCredentials, $tokenCredentials);

        $oauthParameters = (new OAuthParametersBuilder($signer))
            ->forAuthenticatedRequest($clientCredentials, $tokenCredentials);

        $signature = $signer->sign($request, $oauthParameters);

        return (new OAuthParametersInjector())->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getAuthenticatedParametersLocation()
        );
    }

    /**
     * Gets the signature class to use.
     */
    protected function getSignatureClass(): string
    {
        return HmacSigner::class;
    }

    protected function getTemporaryCredentialsParametersLocation(): string
    {
        return OAuthParametersInjector::LOCATION_QUERY;
    }

    protected function getAuthorizationParametersLocation(): string
    {
        return OAuthParametersInjector::LOCATION_BODY;
    }

    protected function getTokenCredentialsParametersLocation(): string
    {
        return OAuthParametersInjector::LOCATION_HEADER;
    }

    protected function getAuthenticatedParametersLocation(): string
    {
        return OAuthParametersInjector::LOCATION_HEADER;
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

    private function getSigner(string $constructor, ...$arguments): Signer
    {
        return forward_static_call(
            [$this->getSignatureClass(), $constructor],
            ...$arguments
        );
    }
}