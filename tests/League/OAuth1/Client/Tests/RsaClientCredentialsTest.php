<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\CredentialsException;
use League\OAuth1\Client\Credentials\RsaClientCredentials;
use PHPUnit\Framework\TestCase;

class RsaClientCredentialsTest extends TestCase
{
    /**
     * @throws \League\OAuth1\Client\Credentials\CredentialsException
     */
    public function testGetRsaPublicKey(): void
    {
        $credentials = new RsaClientCredentials();
        $credentials->setRsaPublicKey(__DIR__ . '/test_rsa_publickey.pem');

        $key = $credentials->getRsaPublicKey();
        static::assertIsResource($key);

        static::assertEquals($key, $credentials->getRsaPublicKey());
    }

    public function testGetRsaPublicKeyNotExists(): void
    {
        $this->expectException(CredentialsException::class);

        $credentials = new RsaClientCredentials();
        $credentials->setRsaPublicKey('fail');

        $credentials->getRsaPublicKey();
    }

    public function testGetRsaPublicKeyInvalid(): void
    {
        $this->expectException(CredentialsException::class);

        $credentials = new RsaClientCredentials();
        $credentials->setRsaPublicKey(__DIR__ . '/test_rsa_invalidkey.pem');

        $credentials->getRsaPublicKey();
    }

    /**
     * @throws \League\OAuth1\Client\Credentials\CredentialsException
     */
    public function testGetRsaPrivateKey(): void
    {
        $credentials = new RsaClientCredentials();
        $credentials->setRsaPrivateKey(__DIR__ . '/test_rsa_privatekey.pem');

        $key = $credentials->getRsaPrivateKey();
        static::assertIsResource($key);

        static::assertEquals($key, $credentials->getRsaPrivateKey());
    }

    public function testGetRsaPrivateKeyNotExists(): void
    {
        $this->expectException(CredentialsException::class);

        $credentials = new RsaClientCredentials();
        $credentials->setRsaPrivateKey('fail');

        $credentials->getRsaPrivateKey();
    }

    public function testGetRsaPrivateKeyInvalid(): void
    {
        $this->expectException(CredentialsException::class);

        $credentials = new RsaClientCredentials();
        $credentials->setRsaPrivateKey(__DIR__ . '/test_rsa_invalidkey.pem');

        $credentials->getRsaPrivateKey();
    }
}
