<?php

namespace League\OAuth1\Client\Server;

use DateTime;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsException;
use League\OAuth1\Client\Credentials\CredentialsInterface;
use League\OAuth1\Client\Credentials\RsaClientCredentials;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\HmacSha1Signature;
use League\OAuth1\Client\Signature\RsaSha1Signature;
use League\OAuth1\Client\Signature\SignatureInterface;
use SimpleXMLElement;

abstract class Server
{
    /** @var ClientCredentials */
    protected $clientCredentials;

    /** @var SignatureInterface */
    protected $signature;

    /**
     * The response type for data returned from API calls.
     *
     * @var string
     */
    protected $responseType = 'json';

    /** @var mixed */
    protected $cachedUserDetailsResponse;

    /**
     * Optional user agent used when sending requests to the server.
     *
     * @var string
     */
    protected $userAgent;

    /**
     * @param ClientCredentials|array $clientCredentials
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        // Pass through an array or client credentials, we don't care
        if (is_array($clientCredentials)) {
            $clientCredentials = $this->createClientCredentials($clientCredentials);
        } elseif ( ! $clientCredentials instanceof ClientCredentialsInterface) {
            throw new InvalidArgumentException('Client credentials must be an array or valid object.');
        }

        $this->clientCredentials = $clientCredentials;

        if ( ! $signature && $clientCredentials instanceof RsaClientCredentials) {
            $signature = new RsaSha1Signature($clientCredentials);
        }
        $this->signature = $signature ?: new HmacSha1Signature($clientCredentials);
    }

    /**
     * Gets temporary credentials by performing a request to
     * the server.
     *
     * @throws CredentialsException If a "bad response" is received by the server
     * @throws GuzzleException
     */
    public function getTemporaryCredentials(): TemporaryCredentials
    {
        $uri = $this->urlTemporaryCredentials();

        $client = $this->createHttpClient();

        $header = $this->temporaryCredentialsProtocolHeader($uri);
        $authorizationHeader = ['Authorization' => $header];
        $headers = $this->buildHttpClientHeaders($authorizationHeader);

        try {
            $response = $client->post($uri, [
                'headers' => $headers,
            ]);
        } catch (BadResponseException $e) {
            throw $this->getCredentialsExceptionForBadResponse($e, 'temporary credentials');
        }

        return $this->createTemporaryCredentials((string) $response->getBody());
    }

    /**
     * Get the authorization URL by passing in the temporary credentials
     * identifier or an object instance.
     *
     * @param TemporaryCredentials|string $temporaryIdentifier
     */
    public function getAuthorizationUrl($temporaryIdentifier): string
    {
        // Somebody can pass through an instance of temporary
        // credentials and we'll extract the identifier from there.
        if ($temporaryIdentifier instanceof TemporaryCredentials) {
            $temporaryIdentifier = $temporaryIdentifier->getIdentifier();
        }

        $parameters = ['oauth_token' => $temporaryIdentifier];

        $url = $this->urlAuthorization();
        $queryString = http_build_query($parameters);

        return $this->buildUrl($url, $queryString);
    }

    /**
     * Redirect the client to the authorization URL.
     *
     * @param TemporaryCredentials|string $temporaryIdentifier
     */
    public function authorize($temporaryIdentifier): void
    {
        $url = $this->getAuthorizationUrl($temporaryIdentifier);

        header('Location: ' . $url);
    }

    /**
     * Retrieves token credentials by passing in the temporary credentials,
     * the temporary credentials identifier as passed back by the server
     * and finally the verifier code.
     *
     * @throws CredentialsException If a "bad response" is received by the server
     * @throws GuzzleException
     */
    public function getTokenCredentials(
        TemporaryCredentials $temporaryCredentials,
        string $temporaryIdentifier,
        string $verifier
    ): TokenCredentials {
        if ($temporaryIdentifier !== $temporaryCredentials->getIdentifier()) {
            throw new InvalidArgumentException(
                'Temporary identifier passed back by server does not match that of stored temporary credentials.
                Potential man-in-the-middle.'
            );
        }

        $uri = $this->urlTokenCredentials();
        $bodyParameters = ['oauth_verifier' => $verifier];

        $client = $this->createHttpClient();

        $headers = $this->getHeaders($temporaryCredentials, 'POST', $uri, $bodyParameters);

        try {
            $response = $client->post($uri, [
                'headers' => $headers,
                'form_params' => $bodyParameters,
            ]);
        } catch (BadResponseException $e) {
            throw $this->getCredentialsExceptionForBadResponse($e, 'token credentials');
        }

        return $this->createTokenCredentials((string) $response->getBody());
    }

