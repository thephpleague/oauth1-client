<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\CredentialsException;
use League\OAuth1\Client\Credentials\RsaClientCredentials;
use PHPUnit\Framework\TestCase;

class RsaClientCredentialsTest extends TestCase
{
    public function testGetRsaPublicKey()
    {
        $credentials = new RsaClientCredentials();
        $credentials->setRsaPublicKey(__DIR__.'/test_rsa_publickey.pem');

        $key = $credentials->getRsaPublicKey();

        static::assertTrue(is_resource($key));
        static::assertEquals($key, $credentials->getRsaPublicKey());
    }

    public function testGetRsaPublicKeyNotExists()
    {
        $this->expectException(CredentialsException::class);

        (new RsaClientCredentials)
            ->setRsaPublicKey('fail')
            ->getRsaPublicKey();
    }

    public function testGetRsaPublicKeyInvalid()
    {
        $this->expectException(CredentialsException::class);

        (new RsaClientCredentials)
            ->setRsaPublicKey(__DIR__.'/test_rsa_invalidkey.pem')
            ->getRsaPublicKey();
    }

    public function testGetRsaPrivateKey()
    {
        $credentials = new RsaClientCredentials();
        $credentials->setRsaPrivateKey(__DIR__.'/test_rsa_privatekey.pem');

        $key = $credentials->getRsaPrivateKey();

        static::assertTrue(is_resource($key));
        static::assertEquals($key, $credentials->getRsaPrivateKey());
    }

    public function testGetRsaPrivateKeyNotExists()
    {
        $this->expectException(CredentialsException::class);

        (new RsaClientCredentials)
            ->setRsaPrivateKey('fail')
            ->getRsaPrivateKey();
    }

    public function testGetRsaPrivateKeyInvalid()
    {
        $this->expectException(CredentialsException::class);

        (new RsaClientCredentials)
            ->setRsaPrivateKey(__DIR__.'/test_rsa_invalidkey.pem')
            ->getRsaPrivateKey();
    }
}
