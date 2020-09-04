<?php

namespace League\OAuth1\Client;

use Exception;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Signature\Signer;

class ParametersBuilder
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
     * @return array<string>
     */
    public function forTemporaryCredentialsRequest(ClientCredentials $clientCredentials): array
    {
        return array_replace($this->generateBaseParameters(), [
            'oauth_consumer_key' => $clientCredentials->getIdentifier(),
            'oauth_callback'     => (string) $clientCredentials->getCallbackUri(),
        ]);
    }

    /**
     * @return array<string>
     */
    public function forAuthorizationRequest(Credentials $temporaryCredentials): array
    {
        return array_replace($this->generateBaseParameters(), [
            'oauth_token' => $temporaryCredentials->getIdentifier(),
        ]);
    }

    /**
     * @return array<string>
     */
    public function forTokenCredentialsRequest(
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials,
        string $verifier
    ): array {
        return array_replace($this->generateBaseParameters(), [
            'oauth_consumer_key' => $clientCredentials->getIdentifier(),
            'oauth_token'        => $temporaryCredentials->getIdentifier(),
            'oauth_verifier'     => $verifier,
        ]);
    }

    /**
     * @return array<string>
     */
    public function forAuthenticatedRequest(
        ClientCredentials $clientCredentials,
        Credentials $tokenCredentials
    ): array {
        return array_replace($this->generateBaseParameters(), [
            'oauth_consumer_key' => $clientCredentials->getIdentifier(),
            'oauth_token'        => $tokenCredentials->getIdentifier(),
        ]);
    }

    /**
     * @return array<string, int|string>
     */
    private function generateBaseParameters(): array
    {
        $parameters = [
            'oauth_signature_method' => $this->signer->getMethod(),
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
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