    /**
     * Get user details by providing valid token credentials.
     *
     * @throws CredentialsException If a "bad response" is received by the server
     * @throws GuzzleException
     */
    public function getUserDetails(TokenCredentials $tokenCredentials, bool $force = false): User
    {
        $data = $this->fetchUserDetails($tokenCredentials, $force);

        return $this->userDetails($data, $tokenCredentials);
    }

    /**
     * Get the user's unique identifier (primary key).
     *
     * @return string|int|null
     *
     * @throws CredentialsException If a "bad response" is received by the server
     * @throws GuzzleException
     */
    public function getUserUid(TokenCredentials $tokenCredentials, bool $force = false)
    {
        $data = $this->fetchUserDetails($tokenCredentials, $force);

        return $this->userUid($data, $tokenCredentials);
    }

    /**
     * Get the user's email, if available.
     *
     * @throws CredentialsException If a "bad response" is received by the server
     * @throws GuzzleException
     */
    public function getUserEmail(TokenCredentials $tokenCredentials, bool $force = false): ?string
    {
        $data = $this->fetchUserDetails($tokenCredentials, $force);

        return $this->userEmail($data, $tokenCredentials);
    }

    /**
     * Get the user's screen name (username), if available.
     *
     * @throws CredentialsException If a "bad response" is received by the server
     * @throws GuzzleException
     */
    public function getUserScreenName(TokenCredentials $tokenCredentials, bool $force = false): string
    {
        $data = $this->fetchUserDetails($tokenCredentials, $force);

        return $this->userScreenName($data, $tokenCredentials);
    }

    /**
     * Fetch user details from the remote service.
     *
     * @return array|SimpleXMLElement
     *
     * @throws CredentialsException If a "bad response" is received by the server
     * @throws GuzzleException
     */
    protected function fetchUserDetails(TokenCredentials $tokenCredentials, bool $force = true)
    {
        if ( ! $this->cachedUserDetailsResponse || $force) {
            $url = $this->urlUserDetails();

            $client = $this->createHttpClient();

            $headers = $this->getHeaders($tokenCredentials, 'GET', $url);

            try {
                $response = $client->get($url, [
                    'headers' => $headers,
                ]);
            } catch (BadResponseException $e) {
                throw $this->getCredentialsExceptionForBadResponse($e, 'user details');
            }
            switch ($this->responseType) {
                case 'json':
                    return $this->cachedUserDetailsResponse = json_decode((string) $response->getBody(), true);

                case 'xml':
                    if (function_exists('simplexml_load_string')) {
                        return $this->cachedUserDetailsResponse = simplexml_load_string((string) $response->getBody());
                    }
                    break;

                case 'string':
                    parse_str((string) $response->getBody(), $this->cachedUserDetailsResponse);
                    break;
            }

            throw new InvalidArgumentException(sprintf('Invalid response type [%s].', $this->responseType));
        }

        return $this->cachedUserDetailsResponse;
    }

    /**
     * Get the client credentials associated with the server.
     *
     * @return ClientCredentialsInterface
     */
    public function getClientCredentials()
    {
        return $this->clientCredentials;
    }

    /**
     * Get the signature associated with the server.
     *
     * @return SignatureInterface
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Creates a Guzzle HTTP client for the given URL.
     */
    public function createHttpClient(): GuzzleHttpClient
    {
        return new GuzzleHttpClient();
    }

