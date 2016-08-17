<?php

namespace League\OAuth1\Client\Signature;

use Http\Discovery\UriFactoryDiscovery;
use Psr\Http\Message\UriInterface;

class HmacSha1Signature extends Signature implements SignatureInterface
{
    /**
     * {@inheritDoc}
     */
    public function method()
    {
        return 'HMAC-SHA1';
    }

    /**
     * {@inheritDoc}
     */
    public function sign($uri, array $parameters = array(), $method = 'POST')
    {
        $url = $this->createUrl($uri);

        $baseString = $this->baseString($url, $method, $parameters);

        return base64_encode($this->hash($baseString));
    }

    /**
     * Create a PSR URI for the given string URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     */
    protected function createUrl($uri)
    {
        return UriFactoryDiscovery::find()->createUri($uri);
    }

    /**
     * Generate a base string for a HMAC-SHA1 signature
     * based on the given a url, method, and any parameters.
     *
     * @param UriInterface $url
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    protected function baseString(UriInterface $url, $method = 'POST', array $parameters = array())
    {
        $baseString = rawurlencode($method).'&';
        $schemeHostPath = $this->createUrl(sprintf('%s://%s%s', $url->getScheme(), $url->getHost(), $url->getPath()));

        $baseString .= rawurlencode($schemeHostPath).'&';

        $data = array();
        parse_str($url->getQuery(), $query);
        $data = array_merge($query, $parameters);

        // normalize data key/values
        array_walk_recursive($data, function (&$key, &$value) {
            $key   = rawurlencode(rawurldecode($key));
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
     * @param  array  $data        Array of parameters to convert.
     * @param  array  $queryParams Array to extend. False by default.
     * @param  string $prevKey     Optional Array key to append
     *
     * @return string              rawurlencoded string version of data
     */
    protected function queryStringFromData($data, $queryParams = false, $prevKey = '')
    {
        if ($initial = (false === $queryParams)) {
            $queryParams = array();
        }

        foreach ($data as $key => $value) {
            if ($prevKey) {
                $key = $prevKey.'['.$key.']'; // Handle multi-dimensional array
            }
            if (is_array($value)) {
                $queryParams = $this->queryStringFromData($value, $queryParams, $key);
            } else {
                $queryParams[] = rawurlencode($key.'='.$value); // join with equals sign
            }
        }

        if ($initial) {
            return implode('%26', $queryParams); // join with ampersand
        }

        return $queryParams;
    }

    /**
     * Hashes a string with the signature's key.
     *
     * @param string $string
     *
     * @return string
     */
    protected function hash($string)
    {
        return hash_hmac('sha1', $string, $this->key(), true);
    }
}
