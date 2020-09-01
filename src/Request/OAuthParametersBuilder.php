<?php

namespace League\OAuth1\Client\Request;

use Exception;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Signature\Signer;

class OAuthParametersBuilder
{
    /** @var Signer */
    private $signer;

    /** @var string|null */
    private $realm;

    public function __construct(Signer $signer, string $realm = null)
    {
        $this->signer = $signer;
        $this->realm  = $realm;
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy
     */
    public function forTemporaryCredentialsRequest(ClientCredentials $clientCredentials): array
    {
        return array_replace($this->generateBaseParameters(), [
            'oauth_consumer_key' => $clientCredentials->getIdentifier(),
            'oauth_callback' => $clientCredentials->getCallbackUri(),
        ]);
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy
     */
    public function forAuthorizationRequest(Credentials $temporaryCredentials): array
    {
        return array_replace($this->generateBaseParameters(), [
            'oauth_token' => $temporaryCredentials->getIdentifier(),
        ]);
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy
     */
    public function forTokenCredentialsRequest(
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials,
        string $verifier
    ): array {
        return array_replace($this->generateBaseParameters(), [
            'oauth_consumer_key' => $clientCredentials->getIdentifier(),
            'oauth_token' => $temporaryCredentials->getIdentifier(),
            'oauth_verifier' => $verifier,
        ]);
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy
     */
    public function forAuthenticatedRequest(
        ClientCredentials $clientCredentials,
        Credentials $tokenCredentials
    ): array {
        return array_replace($this->generateBaseParameters(), [
            'oauth_consumer_key' => $clientCredentials->getIdentifier(),
            'oauth_token' => $tokenCredentials->getIdentifier(),
        ]);
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy
     */
    private function generateBaseParameters(): array
    {
        $parameters = [
            'oauth_signature_method' => $this->signer->getMethod(),
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
        ];

        if ($this->realm) {
            $parameters['realm'] = $this->realm;
        }

        return $parameters;
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy
     */
    protected function generateNonce(): string
    {
        return bin2hex(random_bytes(8));
    }
}