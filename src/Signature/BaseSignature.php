<?php

namespace League\OAuth1\Client\Signature;

use Exception;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Credentials\CredentialsInterface;
use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\Psr7\parse_query;

abstract class BaseSignature implements Signature
{
    /** @var ClientCredentialsInterface */
    protected $clientCredentials;

    /** @var CredentialsInterface */
    protected $contextCredentials;

    /** @var BaseStringBuilder */
    protected $baseStringBuilder;

    protected function __construct(
        ClientCredentials $clientCredentials,
        Credentials $contextCredentials
    ) {
        $this->clientCredentials  = $clientCredentials;
        $this->contextCredentials = $contextCredentials;

        // @todo Allow custom resolution of parameter normalizer
        $this->baseStringBuilder = new BaseStringBuilder();
    }

    public static function withTemporaryCredentials(
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials
    ): Signature {
        return new static($clientCredentials, $temporaryCredentials);
    }

    public static function withTokenCredentials(
        ClientCredentials $clientCredentials,
        Credentials $tokenCredentials
    ): Signature {
        return new static($clientCredentials, $tokenCredentials);
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy for nonce
     */
    protected function generateOAuthParameters(): array
    {
        return [
            'oauth_consumer_key' => $this->clientCredentials->getIdentifier(),
            'oauth_token' => $this->contextCredentials->getIdentifier(),
            'oauth_signature_method' => $this->getMethod(),
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->getNonce(),
        ];
    }

    /**
     * Creates an authorization header from the given OAuth parameters.
     *
     * @link https://tools.ietf.org/html/rfc5849#section-3.5.1 Authorization Header
     */
    protected function createAuthorizationHeader(array $oauthParameters, string $realm = null): array
    {
        $parts = [];

        foreach ($oauthParameters as $key => $value) {
            $parts[] = sprintf('%s="%s"', $key, $value);
        }

        if ($realm) {
            array_unshift($parts, sprintf('realm="%s"', $realm));
        }

        $value = sprintf('OAuth %s', implode(', ', $parts));

        return ['Authorization', $value];
    }

    /**
     * @throws Exception If it was not possible to gather sufficient entropy
     */
    protected function getNonce(): string
    {
        return bin2hex(random_bytes(8));
    }
}
