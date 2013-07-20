<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;
use Symfony\Component\HttpFoundation\Request;

class HmacSha1Signature extends Signature implements SignatureInterface
{
    /**
     * Get the OAuth signature method.
     *
     * @return string
     */
    public function method()
    {
        return 'HMAC-SHA1';
    }

    /**
     * Sign the given request for the client.
     *
     * @param  string  $uri
     * @param  array   $credentials
     * @param  string  $method
     * @return string
     * @see    OAuth 1.0 RFC 5849 Section 3.4.2
     */
    public function sign($uri, array $parameters = array(), $method = 'POST')
    {
        $request = $this->createRequest($uri, $method);

        $baseString = $this->baseString($request, $parameters);

        return base64_encode($this->hash($baseString));
    }

    protected function createRequest($uri, $method = 'POST')
    {
        return Request::create($uri, $method);
    }

    protected function baseString(Request $request, array $parameters = array())
    {
        $baseString = rawurlencode($request->getMethod()).'&';
        $baseString .= rawurlencode($request->getSchemeAndHttpHost()).'&';

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

    protected function hash($string)
    {
        return hash_hmac('sha1', $string, $this->key(), true);
    }
}