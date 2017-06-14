<?php

namespace League\OAuth1\Client\Signature;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use League\OAuth1\Client\Signature\Signature;
use League\OAuth1\Client\Signature\SignatureInterface;

class RsaSha1Signature extends Signature implements SignatureInterface
{
    /**
     * {@inheritdoc}
     */
    public function method()
    {
        return 'RSA-SHA1';
    }

    /**
     * {@inheritdoc}
     */
    public function sign($uri, array $parameters = array(), $method = 'POST')
    {
        $url = $this->createUrl($uri);
        $baseString = $this->baseString($url, $method, $parameters);

        $privateKey = $this->clientCredentials->getRsaPrivateKey();

        openssl_sign($baseString, $signature, $privateKey);

        return base64_encode($signature);
    }

    /**
     * Create a Guzzle url for the given URI.
     *
     * @param string $uri
     *
     * @return Url
     */
    protected function createUrl($uri)
    {
        return Psr7\uri_for($uri);
    }

    /**
     * Generate a base string for a RSA-SHA1 signature
     * based on the given a url, method, and any parameters.
     *
     * @param Url    $url
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    protected function baseString(Uri $url, $method = 'POST', array $parameters = array())
    {
        $baseString = rawurlencode($method).'&';

        $schemeHostPath = Uri::fromParts(array(
           'scheme' => $url->getScheme(),
           'host' => $url->getHost(),
           'path' => $url->getPath(),
        ));

        $baseString .= rawurlencode($schemeHostPath).'&';

        $data = array();
        parse_str($url->getQuery(), $query);
        $data = array_merge($query, $parameters);

        // normalize data key/values
        array_walk_recursive($data, function (&$key, &$value) {
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
     * @param array  $data        Array of parameters to convert.
     * @param array  $queryParams Array to extend. False by default.
     * @param string $prevKey     Optional Array key to append
     *
     * @return string rawurlencoded string version of data
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
}
