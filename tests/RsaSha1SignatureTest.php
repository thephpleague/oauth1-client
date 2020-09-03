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
    public function testMethod()
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());

        static::assertEquals('RSA-SHA1', $signature->method());
    }

    public function testSigningRequest()
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());

        $actual = $signature->sign('http://www.example.com/?qux=corge', [
            'foo' => 'bar',
            'baz' => null,
        ]);

        static::assertEquals(
            'h8vpV4CYnLwss+rWicKE4sY6AiW2+DT6Fe7qB8jA7LSLhX5jvLEeX1D8E2ynSePSksAY48j+OSLu9vo5juS2duwNK8UA2Rtnnvuj6UFxpx70dpjHAsQg6EbycGptL/SChDkxfpG8LhuwX1FlFa+H0jLYXI5Dy8j90g51GRJbj48=',
            $actual
        );
    }

    public function testQueryStringFromArray()
    {
        $actual = $this->invokeQueryStringFromData(['a' => 'b']);

        static::assertSame('a%3Db', $actual);
    }

    public function testQueryStringFromIndexedArray()
    {
        $actual = $this->invokeQueryStringFromData(['a', 'b']);

        static::assertSame('0%3Da%261%3Db', $actual);
    }

    public function testQueryStringFromMultiDimensionalArray()
    {
        // Convert to query string.
        $actual = $this->invokeQueryStringFromData([
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
        ]);

        static::assertSame(
            'a%5Bb%5D%5Bc%5D%3Dd%26a%5Be%5D%5Bf%5D%3Dg%26h%3Di%26empty%3D%26null%3D%26false%3D',
            $actual
        );

        // Reverse engineer the string.
        $actual = urldecode($actual);

        static::assertSame('a[b][c]=d&a[e][f]=g&h=i&empty=&null=&false=', $actual);

        // Finally, parse the string back to an array.
        parse_str($actual, $original);

        // And ensure it matches the orignal array (approximately).
        static::assertSame(
            [
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
                'null' => '', // null value gets lost in string translation
                'false' => '', // false value gets lost in string translation
            ],
            $original
        );
    }

    public function testSigningRequestWithMultiDimensionalParams()
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());

        $actual = $signature->sign('http://www.example.com/', [
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
        ]);

        static::assertEquals(
            'X9EkmOEbA5CoF2Hicf3ciAumpp1zkKxnVZkh/mEwWyF2DDcrfou9XF11WvbBu3G4loJGeX4GY1FsIrQpsjEILbn0e7Alyii/x8VA9mBwdqMhQVl49jF0pdowocc03M04cAbAOMNObT7tMmDs+YTFgRxEGCiUkq9AizP1cW3+eBo=',
            $actual
        );
    }

    protected function invokeQueryStringFromData(array $args)
    {
        $signature = new RsaSha1Signature(Mockery::mock(ClientCredentialsInterface::class));

        $method = (new ReflectionObject($signature))
            ->getMethod('queryStringFromData');
        $method->setAccessible(true);

        return $method->invokeArgs($signature, [$args]);
    }

    protected function getClientCredentials(): RsaClientCredentials
    {
        return (new RsaClientCredentials)
            ->setRsaPublicKey(__DIR__.'/test_rsa_publickey.pem')
            ->setRsaPrivateKey(__DIR__.'/test_rsa_privatekey.pem');
    }
}
