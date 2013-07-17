<?php

namespace League\OAuth1\Client\Signature;

class HmacSha1Signature extends AbstractSignature implements SignatureInterface
{
    /**
     * Sign the given request for the client.
     *
     * @param  ?
     * @return string
     */
    public function sign(RequestInterface $request, ClientInterface $client, TokenInterface $token = null)
    {
        $key = $this->key($client, $token);

        $string = $request->getMethod().'&';
        $string .= $request->getSchemeAndHttpHost().'&';
        $string
    }

    /**
     * Verify the given signature against a request for the client.
     *
     * @param  ?
     * @return string
     */
    public function verify($signature, RequestInterface $request, ClientInterface $client, TokenInterface $token = null)
    {

    }
}