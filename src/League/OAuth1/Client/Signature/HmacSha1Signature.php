<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;
use Symfony\Component\HttpFoundation\Request;

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
        $request = $this->createRequest($uri, $method);

        $baseString = $this->baseString($request, $parameters);

        return base64_encode($this->hash($baseString));
    }

    /**
     * Create a Symfony request for the given HTTP method on a URI.
     *
     * @param  string  $uri
     * @param  string  $method
     * @return Request
     */
    protected function createRequest($uri, $method = 'POST')
    {
        return Request::create($uri, $method);
    }

    /**
     * Generate a base string for a HMAC-SHA1 signature
     * based on the given request and any parameters.
     *
     * @param  Request  $request
     * @param  array    $request
     * @return string
     */
    protected function baseString(Request $request, array $parameters = array())
    {
        $baseString = rawurlencode($request->getMethod()).'&';
        $baseString .= rawurlencode($request->getSchemeAndHttpHost().$request->getPathInfo()).'&';

        $data = array();
        parse_str($request->getQueryString(), $query);
        foreach (array_merge($query, $parameters) as $key => $value) {
            $data[rawurlencode($key)] = rawurlencode($value);
        }

        ksort($data);
        array_walk($data, function(&$value, $key) {
            $value = $key.'='.$value;
        });
        $baseString .= rawurlencode(implode('&', $data));

        return $baseString;
    }

    /**
     * Hashes a string with the signature's key.
     *
     * @param  string  $string
     * @return string
     */
    protected function hash($string)
    {
        return hash_hmac('sha1', $string, $this->key(), true);
    }
}
