<?php

namespace League\OAuth1\Client\Signature;

use Guzzle\Http\Url;

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
        $baseString = $this->baseString($uri, $method, $parameters);

        return base64_encode($this->hash($baseString));
    }

    /**
     * Generate a base string for a HMAC-SHA1 signature
     * based on the given a url, method, and any parameters.
     *
     * @param Url    $url
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    protected function baseString($uri, $method = 'POST', array $parameters = array())
    {
        $baseString = rawurlencode($method).'&';

        $url = parse_url($uri);

        $schemeHostPath = "{$url['scheme']}://{$url['host']}{$url['path']}";

        $baseString .= rawurlencode($schemeHostPath).'&';

        $query = array();
        if (isset($url['query'])) {
            parse_str($url['query'], $query);
        }
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
