<?php

namespace League\OAuth1\Client;

use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\stream_for;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

class RequestInjector
{
    public const LOCATION_HEADER = 'header';
    public const LOCATION_BODY   = 'body';
    public const LOCATION_QUERY  = 'query';

    private const FORM_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    /**
     * @param array<string, string|int> $oauthParameters
     */
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

    /**
     * @param array<string, string|int> $oauthParameters
     */
    private function injectHeader(RequestInterface $request, array $oauthParameters): RequestInterface
    {
        // Replace the authorization header
        $request = $request->withHeader(...$this->createAuthorizationHeader($oauthParameters));

        return $this->fixGuzzleCasting($request);
    }

    /**
     * @param array<string, string|int> $oauthParameters
     */
    private function injectBody(RequestInterface $request, array $oauthParameters): RequestInterface
    {
        $body = build_query($oauthParameters);

        // Replace the existing body
        $request = $request
            ->withHeader('Content-Type', self::FORM_CONTENT_TYPE)
            ->withBody(stream_for($body));

        return $this->fixGuzzleCasting($request);
    }

    /**
     * @param array<string, string|int> $oauthParameters
     */
    private function injectQuery(RequestInterface $request, array $oauthParameters): RequestInterface
    {
        $query = build_query($oauthParameters);

        // Replace existing query string
        return $request->withUri($request->getUri()->withQuery($query));
    }

    /**
     * Creates an authorization header from the given OAuth parameters.
     *
     * @param array<string, string|int> $oauthParameters
     *
     * @return array<int, string>
     *
     * @see https://tools.ietf.org/html/rfc5849#section-3.5.1 Authorization Header
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

    /**
     * There's an issue where Guzzle type declares the return type of modifying
     * a `RequestInterface` as a `MessageInterface` due to the use of a shared
     * trait (as the former extends the latter). This is incorrect according
     * to PSR-7 and this upsets both PHPStorm and PHPStan. A workaround here
     * is to change the casting to keep the static analysis tool happy.
     *
     * @todo Remove this if/when Guzzle fix their castings
     */
    private function fixGuzzleCasting(MessageInterface $request): RequestInterface
    {
        /* @var RequestInterface $request */

        // @phpstan-ignore-next-line
        return $request;
    }
}
