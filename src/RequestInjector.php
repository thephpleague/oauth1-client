<?php

namespace League\OAuth1\Client;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\stream_for;

class RequestInjector
{
    public const LOCATION_HEADER = 'header';
    public const LOCATION_BODY   = 'body';
    public const LOCATION_QUERY  = 'query';

    private const FORM_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    public function inject(
        RequestInterface $request,
        array $oauthParameters,
        string $signature,
        string $location
    ): RequestInterface {
        $oauthParameters['oauth_signature'] = $signature;

        switch ($location) {
            case self::LOCATION_HEADER:
                return $this->injectHeader($request, $oauthParameters);
            case self::LOCATION_BODY:
                return $this->injectBody($request, $oauthParameters);
            case self::LOCATION_QUERY:
                return $this->injectQuery($request, $oauthParameters);
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid request injection location "%s".',
            $location
        ));
    }

    private function injectHeader(RequestInterface $request, array $oauthParameters): RequestInterface
    {
        // Replace the authorization header
        return $request->withHeader(...$this->createAuthorizationHeader($oauthParameters));
    }

    private function injectBody(RequestInterface $request, array $oauthParameters): RequestInterface
    {
        $body = build_query($oauthParameters);

        // Replace the existing body
        return $request
            ->withHeader('Content-Type', self::FORM_CONTENT_TYPE)
            ->withBody(stream_for($body));
    }

    private function injectQuery(RequestInterface $request, array $oauthParameters): RequestInterface
    {
        $query = build_query($oauthParameters);

        // Replace existing query string
        return $request->withUri($request->getUri()->withQuery($query));
    }

    /**
     * Creates an authorization header from the given OAuth parameters.
     *
     * @link https://tools.ietf.org/html/rfc5849#section-3.5.1 Authorization Header
     */
    private function createAuthorizationHeader(array $oauthParameters): array
    {
        $parts = [];

        if (isset($oauthParameters['realm'])) {
            $parts[] = sprintf('realm="%s"', rawurlencode($oauthParameters['realm']));
            unset($oauthParameters['realm']);
        }

        foreach ($oauthParameters as $key => $value) {
            $parts[] = sprintf('%s="%s"', rawurlencode($key), rawurlencode($value));
        }

        $value = sprintf('OAuth %s', implode(', ', $parts));

        return ['Authorization', $value];
    }
}