<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

trait EncodesUrl
{
    /**
     * Create a Guzzle url for the given URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     */
    protected function createUrl($uri)
    {
        return Psr7\Utils::uriFor($uri);
    }

    /**
     * Generate a base string for a RSA-SHA1 signature
     * based on the given a url, method, and any parameters.
     *
     * @param UriInterface $url
     * @param string       $method
     * @param array        $parameters
     *
     * @return string
     */
    protected function baseString(UriInterface $url, $method = 'POST', array $parameters = [])
    {
        $baseString = rawurlencode($method) . '&';

        $schemeHostPath = Uri::fromParts([
            'scheme' => $url->getScheme(),
            'host' => $url->getHost(),
            'port' => $url->getPort(),
            'path' => $url->getPath(),
        ]);

        $baseString .= rawurlencode($schemeHostPath) . '&';

        parse_str($url->getQuery(), $query);
        $query = $this->normalizeArray($query);
        $queryParams = $this->paramsFromData($query, '', false, true);

        $parameters = $this->normalizeArray($parameters);
        $otherParams = $this->paramsFromData($parameters);

        $params = array_merge($queryParams, $otherParams);
        // Sort the final key=value strings. This ensures values are also sorted.
        sort($params);

        $baseString .= implode('%26', $params); // join with ampersand

        return $baseString;
    }

    /**
     * Return a copy of the given array with all keys and values rawurlencoded.
     *
     * @param array $array Array to normalize
     *
     * @return array Normalized array
     */
    protected function normalizeArray(array $array = [])
    {
        $normalizedArray = [];

        foreach ($array as $key => $value) {
            $key = rawurlencode(rawurldecode($key));

            if (is_array($value)) {
                $normalizedArray[$key] = $this->normalizeArray($value);
            } else {
                $normalizedArray[$key] = rawurlencode(rawurldecode($value));
            }
        }

        return $normalizedArray;
    }

    /**
     * Creates an array of rawurlencoded strings out of each array key/value pair
     * Handles multi-dimensional arrays recursively.
     *
     * @param array  $data         Array of parameters to convert.
     * @param string $prevKey      Optional Array key to append
     * @param bool   $isSequential Optional. Whether or not the data is a sequential array.
     * @param bool   $useParseStr  Optional. Whether or not multi-dimentional data is structured like PHP's parse_str.
     *
     * @return array a list of urlencoded key-value param strings.
     */
    protected function paramsFromData($data, $prevKey = '', $isSequential = false, $useParseStr = false): array
    {
        $params = [];

        foreach ($data as $key => $value) {
            if ($prevKey) {
                if ($isSequential) {
                    $key = $prevKey; // handle params like test=123&test=456
                } else {
                    $key = $prevKey . '[' . $key . ']'; // Handle multi-dimensional array
                }
            }
            if (is_array($value)) {
                $params = array_merge(
                    $params,
                    $this->paramsFromData($value, $key, ! $useParseStr && $this->isSequentialArray($value))
                );
            } else {
                $params[] = rawurlencode($key . '=' . $value); // join with equals sign
            }
        }

        return $params;
    }

    /**
     * Creates an array of rawurlencoded strings out of each array key/value pair
     * Handles multi-dimensional arrays recursively.
     *
     * @param array      $data         Array of parameters to convert.
     * @param array|null $queryParams  Array to extend. False by default.
     * @param string     $prevKey      Optional Array key to append
     * @param bool       $isSequential Optional. Whether or not the data is a sequential array.
     *
     * @return string rawurlencoded string version of data
     */
    protected function queryStringFromData($data, $queryParams = null, $prevKey = '', $isSequential = false)
    {
        return implode('%26', $this->paramsFromData($data)); // join with ampersand
    }

    /**
     * Gets whether or not the passed array is sequential.
     *
     * @param array $array The array to check.
     *
     * @return bool true if the array is sequential, false if it contains
     *              one or more associative or non-sequential keys.
     */
    protected function isSequentialArray(array $array): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($array);
        }

        $i = 0;
        foreach ($array as $key => $value) {
            if ($key !== $i++) {
                return false;
            }
        }

        return true;
    }
}
