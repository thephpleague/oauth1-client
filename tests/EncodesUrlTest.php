<?php

namespace League\OAuth1\Client\Tests;

use League\OAuth1\Client\Signature\EncodesUrl;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class EncodesUrlClass
{
    use EncodesUrl;
}

class EncodesUrlTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function testParamsFromArray()
    {
        $array = ['a' => 'b'];
        $res = $this->invokeParamsFromData($array);

        $this->assertSame(
            ['a%3Db'],
            $res
        );
    }

    public function testParamsFromIndexedArray()
    {
        $array = ['a', 'b'];
        $res = $this->invokeParamsFromData($array);

        $this->assertSame(
            ['0%3Da', '1%3Db'],
            $res
        );
    }

    public function testParamsFromMultiValueArray()
    {
        $array = ['test' => ['789', '1234']];
        $res = $this->invokeParamsFromData($array);

        // Ensure no indices are added to param names.
        $this->assertSame(
            ['test%3D789', 'test%3D1234'],
            $res
        );
    }

    public function testBaseStringFromMultiValueParamsArray()
    {
        $uri = $this->getMockUri();

        $params = ['test' => ['789', '1234']];
        $res = $this->invokeBaseString($uri, 'GET', $params);

        // Ensure duplicate params are sorted by string value and no indices
        // are added to param names.
        $this->assertSame(
            'GET&http%3A%2F%2Fwww.example.com&test%3D1234%26test%3D789',
            $res
        );
    }

    public function testBaseStringFromMultiValueQueryString()
    {
        $uri = $this->getMockUri('&test[0]=789&test[1]=1234');

        $res = $this->invokeBaseString($uri, 'GET', []);

        $this->assertSame(
            'GET&http%3A%2F%2Fwww.example.com&test%5B0%5D%3D789%26test%5B1%5D%3D1234',
            $res
        );
    }

    public function testParamsFromMultiDimensionalArray()
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

        // Convert to query string.
        $res = $this->invokeParamsFromData($array);

        $this->assertSame(
            [
                'a%5Bb%5D%5Bc%5D%3Dd',
                'a%5Be%5D%5Bf%5D%3Dg',
                'h%3Di',
                'empty%3D',
                'null%3D',
                'false%3D',
            ],
            $res
        );

        // Reverse engineer the string.
        $res = array_map('urldecode', $res);

        $this->assertSame(
            [
                'a[b][c]=d',
                'a[e][f]=g',
                'h=i',
                'empty=',
                'null=',
                'false=',
            ],
            $res
        );

        // Finally, parse the string back to an array.
        parse_str(implode('&', $res), $original_array);

        // And ensure it matches the orignal array (approximately).
        $this->assertSame(
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
            $original_array
        );
    }

    protected function invokeParamsFromData(array $args)
    {
        $signature = new EncodesUrlClass(m::mock('League\OAuth1\Client\Credentials\ClientCredentialsInterface'));
        $refl = new \ReflectionObject($signature);
        $method = $refl->getMethod('paramsFromData');
        $method->setAccessible(true);

        return $method->invokeArgs($signature, [$args]);
    }

    protected function invokeBaseString(...$args)
    {
        $signature = new EncodesUrlClass(m::mock('League\OAuth1\Client\Credentials\ClientCredentialsInterface'));
        $refl = new \ReflectionObject($signature);
        $method = $refl->getMethod('baseString');
        $method->setAccessible(true);

        return $method->invokeArgs($signature, $args);
    }

    protected function getMockUri(string $queryString = '')
    {
        $uri = m::mock('Psr\Http\Message\UriInterface');
        $uri->shouldReceive([
            'getScheme' => 'http',
            'getHost' => 'www.example.com',
            'getPort' => null,
            'getPath' => '',
            'getQuery' => $queryString,
        ]);

        return $uri;
    }
}
