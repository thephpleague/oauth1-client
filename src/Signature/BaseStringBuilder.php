<?php

namespace League\OAuth1\Client\Signature;

use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\Psr7\parse_query;

class BaseStringBuilder
{
    /**
     * Creates a base string for the given Request and additional OAuth parameters.
     *
     * @link https://tools.ietf.org/html/rfc5849#section-3.4.1 Signature Base String
     */
    public function build(RequestInterface $request, array $oauthParameters = []): string
    {
        $uri = $request->getUri()->withQuery('');

        return sprintf(
            '%s&%s&%s',
            strtoupper($request->getMethod()),
            rawurlencode($uri),
            $this->normalizeParameters($request, $oauthParameters)
        );
    }

    /**
     * Normalizes parameters according to the RFC-5849 OAuth 1 spec.
     *
     * @link https://tools.ietf.org/html/rfc5849#section-3.4.1.3.1 Parameter Sources
     * @link https://tools.ietf.org/html/rfc5849#section-3.4.1.3.2 Parameters Normalization
     * @link https://tools.ietf.org/html/rfc5849#section-3.5.1     Authorization Header
     */
    private function normalizeParameters(RequestInterface $request, array $oauthParameters = []): string
    {
        // This array contains groups of key/value arrays. This allows it to contain duplicate key and values (if say
        // the same key existed in the query as well as the body) which is important for correct signature construction.
        $parameters = [];

        // Firstly, let's grab the query string
        foreach (parse_query($request->getUri()->getQuery()) as $key => $value) {
            $parameters[] = compact('key', 'value');
        }

        // Next, the page contains a form, let's merge in the encoded key/value contents
        if (
            $request->hasHeader('Content-Type')
            && 'application/x-www-form-urlencoded' === $request->getHeaderLine('Content-Type')
        ) {
            $body = parse_query($request->getBody()->getContents());

            foreach ($body as $key => $value) {
                $parameters[] = compact('key', 'value');
            }
        }

        // Finally, add in any additional parameters
        foreach ($oauthParameters as $key => $value) {

            // These two OAuth parameters are not allowed to be used for creating a new signature
            if (in_array($key, ['realm', 'oauth_signature'])) {
                continue;
            }

            $parameters[] = compact('key', 'value');
        }

        // Parameters now need to be encoded prior to sorting
        $parameters = array_map(static function (array $parameter): array {
            return [
                'key' => rawurlencode($parameter['key']),
                'value' => rawurlencode($parameter['value']),
            ];
        }, $parameters);

        // Parameters must be sorted by name, using ascending byte value ordering. If two names
        // are equal, then they must be sorted by value, using ascending byte ordering.
        usort($parameters, static function (array $params1, array $params2): int {
            $keySort = strcmp($params1['key'], $params2['key']);

            if (0 !== $keySort) {
                return $keySort;
            }

            return strcmp($params1['value'], $params2['value']);
        });

        return implode(
            '&',
            array_map(static function (array $parameter): string {
                return sprintf('%s=%s', $parameter['key'], $parameter['value']);
            }, $parameters)
        );
    }
}