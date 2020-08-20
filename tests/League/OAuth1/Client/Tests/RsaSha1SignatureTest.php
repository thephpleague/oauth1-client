<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\RsaClientCredentials;
use League\OAuth1\Client\Signature\RsaSha1Signature;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class RsaSha1SignatureTest extends TestCase
{
    public function testMethod(): void
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());
        static::assertEquals('RSA-SHA1', $signature->method());
    }

    public function testSigningRequest(): void
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());

        $uri = 'http://www.example.com/?qux=corge';
        $parameters = ['foo' => 'bar', 'baz' => null];

        static::assertEquals(
            'ntJI+IGB/KqGB6jAHv1NRFi7Sn66in6qcd6tmnNDUja26yyJ01aqUpt3Xj2gkgeoJWlPhSLrUCuNp6H6MmCDqaU31mT9OidpUYZb96sGwL2OTmg3WzRu2rgi0KKsjat2g8tlo9Vuo9dQgNl64QeGEnl1TW+Srp9l5SIKfxJI9uU=',
            $signature->sign($uri, $parameters)
        );
    }

    public function testSigningRequestWithMultiDimensionalParams(): void
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());

        $uri = 'http://www.example.com/';
        $parameters = [
            'a' => [
                'b' => [
                    'c' => 'd',
                ],
                'e' => [
                    'f' => 'g',
                ],
            ],
            'h' => 'i',
            'empty' => '',
            'null' => null,
            'false' => false,
        ];

        static::assertEquals(
            'ZHwp6B90RpglvFLVNFm3jRXEokO5WK0kr0RR33Oe5gJglbwH61Jk15bsGZPxhHcYKu1xPL32KGzIeiNHKOlxw1lIuycbEo/NTE+plkfqLvNORCAIcU88hF2QCV/mm64JHOacUaThNBfEV6VPeZiEMd/MxMahshOj2MGixroRM7g=',
            $signature->sign($uri, $parameters)
        );
    }

    /**
     * @param array $args
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    protected function invokeQueryStringFromData(array $args)
    {
        $signature = new RsaSha1Signature(Mockery::mock(ClientCredentialsInterface::class));
        $refl = new ReflectionObject($signature);
        $method = $refl->getMethod('queryStringFromData');
        $method->setAccessible(true);

        return $method->invokeArgs($signature, [$args]);
    }

    protected function getClientCredentials(): RsaClientCredentials
    {
        $credentials = new RsaClientCredentials();
        $credentials->setRsaPublicKey(__DIR__ . '/test_rsa_publickey.pem');
        $credentials->setRsaPrivateKey(__DIR__ . '/test_rsa_privatekey.pem');

        return $credentials;
    }
}
