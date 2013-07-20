<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Server\ServerInterface;
use League\OAuth1\Client\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class HmacSha1Signature extends AbstractSignature implements SignatureInterface
{
    /**
     * Sign the given request for the client.
     *
     * @param  Request          $request
     * @param  ServerInterface  $server
     * @param  TokenInterface   $token
     * @return string
     */
    public function sign(Request $request, ServerInterface $server, TokenInterface $token = null)
    {
        $signature = $request->getMethod().'&';
        $signature .= rawurlencode($request->getSchemeAndHttpHost()).'&';
        $signature .= rawurlencode($request->getQueryString()).'&';

        $hashed = hash_hmac('sha1', $signature, $this->key(), true);

        return base64_encode($hashed);
    }
}