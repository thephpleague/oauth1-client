<?php

namespace League\OAuth1\Client\Provider;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\ParametersBuilder;
use League\OAuth1\Client\RequestInjector;
use League\OAuth1\Client\Signature\HmacSigner;
use League\OAuth1\Client\Signature\Signer;
use LogicException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

abstract class BaseProvider implements Provider
{
    /** @var ClientCredentials */
    private $clientCredentials;

    /** @var callable */
    private $signerResolver;

    /** @var Signer */
    private $signer;

    /** @var callable */
    private $parametersBuilderResolver;

    /** @var ParametersBuilder */
    private $parametersBuilder;

    /** @var callable */
    private $requestInjectorResolver;

    /** @var RequestInjector */
    private $requestInjector;

    public function __construct(ClientCredentials $clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;

        $this->resolveSignerUsing(function (): Signer {
            return $this->createSigner();
        });

        $this->resolveParametersBuilderUsing(function (): ParametersBuilder {
            return new ParametersBuilder($this->getSigner());
        });

        $this->resolveRequestInjectorUsing(static function (): RequestInjector {
            return new RequestInjector();
        });
    }

    public function resolveSignerUsing(callable $callback): BaseProvider
    {
        $this->signerResolver = $callback;

        return $this;
    }

    public function resolveParametersBuilderUsing(callable $callback): BaseProvider
    {
        $this->parametersBuilderResolver = $callback;

        return $this;
    }

    public function resolveRequestInjectorUsing(callable $callback): BaseProvider
    {
        $this->requestInjectorResolver = $callback;

        return $this;
    }

    public function getParametersBuilder(): ParametersBuilder
    {
        if (null === $this->parametersBuilder) {
            $this->parametersBuilder = ($this->parametersBuilderResolver)($this);
        }

        return $this->parametersBuilder;
    }

    public function getSigner(): Signer
    {
        if (null === $this->signer) {
            $this->signer = ($this->signerResolver)($this);
        }

        return $this->signer;
    }

    public function getRequestInjector(): RequestInjector
    {
        if (null === $this->requestInjector) {
            $this->requestInjector = ($this->requestInjectorResolver)($this);
        }

        return $this->requestInjector;
    }

    public function createTemporaryCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getTemporaryCredentialsUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid temporary credentials URI');
        }

        return $requestFactory->createRequest($this->getTemporaryCredentialsMethod(), $uri);
    }

    public function prepareTemporaryCredentialsRequest(RequestInterface $request): RequestInterface
    {
        $oauthParameters = $this
            ->getParametersBuilder()
            ->forTemporaryCredentialsRequest($this->clientCredentials);

        $signature = $this
            ->getSigner()
            ->sign($request, $oauthParameters);

        return $this->getRequestInjector()->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getTemporaryCredentialsLocation()
        );
    }

    public function createAuthorizationRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getAuthorizationUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid authorization URI');
        }

        return $requestFactory->createRequest($this->getAuthorizationMethod(), $uri);
    }

    public function prepareAuthorizationRequest(
        RequestInterface $request,
        Credentials $temporaryCredentials
    ): RequestInterface {
        $oauthParameters = $this
            ->getParametersBuilder()
            ->forAuthorizationRequest($temporaryCredentials);

        $signature = $this
            ->getSigner()
            ->sign($request, $oauthParameters, $temporaryCredentials);

        return $this->getRequestInjector()->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getAuthorizationLocation()
        );
    }

    public function createTokenCredentialsRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getTokenCredentialsUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid token credentials URI');
        }

        return $requestFactory->createRequest($this->getTokenCredentialsMethod(), $uri);
    }

    public function prepareTokenCredentialsRequest(
        RequestInterface $request,
        Credentials $temporaryCredentials,
        string $verifier
    ): RequestInterface {
        $oauthParameters = $this
            ->getParametersBuilder()
            ->forTokenCredentialsRequest(
                $this->clientCredentials,
                $temporaryCredentials,
                $verifier
            );

        $signature = $this
            ->getSigner()
            ->sign($request, $oauthParameters, $temporaryCredentials);

        return $this->getRequestInjector()->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getTokenCredentialsLocation()
        );
    }

    public function createUserDetailsRequest(RequestFactoryInterface $requestFactory): RequestInterface
    {
        if (false === filter_var($uri = $this->getUserDetailsUri(), FILTER_VALIDATE_URL)) {
            throw new LogicException('You have not configured a valid user details URI');
        }

        return $requestFactory->createRequest($this->getUserDetailsMethod(), $uri);
    }

    public function prepareAuthenticatedRequest(
        RequestInterface $request,
        Credentials $tokenCredentials
    ): RequestInterface {
        $oauthParameters = $this
            ->getParametersBuilder()
            ->forAuthenticatedRequest($this->clientCredentials, $tokenCredentials);

        $signature = $this
            ->getSigner()
            ->sign($request, $oauthParameters, $tokenCredentials);

        return $this->getRequestInjector()->inject(
            $request,
            $oauthParameters,
            $signature,
            $this->getAuthenticatedLocation()
        );
    }

    /**
     * Gets the signer class to use.
     */
    protected function getSignerClass(): string
    {
        return HmacSigner::class;
    }

    /**
     * Creates a default signer instance.
     */
    private function createSigner(): Signer
    {
        $signer = $this->getSignerClass();

        return new $signer($this->clientCredentials);
    }

    protected function getTemporaryCredentialsMethod(): string
    {
        return 'GET';
    }

    protected function getTemporaryCredentialsLocation(): string
    {
        return RequestInjector::LOCATION_QUERY;
    }

    protected function getAuthorizationMethod(): string
    {
        return 'GET';
    }

    protected function getAuthorizationLocation(): string
    {
        return RequestInjector::LOCATION_QUERY;
    }

    protected function getTokenCredentialsMethod(): string
    {
        return 'POST';
    }

    protected function getTokenCredentialsLocation(): string
    {
        return RequestInjector::LOCATION_HEADER;
    }

    protected function getUserDetailsMethod(): string
    {
        return 'GET';
    }

    protected function getAuthenticatedLocation(): string
    {
        return RequestInjector::LOCATION_HEADER;
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
