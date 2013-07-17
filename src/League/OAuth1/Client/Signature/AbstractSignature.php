<?php

namespace Leagure\OAuth1\Client\Signature;

class AbstractSignature implements SignatureInterface
{
    public function key(ClientInterface $client, TokenInterface $token = null)
    {
        $key = urlencode($client->getSecret()).'&';

        if ($token) {
            $key .= urlencode($token->getSecret());
        }

        return $key;
    }
}