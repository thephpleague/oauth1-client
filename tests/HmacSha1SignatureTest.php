<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Signature\HmacSha1Signature;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
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

        $actual = $signature->sign('http://www.example.com/?qux=corge', [
            'foo' => 'bar',
            'baz' => null,
        ]);

        static::assertEquals('A3Y7C1SUHXR1EBYIUlT3d6QT1cQ=', $actual);
    }

    /** @test */
    public function it_should_create_a_query_string_from_an_array(): void
    {
        $queryString = $this->invokeQueryStringFromData(['a' => 'b']);

        static::assertSame('a%3Db', $queryString);
    }

    /** @test */
    public function it_should_create_a_query_string_from_a_dictionary(): void
    {
        $queryString = $this->invokeQueryStringFromData(['a', 'b']);

        static::assertSame('0%3Da%261%3Db', $queryString);
    }

    /** @test */
    public function it_should_creat_a_query_string_from_a_multidimensional_array(): void
    {
        $queryString = $this->invokeQueryStringFromData([
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
            $queryString
        );

        // Reverse engineer the string.
        $queryString = urldecode($queryString);

        static::assertSame(
            'a[b][c]=d&a[e][f]=g&h=i&empty=&null=&false=',
            $queryString
        );
    }

    /** @test */
    public function it_should_sign_a_request_with_multidimensional_params(): void
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

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

        static::assertEquals('ZUxiJKugeEplaZm9e4hshN0I70U=', $actual);
    }

    private function invokeQueryStringFromData(array $args)
    {
        $signature = new HmacSha1Signature(m::mock(ClientCredentialsInterface::class));

        $reflectionObject = new ReflectionObject($signature);
        $method = $reflectionObject->getMethod('queryStringFromData');
        $method->setAccessible(true);

        return $method->invokeArgs($signature, [$args]);
    }

    /**
     * @return \League\OAuth1\Client\Credentials\ClientCredentialsInterface|m\MockInterface
     */
    private function getMockClientCredentials(): ClientCredentialsInterface
    {
        $clientCredentials = m::mock(ClientCredentialsInterface::class);
        $clientCredentials->shouldReceive('getSecret')->andReturn('clientsecret');

        return $clientCredentials;
    }
}
