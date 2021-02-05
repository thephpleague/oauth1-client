<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Credentials\RsaKeyPair;
use Psr\Http\Message\RequestInterface;

class RsaSigner implements Signer
{
    private const METHOD = 'RSA-SHA1';

    /** @var ClientCredentials */
    private $clientCredentials;

    /** @var RsaKeyPair */
    private $keyPair;

    /** @var BaseStringBuilder */
    private $baseStringBuilder;

    public function __construct(
        ClientCredentials $clientCredentials,
        RsaKeyPair $keyPair,
        BaseStringBuilder $baseStringBuilder = null
    ) {
        $this->clientCredentials = $clientCredentials;
        $this->keyPair           = $keyPair;
        $this->baseStringBuilder = $baseStringBuilder ?: new BaseStringBuilder();
    }

    public function getMethod(): string
    {
        return self::METHOD;
    }

    public function sign(RequestInterface $request, array $oauthParameters, Credentials $contextCredentials = null): string
    {
        openssl_sign(
            $this->baseStringBuilder->forRequest($request, $oauthParameters),
            $signature,
            $this->keyPair->getPrivateKey()->getRaw()
        );

        return base64_encode($signature);
    }
}