    /**
     * Set the user agent value.
     */
    public function setUserAgent(string $userAgent = null): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get all headers required to created an authenticated request.
     */
    public function getHeaders(
        CredentialsInterface $credentials,
        string $method,
        string $url,
        array $bodyParameters = []
    ): array {
        $header = $this->protocolHeader(strtoupper($method), $url, $credentials, $bodyParameters);
        $authorizationHeader = ['Authorization' => $header];

        return $this->buildHttpClientHeaders($authorizationHeader);
    }

    /**
     * Get Guzzle HTTP client default headers.
     */
    protected function getHttpClientDefaultHeaders(): array
    {
        $defaultHeaders = [];
        if ( ! empty($this->userAgent)) {
            $defaultHeaders['User-Agent'] = $this->userAgent;
        }

        return $defaultHeaders;
    }

    /**
     * Build Guzzle HTTP client headers.
     */
    protected function buildHttpClientHeaders(array $headers = []): array
    {
        $defaultHeaders = $this->getHttpClientDefaultHeaders();

        return array_merge($headers, $defaultHeaders);
    }

    /**
     * Creates a client credentials instance from an array of credentials.
     */
    protected function createClientCredentials(array $options): ClientCredentials
    {
        $keys = ['identifier', 'secret'];

        foreach ($keys as $key) {
            if ( ! isset($options[$key])) {
                throw new InvalidArgumentException("Missing client credentials key [$key] from options.");
            }
        }

        if (isset($options['rsa_private_key'], $options['rsa_public_key'])) {
            $clientCredentials = new RsaClientCredentials();
            $clientCredentials->setRsaPrivateKey($options['rsa_private_key']);
            $clientCredentials->setRsaPublicKey($options['rsa_public_key']);
        } else {
            $clientCredentials = new ClientCredentials();
        }

        $clientCredentials->setIdentifier($options['identifier']);
        $clientCredentials->setSecret($options['secret']);

        if (isset($options['callback_uri'])) {
            $clientCredentials->setCallbackUri($options['callback_uri']);
        }

        return $clientCredentials;
    }

    /**
     * Handle a bad response coming back when getting temporary credentials.
     */
    protected function getCredentialsExceptionForBadResponse(
        BadResponseException $e,
        string $type
    ): CredentialsException {
        $response = $e->getResponse();
        $body = $response->getBody();
        $statusCode = $response->getStatusCode();

        return new CredentialsException(
            sprintf(
                'Received HTTP status code [%s] with message "%s" when getting %s.',
                $statusCode,
                $body,
                $type
            )
        );
    }

    /**
     * Creates temporary credentials from the body response.
     *
     * @throws CredentialsException If an error ocurred while parsing the given body for temporary credentials
     */
    protected function createTemporaryCredentials(string $body): TemporaryCredentials
    {
        parse_str($body, $data);

        if ( ! $data || ! is_array($data)) {
            throw new CredentialsException('Unable to parse temporary credentials response.');
        }

        if ('true' !== ($data['oauth_callback_confirmed'] ?? '')) {
            throw new CredentialsException('Error in retrieving temporary credentials.');
        }

        $temporaryCredentials = new TemporaryCredentials();
        $temporaryCredentials->setIdentifier($data['oauth_token']);
        $temporaryCredentials->setSecret($data['oauth_token_secret']);

        return $temporaryCredentials;
    }

    /**
     * Creates token credentials from the body response.
     *
     * @throws CredentialsException If an error occurred parsing the given body for token credentials
     */
    protected function createTokenCredentials(string $body): TokenCredentials
    {
        parse_str($body, $data);

        if ( ! $data || ! is_array($data)) {
            throw new CredentialsException('Unable to parse token credentials response.');
        }

        if (isset($data['error'])) {
            throw new CredentialsException("Error [{$data['error']}] in retrieving token credentials.");
        }

        $tokenCredentials = new TokenCredentials();
        $tokenCredentials->setIdentifier($data['oauth_token']);
        $tokenCredentials->setSecret($data['oauth_token_secret']);

        return $tokenCredentials;
    }

