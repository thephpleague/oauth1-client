<?php

namespace League\OAuth1\Client\Signature;

interface SignatureInterface
{
    public function key(ClientInterface $client, TokenInterface $token = null);

    /**
     * Sign the given request for the client.
     *
     * @param  ?
     * @return string
     */
    public function sign(RequestInterface $request, ClientInterface $client, TokenInterface $token = null);

    /**
     * Verify the given signature against a request for the client.
     *
     * @param  ?
     * @return string
     */
    public function verify($signature, RequestInterface $request, ClientInterface $client, TokenInterface $token = null);
}