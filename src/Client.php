<?php

namespace League\OAuth1\Client;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Provider\Provider;
use LogicException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Client
{
    /** @var Provider */
    private $provider;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var ClientInterface */
    private $httpClient;

    /** @var ClientCredentials */
    private $clientCredentials;

    /** @var Credentials */
    private $temporaryCredentials;

    /** @var string */
    private $verifier;

    /** @var Credentials */
    private $tokenCredentials;

    public function __construct(
        Provider $provider,
        RequestFactoryInterface $requestFactory,
        ClientInterface $httpClient
    ) {
        $this->provider       = $provider;
        $this->requestFactory = $requestFactory;
        $this->httpClient     = $httpClient;
    }

    /**
     * Starts the OAuth authentication process by getting temporary credentials.
     *
     * @throws ClientExceptionInterface If an error happens while processing the request
     */
    public function fetchTemporaryCredentials(ClientCredentials $clientCredentials = null): Credentials
    {
        $clientCredentials = $this->findClientCredentials($clientCredentials);

        if (null === $clientCredentials) {
            throw new LogicException(
                'You have must first configure client credentials before fetching temporary credentials.'
            );
        }

        $request = $this->provider->createTemporaryCredentialsRequest($this->requestFactory);

        $response = $this->httpClient->sendRequest(
            $this->provider->prepareTemporaryCredentialsRequest($request, $clientCredentials)
        );

        return $this->temporaryCredentials = $this->extractTemporaryCredentials($response);
    }

    /**
     * Prepares an authorization request based on the fetched temporary credentials.
     */
    public function prepareAuthorizationRequest(
        ClientCredentials $clientCredentials = null,
        Credentials $temporaryCredentials = null
    ): RequestInterface {
        $clientCredentials    = $this->findClientCredentials($clientCredentials);
        $temporaryCredentials = $this->findTemporaryCredentials($temporaryCredentials);

        if (null === $clientCredentials || null === $temporaryCredentials) {
            throw new LogicException('You must provide temporary credentials to prepare an authorization request.');
        }

        $request = $this->provider->createAuthorizationRequest($this->requestFactory);

        return $this->provider->prepareAuthorizationRequest($request, $clientCredentials, $temporaryCredentials);
    }

    /**
     * Fetches token credentials.
     *
     * @throws ClientExceptionInterface If an error happens while processing the request
     */
    public function fetchTokenCredentials(
        ClientCredentials $clientCredentials = null,
        Credentials $temporaryCredentials = null,
        string $verifier = null
    ): Credentials {
        $clientCredentials    = $this->findClientCredentials($clientCredentials);
        $temporaryCredentials = $this->findTemporaryCredentials($temporaryCredentials);
        $verifier             = $this->findVerifier($verifier);

        if (null === $clientCredentials || null === $temporaryCredentials || null === $verifier) {
            throw new LogicException(
                'You have must first provide client credentials and authorize before fetching token credentials.'
            );
        }

        $request = $this->provider->createTokenCredentialsRequest($this->requestFactory);

        $response = $this->httpClient->sendRequest(
            $this->provider->prepareTokenCredentialsRequest(
                $request,
                $clientCredentials,
                $temporaryCredentials,
                $verifier
            )
        );

        return $this->temporaryCredentials = $this->extractTokenCredentials($response);
    }

    /**
     * Fetches user details.
     *
     * @throws ClientExceptionInterface If an error happens while processing the request
     */
    public function fetchUserDetails(
        ClientCredentials $clientCredentials = null,
        Credentials $tokenCredentials = null
    ): User {
        $request = $this->provider->createUserDetailsRequest($this->requestFactory);

        $response = $this->executeAuthenticatedRequest($request, $clientCredentials, $tokenCredentials);

        return $this->provider->extractUserDetails($response);
    }

    /**
     * Sends an authenticated request.
     *
     * @throws ClientExceptionInterface If an error happens while processing the request
     */
    public function executeAuthenticatedRequest(
        RequestInterface $request,
        ClientCredentials $clientCredentials = null,
        Credentials $tokenCredentials = null
    ): ResponseInterface {
        $clientCredentials = $this->findClientCredentials($clientCredentials);
        $tokenCredentials = $this->findTokenCredentials($tokenCredentials);

        if (null === $clientCredentials || null === $tokenCredentials) {
            throw new LogicException('You must provide client and token credentials to prepare an authenticated request.');
        }

        return $this->httpClient->sendRequest(
            $this->provider->prepareAuthenticatedRequest($request, $clientCredentials, $tokenCredentials)
        );
    }

    public function getClientCredentials(): ?ClientCredentials
    {
        return $this->clientCredentials;
    }

    public function setClientCredentials(ClientCredentials $clientCredentials): void
    {
        $this->clientCredentials = $clientCredentials;
    }

    public function getTemporaryCredentials(): ?Credentials
    {
        return $this->temporaryCredentials;
    }

    public function setTemporaryCredentials(Credentials $temporaryCredentials): void
    {
        $this->temporaryCredentials = $temporaryCredentials;
    }

    public function getVerifier(): string
    {
        return $this->verifier;
    }

    public function setVerifier(string $verifier): void
    {
        $this->verifier = $verifier;
    }

    public function getTokenCredentials(): ?Credentials
    {
        return $this->tokenCredentials;
    }

    public function setTokenCredentials(Credentials $tokenCredentials): void
    {
        $this->tokenCredentials = $tokenCredentials;
    }

    private function findClientCredentials(?ClientCredentials $clientCredentials): ?ClientCredentials
    {
        return $clientCredentials ?: $this->getClientCredentials();
    }

    private function findTemporaryCredentials(?Credentials $temporaryCredentials): ?Credentials
    {
        return $temporaryCredentials ?: $this->getTemporaryCredentials();
    }

    private function findTokenCredentials(?Credentials $tokenCredentials): ?Credentials
    {
        return $tokenCredentials ?: $this->getTokenCredentials();
    }

    private function findVerifier(?string $verifier): ?string
    {
        return $verifier ?: $this->getVerifier();
    }

    private function extractTemporaryCredentials(ResponseInterface $response): Credentials
    {
        parse_str($response->getBody()->getContents(), $data);

        if ('true' !== ($data['oauth_callback_confirmed'] ?? '')) {
            // @todo Add granular exceptions…
            throw new RuntimeException('Error in retrieving temporary credentials.');
        }

        return new Credentials($data['oauth_token'], $data['oauth_token_secret']);
    }

    private function extractTokenCredentials(ResponseInterface $response): Credentials
    {
        parse_str($response->getBody()->getContents(), $data);

        if ($data['error'] ?? null) {
            // @todo Add granular exceptions…
            throw new RuntimeException(sprintf(
                'Error received while retrieving token credentials: "%s"',
                $data['error']
            ));
        }

        return new Credentials($data['oauth_token'], $data['oauth_token_secret']);
    }
}