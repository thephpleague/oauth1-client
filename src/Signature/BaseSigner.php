<?php

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;

abstract class BaseSigner implements Signer
{
    /** @var ClientCredentials */
    protected $clientCredentials;

    /** @var BaseStringBuilder */
    protected $baseStringBuilder;

    public function __construct(ClientCredentials $clientCredentials, BaseStringBuilder $baseStringBuilder = null)
    {
        $this->clientCredentials  = $clientCredentials;
        $this->baseStringBuilder = $baseStringBuilder ?: new BaseStringBuilder();
    }
}
