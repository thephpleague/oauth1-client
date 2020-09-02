<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

trait EncodesQuery
{
    /**
     * Generate a base string for a RSA-SHA1 signature
     * based on the given a url, method, and any parameters.
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

        $baseString .= $this->queryStringFromData($data);

        return $baseString;
    }

    /**
     * Creates an array of rawurlencoded strings out of each array key/value pair
     * Handles multi-demensional arrays recursively.
     *
     * @param array      $data        Array of parameters to convert.
     * @param array|null $queryParams Array to extend. False by default.
     * @param string     $parentKey   Optional Array key to append
     *
     * @return string|array A `rawurlencoded` string version of data or an array of nested values when used recursively
     */
    protected function queryStringFromData(array $data, array $queryParams = null, string $parentKey = '')
    {
        if ($initial = (null === $queryParams)) {
            $queryParams = [];
        }

        foreach ($data as $key => $value) {
            if ($parentKey) {
                $key = $parentKey . '[' . $key . ']'; // Handle multi-dimensional array
            }

            if (is_array($value)) {
                $queryParams = $this->queryStringFromData($value, $queryParams, $key);
            } else {
                $queryParams[] = rawurlencode($key . '=' . $value); // join with equals sign
            }
        }

        if ($initial) {
            return implode('%26', $queryParams); // join with ampersand
        }

        return $queryParams;
    }
}
