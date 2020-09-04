<?php

namespace League\OAuth1\Client;

use function GuzzleHttp\Psr7\parse_query;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Exception\CredentialsFetchingFailedException;
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
    public function fetchTemporaryCredentials(): Credentials
    {
        $request = $this->provider->createTemporaryCredentialsRequest($this->requestFactory);

        $response = $this->httpClient->sendRequest(
            $this->provider->prepareTemporaryCredentialsRequest($request)
        );

        return $this->temporaryCredentials = $this->extractTemporaryCredentials($response);
    }

    /**
     * Prepares an authorization request based on the fetched temporary credentials.
     */
    public function prepareAuthorizationRequest(Credentials $temporaryCredentials = null): RequestInterface
    {
        $temporaryCredentials = $this->findTemporaryCredentials($temporaryCredentials);

        if (null === $temporaryCredentials) {
            throw new LogicException('You must provide temporary credentials to prepare an authorization request.');
        }

        $request = $this->provider->createAuthorizationRequest($this->requestFactory);

        return $this->provider->prepareAuthorizationRequest($request, $temporaryCredentials);
    }

    /**
     * Fetches token credentials.
     *
     * @throws ClientExceptionInterface If an error happens while processing the request
     */
    public function fetchTokenCredentials(
        Credentials $temporaryCredentials = null,
        string $verifier = null
    ): Credentials {
        $temporaryCredentials = $this->findTemporaryCredentials($temporaryCredentials);
        $verifier             = $this->findVerifier($verifier);

        if (null === $temporaryCredentials || null === $verifier) {
            throw new LogicException(
                'You have must first provide client credentials and authorize before fetching token credentials.'
            );
        }

        $request = $this->provider->createTokenCredentialsRequest($this->requestFactory);

        $response = $this->httpClient->sendRequest(
            $this->provider->prepareTokenCredentialsRequest(
                $request,
                $temporaryCredentials,
                $verifier
            )
        );

        return $this->tokenCredentials = $this->extractTokenCredentials($response);
    }

    /**
     * Fetches user details.
     *
     * @throws ClientExceptionInterface If an error happens while processing the request
     */
    public function fetchUserDetails(Credentials $tokenCredentials = null): User
    {
        $request = $this->provider->createUserDetailsRequest($this->requestFactory);

        $response = $this->executeAuthenticatedRequest($request, $tokenCredentials);

        return $this->provider->extractUserDetails($response);
    }

    /**
     * Sends an authenticated request.
     *
     * @throws ClientExceptionInterface If an error happens while processing the request
     */
    public function executeAuthenticatedRequest(
        RequestInterface $request,
        Credentials $tokenCredentials = null
    ): ResponseInterface {
        $tokenCredentials = $this->findTokenCredentials($tokenCredentials);

        if (null === $tokenCredentials) {
            throw new LogicException('You must provide client and token credentials to prepare an authenticated request.');
        }

        return $this->httpClient->sendRequest(
            $this->provider->prepareAuthenticatedRequest($request, $tokenCredentials)
        );
    }

    public function getTemporaryCredentials(): ?Credentials
    {
        return $this->temporaryCredentials;
    }

    public function setTemporaryCredentials(Credentials $temporaryCredentials): void
    {
        $this->temporaryCredentials = $temporaryCredentials;
    }

    public function getVerifier(): ?string
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
        $data = parse_query($response->getBody()->getContents());

        if (
            'true' !== ($data['oauth_callback_confirmed'] ?? null)
            || empty($data['oauth_token'] ?? null)
            || empty($data['oauth_token_secret'] ?? null)
        ) {
            throw CredentialsFetchingFailedException::forTemporaryCredentials($response);
        }

        return new Credentials($data['oauth_token'], $data['oauth_token_secret']);
    }

    private function extractTokenCredentials(ResponseInterface $response): Credentials
    {
        $data = parse_query($response->getBody()->getContents());

        if (
            ($data['error'] ?? null)
            || empty($data['oauth_token'] ?? null)
            || empty($data['oauth_token_secret'] ?? null)
        ) {
            throw CredentialsFetchingFailedException::forTokenCredentials($response);
        }

        return new Credentials($data['oauth_token'], $data['oauth_token_secret']);
    }
}
