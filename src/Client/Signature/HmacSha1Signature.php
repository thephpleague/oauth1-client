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
        $url = $this->createUrl($uri);

        $baseString = $this->baseString($url, $method, $parameters);

        return base64_encode($this->hash($baseString));
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
        return Url::factory($uri);
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
    protected function baseString(Url $url, $method = 'POST', array $parameters = array())
    {
        $baseString = rawurlencode($method).'&';

        $schemeHostPath = Url::buildUrl(array(
           'scheme' => $url->getScheme(),
           'host' => $url->getHost(),
           'path' => $url->getPath(),
        ));

        $baseString .= rawurlencode($schemeHostPath).'&';

        $data = array();
        parse_str($url->getQuery(), $query);
        foreach (array_merge($query, $parameters) as $key => $value) {
            $data[rawurlencode($key)] = rawurlencode($value);
        }

        ksort($data);
        array_walk($data, function (&$value, $key) {
            $value = $key.'='.$value;
        });
        $baseString .= rawurlencode(implode('&', $data));

        return $baseString;
    }


    /**
     * Parses a query string into components including properly parsing queries that have array in them like a[]=1
     * or a[hello]=1.
     *
     * @param string $query The query string to parse into an associative array.
     *
     * @return array The parsed query into a single-level associative array.
     */
    protected function parseQuery($query)
    {
        $parsed = array();
        $parts = explode('&', $query);

        foreach ($parts as $part) {
            $equalsPos = strpos($part, '=');

            if ($equalsPos === false) {
                $key   = urldecode($part);
                $value = '';
            } else {
                $key   = urldecode(substr($part, 0, $equalsPos));
                $value = urldecode(substr($part, $equalsPos + 1));
            }

            //Example where the key for 'c' is '': a=b&=c
            if ($key == '') {
                continue;
            }

            if (!isset($parsed[$key])) {
                $parsed[$key] = $value;
            } else {
                //ensure this is an array since we need to store multiple values
                if (!is_array($parsed[$key])) {
                    $parsed[$key] = array($parsed[$key]);
                }

                $parsed[$key][] = $value;
            }
        }

        return $parsed;
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
