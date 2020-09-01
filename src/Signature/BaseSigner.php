<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;

abstract class BaseSigner implements Signer
{
    /** @var ClientCredentials */
    protected $clientCredentials;

    /** @var Credentials */
    protected $contextCredentials;

    /** @var BaseStringBuilder */
    protected $baseStringBuilder;

    protected function __construct(
        ClientCredentials $clientCredentials,
        Credentials $contextCredentials = null
    ) {
        $this->clientCredentials  = $clientCredentials;
        $this->contextCredentials = $contextCredentials;

        // @todo Allow custom resolution of parameter normalizer
        $this->baseStringBuilder = new BaseStringBuilder();
    }

    public static function withClientCredentials(ClientCredentials $clientCredentials): Signer
    {
        return new static($clientCredentials);
    }

    public static function withTemporaryCredentials(
        ClientCredentials $clientCredentials,
        Credentials $temporaryCredentials
    ): Signer {
        return new static($clientCredentials, $temporaryCredentials);
    }

    public static function withTokenCredentials(
        ClientCredentials $clientCredentials,
        Credentials $tokenCredentials
    ): Signer {
        return new static($clientCredentials, $tokenCredentials);
    }
}
