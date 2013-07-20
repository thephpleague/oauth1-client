<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Server\ServerInterface;
use League\OAuth1\Client\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSignature implements SignatureInterface
{
    /**
     * Sign the given request for the client.
     *
     * @param  Request          $request
     * @param  ServerInterface  $server
     * @param  TokenInterface   $token
     * @return string
     */
    protected function key(ServerInterface $server, TokenInterface $token = null)
    {
        $key = rawurlencode($server->getClientSecret()).'&';

        if ($token !== null) {
            $key .= rawurlencode($token->getSecret());
        }

        return $key;
    }
}