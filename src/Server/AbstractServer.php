<?php

/**
 * This file is part of the league/oauth1-client library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */
namespace League\OAuth1\Client\Server;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Exceptions\Exception;
use League\OAuth1\Client\Exceptions\ConfigurationException;
use League\OAuth1\Client\Exceptions\CredentialsException;
use League\OAuth1\Client\Signature\HmacSha1Signature;
use League\OAuth1\Client\Signature\SignatureInterface;
use League\OAuth1\Client\Tool\ArrayAccessorTrait;
use League\OAuth1\Client\Tool\Crypto;
use League\OAuth1\Client\Tool\RequestFactory;
use League\OAuth1\Client\Tool\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractServer
{
    use ArrayAccessorTrait;

    /**
     * Client credentials.
     *
     * @var ClientCredentials
     */
    protected $clientCredentials;

    /**
     * Signature.
     *
     * @var SignatureInterface
     */
    protected $signature;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * The response type for data returned from API calls.
     *
     * @var string
     */
    protected $responseType = 'json';

    /**
     * Cached user details response.
     *
     * @var unknown
     */
    protected $cachedUserDetailsResponse;

    /**
     * Optional user agent.
     *
     * @var string
     */
    protected $userAgent;

    /**
     * Creates a new server instance.
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options, array $collaborators = array())
    {
        $clientCredentials = ClientCredentials::createFromOptions($options);

        $this->setClientCredentials($clientCredentials)
            ->setCollaborators($collaborators, $options)
            ->setOptions($options);
    }

    /**
     * Redirects the client to the authorization URL.
     *
     * @param TemporaryCredentials|string $temporaryIdentifier
     */
    public function authorize($temporaryIdentifier)
    {
        $url = $this->getAuthorizationUrl($temporaryIdentifier);

        header('Location: '.$url);

        return;
    }

    /**
     * Builds Guzzle HTTP client headers.
     *
     * @return array
     */
    protected function buildHttpClientHeaders($headers = array())
    {
        $defaultHeaders = $this->getHttpClientDefaultHeaders();

        return array_merge($headers, $defaultHeaders);
    }

    /**
     * Builds a url by combining hostname and query string after checking for
     * exisiting '?' character in host.
     *
     * @param string $host
     * @param string $queryString
     *
     * @return string
     */
    protected function buildUrl($host, $queryString)
    {
        return $host.(strpos($host, '?') !== false ? '&' : '?').$queryString;
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string      $data     Parsed response data
     *
     * @throws IdentityProviderException
     */
    abstract protected function checkResponse(ResponseInterface $response, $data);

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array            $response
     * @param TokenCredentials $token
     *
     * @return ResourceOwnerInterface
     */
    abstract protected function createResourceOwner(array $response, TokenCredentials $tokenCredentials);

    /**
     * Fetches user details from the remote service.
     *
     * @param TokenCredentials $tokenCredentials
     * @param bool             $force
     *
     * @return array HTTP client response
     */
    protected function fetchResourceOwnerDetails(TokenCredentials $tokenCredentials, $force = true)
    {
        if (!$this->cachedUserDetailsResponse || $force) {
            $uri = $this->getResourceOwnerDetailsUrl($tokenCredentials);
            $headers = $this->getHeaders($tokenCredentials, 'GET', $uri);

            try {
                $request = $this->getRequestFactory()->getRequest(
                    'GET',
                    $uri,
                    $headers
                );
                $response = $this->getHttpClient()->send($request);
            } catch (BadResponseException $e) {
                Exception::handleUserDetailsRetrievalException($e);
            }

            $this->parseResourceOwnersDetailsResponse($response);
            $this->checkResponse($response, $this->cachedUserDetailsResponse);
        }

        return $this->cachedUserDetailsResponse;
    }

    /**
     * Retrieves any additional required protocol parameters for an
     * OAuth request.
     *
     * @return array
     */
    protected function getAdditionalProtocolParameters()
    {
        return array();
    }

    /**
     * Creates a new authenticated request.
     *
     * @param string           $method
     * @param string           $url
     * @param TokenCredentials $tokenCredentials
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public function getAuthenticatedRequest($method, $url, TokenCredentials $tokenCredentials)
    {
        $headers = $this->getHeaders($tokenCredentials, $method, $url);

        return $this->getRequestFactory()->getRequest($method, $url, $headers);
    }

    /**
     * Gets the authorization URL by passing in the temporary credentials
     * identifier or an object instance.
     *
     * @param TemporaryCredentials|string $temporaryIdentifier
     * @param array                       $options
     *
     * @return string
     */
    public function getAuthorizationUrl($temporaryIdentifier, array $options = array())
    {
        // Somebody can pass through an instance of temporary
        // credentials and we'll extract the identifier from there.
        if ($temporaryIdentifier instanceof TemporaryCredentials) {
            $temporaryIdentifier = $temporaryIdentifier->getIdentifier();
        }

        $parameters = array('oauth_token' => $temporaryIdentifier);

        $url = $this->getBaseAuthorizationUrl($options);
        $queryString = http_build_query($parameters);

        return $this->buildUrl($url, $queryString);
    }

    /**
     * Gets the URL for redirecting the resource owner to authorize the client.
     *
     * @return string
     */
    abstract protected function getBaseAuthorizationUrl();

    /**
     * Gets the base protocol parameters for an OAuth request.
     * Each request builds on these parameters.
     *
     * @return array
     *
     * @see    OAuth 1.0 RFC 5849 Section 3.1
     */
    protected function getBaseProtocolParameters()
    {
        return array(
            'oauth_consumer_key' => $this->clientCredentials->getIdentifier(),
            'oauth_nonce' => Crypto::nonce(),
            'oauth_signature_method' => $this->signature->method(),
            'oauth_timestamp' => (new \DateTime())->format('U'),
            'oauth_version' => '1.0',
        );
    }

    /**
     * Gets the URL for retrieving temporary credentials.
     *
     * @return string
     */
    abstract protected function getBaseTemporaryCredentialsUrl();

    /**
     * Gets the URL retrieving token credentials.
     *
     * @return string
     */
    abstract protected function getBaseTokenCredentialsUrl();

    /**
     * Gets the client credentials associated with the server.
     *
     * @return ClientCredentials
     */
    public function getClientCredentials()
    {
        return $this->clientCredentials;
    }

    /**
     * Retrieves default http client.
     *
     * @return HttpClient
     */
    protected function getDefaultHttpClient(array $options = array())
    {
        $clientOptions = ['timeout'];

        return new HttpClient(
            array_intersect_key($options, array_flip($clientOptions))
        );
    }

    /**
     * Retrieves default request factory.
     *
     * @return RequestFactoryInterface
     */
    protected function getDefaultRequestFactory()
    {
        return new RequestFactory();
    }

    /**
     * Retrieves default signature.
     *
     * @return SignatureInterface
     */
    protected function getDefaultSignature()
    {
        return new HmacSha1Signature($this->clientCredentials);
    }

    /**
     * Gets all headers required to created an authenticated request.
     *
     * @param Credentials $credentials
     * @param string      $method
     * @param string      $url
     * @param array       $bodyParameters
     *
     * @return array
     */
    public function getHeaders(Credentials $credentials, $method, $url, array $bodyParameters = array())
    {
        $header = $this->getProtocolHeader(strtoupper($method), $url, $credentials, $bodyParameters);
        $authorizationHeader = array('Authorization' => $header);
        $headers = $this->buildHttpClientHeaders($authorizationHeader);

        return $headers;
    }

    /**
     * Retrieves currently configured http client.
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Gets Guzzle HTTP client default headers.
     *
     * @return array
     */
    protected function getHttpClientDefaultHeaders()
    {
        $defaultHeaders = array();
        if (!empty($this->userAgent)) {
            $defaultHeaders['User-Agent'] = $this->userAgent;
        }

        return $defaultHeaders;
    }

    /**
     * Generates the OAuth protocol header for requests other than temporary
     * credentials, based on the URI, method, given credentials & body query
     * string.
     *
     * @param string      $method
     * @param string      $uri
     * @param Credentials $credentials
     * @param array       $bodyParameters
     *
     * @return string
     */
    protected function getProtocolHeader($method, $uri, Credentials $credentials, array $bodyParameters = array())
    {
        $parameters = array_merge(
            $this->getBaseProtocolParameters(),
            $this->getAdditionalProtocolParameters(),
            array(
                'oauth_token' => $credentials->getIdentifier(),
                'oauth_verifier' => static::getValueByKey($bodyParameters, 'oauth_verifier'),
            )
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
     * Retrieves currently configured request factory.
     *
     * @return RequestFactoryInterface
     */
    public function getRequestFactory()
    {
        return $this->requestFactory;
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param TokenCredentials $tokenCredentials
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner(TokenCredentials $tokenCredentials, $force = false)
    {
        $response = $this->fetchResourceOwnerDetails($tokenCredentials, $force);

        return $this->createResourceOwner($response, $tokenCredentials);
    }

    /**
     * Gets the URL for retrieving user details.
     *
     * @param TokenCredentials $tokenCredentials
     *
     * @return string
     */
    abstract protected function getResourceOwnerDetailsUrl(TokenCredentials $tokenCredentials);

    /**
     * Retrieves the signature associated with the server.
     *
     * @return SignatureInterface
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Gets temporary credentials by performing a request to
     * the server.
     *
     * @return TemporaryCredentials
     *
     * @throws CredentialsException If the request failed
     */
    public function getTemporaryCredentials()
    {
        $uri = $this->getBaseTemporaryCredentialsUrl();
        $header = $this->getTemporaryCredentialsProtocolHeader($uri);
        $authorizationHeader = array('Authorization' => $header);
        $headers = $this->buildHttpClientHeaders($authorizationHeader);

        try {
            $request = $this->getRequestFactory()->getRequest('POST', $uri, $headers);
            $response = $this->getHttpClient()->send($request);
        } catch (BadResponseException $e) {
            throw CredentialsException::temporaryCredentialsBadResponse($e);
        }

        return TemporaryCredentials::createFromResponse($response);
    }

    /**
     * Generates the OAuth protocol header for a temporary credentials
     * request, based on the URI.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function getTemporaryCredentialsProtocolHeader($uri)
    {
        $parameters = array_merge($this->getBaseProtocolParameters(), array(
            'oauth_callback' => $this->clientCredentials->getCallbackUri(),
        ));

        $parameters['oauth_signature'] = $this->signature->sign($uri, $parameters, 'POST');

        return $this->normalizeProtocolParameters($parameters);
    }

    /**
     * Retrieves token credentials by passing in the temporary credentials,
     * the temporary credentials identifier as passed back by the server
     * and finally the verifier code.
     *
     * @param TemporaryCredentials $temporaryCredentials
     * @param string               $temporaryIdentifier
     * @param string               $verifier
     *
     * @return TokenCredentials
     *
     * @throws CredentialsException If the request failed
     */
    public function getTokenCredentials(TemporaryCredentials $temporaryCredentials, $temporaryIdentifier, $verifier)
    {
        $temporaryCredentials->checkIdentifier($temporaryIdentifier);
        $uri = $this->getBaseTokenCredentialsUrl();
        $bodyParameters = array('oauth_verifier' => $verifier);
        $headers = $this->getHeaders($temporaryCredentials, 'POST', $uri, $bodyParameters);
        $body = json_encode($bodyParameters);

        try {
            $request = $this->getRequestFactory()->getRequest('POST', $uri, $headers, $body);
            $response = $this->getHttpClient()->send($request);
        } catch (BadResponseException $e) {
            throw CredentialsException::tokenCredentialsBadResponse($e);
        }

        return TokenCredentials::createFromResponse($response);
    }

    /**
     * Takes an array of protocol parameters and normalizes them
     * to be used as a HTTP header.
     *
     * @param array $parameters
     *
     * @return string
     */
    protected function normalizeProtocolParameters(array $parameters)
    {
        $parameters = array_filter($parameters, function ($value) {
            return !empty($value);
        });

        array_walk($parameters, function (&$value, $key) {
            $value = rawurlencode($key).'="'.rawurlencode($value).'"';
        });

        ksort($parameters);

        return 'OAuth '.implode(', ', $parameters);
    }

    /**
     * Parses user details http response, attempts to assign to cached user
     * detail response property.
     *
     * @param ResponseInterface $response
     *
     * @throws ConfigurationException
     */
    protected function parseResourceOwnersDetailsResponse(ResponseInterface $response)
    {
        switch ($this->responseType) {
            case 'json':
                $this->cachedUserDetailsResponse = json_decode((string) $response->getBody(), true);
                break;
            case 'xml':
                $this->cachedUserDetailsResponse = simplexml_load_string((string) $response->getBody());
                break;
            case 'string':
                parse_str($response->getBody(), $this->cachedUserDetailsResponse);
                break;
            default:
                throw ConfigurationException::invalidResponseType($this->responseType);
        }
    }

    /**
     * Updates currently configured client credentials.
     *
     * @param ClientCredentials $clientCredentials
     *
     * @return AbstractServer
     */
    public function setClientCredentials(ClientCredentials $clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;

        return $this;
    }

    /**
     * Attempts to configure and set collaborators.
     *
     * @param array $collaborators
     * @param array $options
     *
     * @return AbstractServer
     */
    protected function setCollaborators(array $collaborators, array $options = array())
    {
        $defaults = [
            'httpClient' => 'getDefaultHttpClient',
            'requestFactory' => 'getDefaultRequestFactory',
            'signature' => 'getDefaultSignature',
        ];

        array_walk($defaults, function ($method, $key) use (&$collaborators, $options) {
            if (empty($collaborators[$key])) {
                $collaborators[$key] = call_user_func_array([$this, $method], [$options]);
            }
        });

        return $this->setSignature($collaborators['signature'])
            ->setRequestFactory($collaborators['requestFactory'])
            ->setHttpClient($collaborators['httpClient']);
    }

    /**
     * Updates currently configured http client.
     *
     * @param HttpClientInterface $httpClient
     *
     * @return AbstractServer
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Attempts to set the given options as properties on server, if defined.
     *
     * @param array $options
     *
     * @return AbstractServer
     */
    protected function setOptions(array $options)
    {
        array_walk($options, function ($value, $key) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        });

        return $this;
    }

    /**
     * Updates currently configured request factory.
     *
     * @param RequestFactoryInterface $requestFactory
     *
     * @return AbstractServer
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * Updates currently configured signature.
     *
     * @param SignatureInterface $signature
     *
     * @return AbstractServer
     */
    public function setSignature(SignatureInterface $signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Sets the user agent value.
     *
     * @param string $userAgent
     *
     * @return Server
     */
    public function setUserAgent($userAgent = null)
    {
        $this->userAgent = $userAgent;

        return $this;
    }
}
