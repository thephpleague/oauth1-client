<?php

namespace League\OAuth1\Client\Signature;

class PlainTextSignature extends AbstractSignature implements SignatureInterface
{
    // public function key(ClientInterface $client, TokenInterface $token);

    /**
     * Sign the given request for the client.
     *
     * @param  ?
     * @return string
     */
    public function sign(RequestInterface $request, ClientInterface $client, TokenInterface $token = null)
    {
        return $this->key($client, $token);
    }

    /**
     * Verify the given signature against a request for the client.
     *
     * @param  ?
     * @return string
     */
    public function verify($signature, RequestInterface $request, ClientInterface $client, TokenInterface $token = null)
    {
        return $signature === $this->key($client, $secret);
    }
}