    /**
     * Get the base protocol parameters for an OAuth request.
     * Each request builds on these parameters.
     *
     * @see OAuth 1.0 RFC 5849 Section 3.1
     */
    protected function baseProtocolParameters(): array
    {
        $dateTime = new DateTime();

        return [
            'oauth_consumer_key' => $this->clientCredentials->getIdentifier(),
            'oauth_nonce' => $this->nonce(),
            'oauth_signature_method' => $this->signature->method(),
            'oauth_timestamp' => $dateTime->format('U'),
            'oauth_version' => '1.0',
        ];
    }

    /**
     * Any additional required protocol parameters for an OAuth request.
     */
    protected function additionalProtocolParameters(): array
    {
        return [];
    }

    /**
     * Generate the OAuth protocol header for a temporary credentials
     * request, based on the URI.
     */
    protected function temporaryCredentialsProtocolHeader(string $uri): string
    {
        $parameters = array_merge($this->baseProtocolParameters(), [
            'oauth_callback' => $this->clientCredentials->getCallbackUri(),
        ]);

        $parameters['oauth_signature'] = $this->signature->sign($uri, $parameters, 'POST');

        return $this->normalizeProtocolParameters($parameters);
    }

    /**
     * Generate the OAuth protocol header for requests other than temporary
     * credentials, based on the URI, method, given credentials & body query
     * string.
     */
    protected function protocolHeader(
        string $method,
        string $uri,
        CredentialsInterface $credentials,
        array $bodyParameters = []
    ): string {
        $parameters = array_merge(
            $this->baseProtocolParameters(),
            $this->additionalProtocolParameters(),
            [
                'oauth_token' => $credentials->getIdentifier(),
            ]
        );

        $this->signature->setCredentials($credentials);

        $parameters['oauth_signature'] = $this->signature->sign(
            $uri,
            array_merge($parameters, $bodyParameters),
            $method
        );

        return $this->normalizeProtocolParameters($parameters);
    }

    /**
     * Takes an array of protocol parameters and normalizes them
     * to be used as a HTTP header.
     */
    protected function normalizeProtocolParameters(array $parameters): string
    {
        array_walk($parameters, static function (&$value, $key) {
            $value = sprintf('%s="%s"', rawurlencode($key), rawurlencode($value));
        });

        return sprintf('OAuth %s', implode(', ', $parameters));
    }

    /**
     * Generate a random string.
     *
     * @see OAuth 1.0 RFC 5849 Section 3.3
     */
    protected function nonce(int $length = 32): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    /**
     * Build a url by combining hostname and query string after checking for
     * existing '?' character in host.
     */
    protected function buildUrl(string $host, string $queryString): string
    {
        return $host . (strpos($host, '?') !== false ? '&' : '?') . $queryString;
    }

    /**
     * Get the URL for retrieving temporary credentials.
     */
    abstract public function urlTemporaryCredentials(): string;

    /**
     * Get the URL for redirecting the resource owner to authorize the client.
     */
    abstract public function urlAuthorization(): string;

    /**
     * Get the URL retrieving token credentials.
     */
    abstract public function urlTokenCredentials(): string;

    /**
     * Get the URL for retrieving user details.
     */
    abstract public function urlUserDetails(): string;

    /**
     * Take the decoded data from the user details URL and convert
     * it to a User object.
     *
     * @param mixed $data
     */
    abstract public function userDetails($data, TokenCredentials $tokenCredentials): User;

    /**
     * Take the decoded data from the user details URL and extract
     * the user's UID.
     *
     * @param mixed $data
     *
     * @return string|int|null
     */
    abstract public function userUid($data, TokenCredentials $tokenCredentials);

    /**
     * Take the decoded data from the user details URL and extract
     * the user's email.
     *
     * @param mixed $data
     */
    abstract public function userEmail($data, TokenCredentials $tokenCredentials): ?string;

    /**
     * Take the decoded data from the user details URL and extract
     * the user's screen name.
     *
     * @param mixed            $data
     * @param TokenCredentials $tokenCredentials
     */
    abstract public function userScreenName($data, TokenCredentials $tokenCredentials): ?string;
}
