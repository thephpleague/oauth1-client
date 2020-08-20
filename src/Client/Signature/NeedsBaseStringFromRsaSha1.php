<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

trait NeedsBaseStringFromRsaSha1
{
    /**
     * Generate a base string for a RSA-SHA1 signature
     * based on the given a url, method, and any parameters.
     *
     * @param UriInterface $url
     * @param string $method
     * @param array $parameters
     *
     * @return string
     */
    protected function baseString(UriInterface $url, string $method = 'POST', array $parameters = []): string
    {
        $baseString = rawurlencode($method) . '&';

        $schemeHostPath = Uri::fromParts([
            'scheme' => $url->getScheme(),
            'host' => $url->getHost(),
            'path' => $url->getPath(),
        ]);

        $baseString .= rawurlencode($schemeHostPath) . '&';

        parse_str($url->getQuery(), $query);
        $data = array_merge($query, $parameters);

        // normalize data key/values
        array_walk_recursive($data, static function (&$key, &$value) {
            $key = rawurlencode(rawurldecode($key));
            $value = rawurlencode(rawurldecode($value));
        });
        ksort($data);

        $baseString .= http_build_query($data);

        return $baseString;
    }
}
