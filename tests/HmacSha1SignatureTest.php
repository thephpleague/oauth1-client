<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Signature\HmacSha1Signature;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use ReflectionException;
use ReflectionObject;

class HmacSha1SignatureTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    /** @test */
    public function it_should_sign_a_request_correctly(): void
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

        $uri = 'http://www.example.com/?qux=corge';

        $parameters = ['foo' => 'bar', 'baz' => null];

        self::assertEquals('A3Y7C1SUHXR1EBYIUlT3d6QT1cQ=', $signature->sign($uri, $parameters));
    }

    /** @test */
    public function it_should_create_a_query_string_from_an_array(): void
    {
        $array = ['a' => 'b'];

        $queryString = $this->invokeQueryStringFromData($array);

        self::assertSame(
            'a%3Db',
            $queryString
        );
    }

    /** @test */
    public function it_should_create_a_query_string_from_a_dictionary(): void
    {
        $array = ['a', 'b'];

        $queryString = $this->invokeQueryStringFromData($array);

        self::assertSame(
            '0%3Da%261%3Db',
            $queryString
        );
    }

    /** @test */
    public function it_should_creat_a_query_string_from_a_multidimensional_array(): void
    {
        $array = [
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

        $queryString = $this->invokeQueryStringFromData($array);

        self::assertSame(
            'a%5Bb%5D%5Bc%5D%3Dd%26a%5Be%5D%5Bf%5D%3Dg%26h%3Di%26empty%3D%26null%3D%26false%3D',
            $queryString
        );

        // Reverse engineer the string.
        $queryString = urldecode($queryString);

        self::assertSame(
            'a[b][c]=d&a[e][f]=g&h=i&empty=&null=&false=',
            $queryString
        );
    }

    /** @test */
    public function it_should_sign_a_request_with_multidimensional_params(): void
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

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

        self::assertEquals('ZUxiJKugeEplaZm9e4hshN0I70U=', $signature->sign($uri, $parameters));
    }

    /**
     * @throws ReflectionException If the reflected property could not be made accessible
     */
    private function invokeQueryStringFromData(array $args)
    {
        $signature = new HmacSha1Signature(m::mock(ClientCredentialsInterface::class));

        $reflectionObject = new ReflectionObject($signature);
        $method = $reflectionObject->getMethod('queryStringFromData');
        $method->setAccessible(true);

        return $method->invokeArgs($signature, [$args]);
    }

    /**
     * @return ClientCredentialsInterface|m\MockInterface
     */
    private function getMockClientCredentials(): ClientCredentialsInterface
    {
        $clientCredentials = m::mock(ClientCredentialsInterface::class);
        $clientCredentials->shouldReceive('getSecret')->andReturn('clientsecret');
        return $clientCredentials;
    }
}
