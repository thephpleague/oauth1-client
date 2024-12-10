<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\RsaClientCredentials;
use League\OAuth1\Client\Signature\RsaSha1Signature;
use Mockery;
use PHPUnit\Framework\TestCase;

class RsaSha1SignatureTest extends TestCase
{
    public function testMethod()
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());
        $this->assertEquals('RSA-SHA1', $signature->method());
    }

    public function testSigningRequest()
    {
        $signature = new RsaSha1Signature($this->getClientCredentials());

        $uri = 'http://www.example.com/?qux=corge';
        $parameters = ['foo' => 'bar', 'baz' => null];

        $this->assertEquals('h8vpV4CYnLwss+rWicKE4sY6AiW2+DT6Fe7qB8jA7LSLhX5jvLEeX1D8E2ynSePSksAY48j+OSLu9vo5juS2duwNK8UA2Rtnnvuj6UFxpx70dpjHAsQg6EbycGptL/SChDkxfpG8LhuwX1FlFa+H0jLYXI5Dy8j90g51GRJbj48=', $signature->sign($uri, $parameters));
    }

    public function testSigningRequestWithMultiDimensionalParams()
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

        $this->assertEquals('X9EkmOEbA5CoF2Hicf3ciAumpp1zkKxnVZkh/mEwWyF2DDcrfou9XF11WvbBu3G4loJGeX4GY1FsIrQpsjEILbn0e7Alyii/x8VA9mBwdqMhQVl49jF0pdowocc03M04cAbAOMNObT7tMmDs+YTFgRxEGCiUkq9AizP1cW3+eBo=', $signature->sign($uri, $parameters));
    }

    protected function getClientCredentials()
    {
        $credentials = new RsaClientCredentials();
        $credentials->setRsaPublicKey(__DIR__ . '/test_rsa_publickey.pem');
        $credentials->setRsaPrivateKey(__DIR__ . '/test_rsa_privatekey.pem');

        return $credentials;
    }
}
