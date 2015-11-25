<?php namespace League\OAuth1\Client\Tests;
/**
 * Part of the Sentry package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Sentry
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use League\OAuth1\Client\Signature\HmacSha1Signature;
use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * Class HmacSha1SignatureTest
 *
 * This class validates assertions about the HMAC SHA1 signatures. Note that wherever you see some base64 encoded
 * signature that is used as an assertion, you can calculate the actual signature by using this method:
 *
 * base64_encode(hash_hmac('sha1', $base, 'clientsecret&', true));
 *
 * The 'clientsecret&' above is embedded in the mock object in this class.
 *
 * Note that for convenience, the expected pre-signed string is included in each test so that you can see what the
 * expectation is prior to signing if you are making modifications to this test or the HMAC-SHA1 implementation.
 *
 * @package League\OAuth1\Client\Tests
 */
class HmacSha1SignatureTest extends PHPUnit_Framework_TestCase
{
    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }

    public function testASingleQueryIsParsed()
    {
        $query = 'a=b';
        $res = $this->invokeParseQuery(array($query));

        $this->assertSame(
            array('a' => 'b'),
            $res
        );
    }

    public function testWhenAQueryKeyHasNoValue()
    {
        $query = 'a&c=d';
        $res = $this->invokeParseQuery(array($query));

        $this->assertSame(
            array('a' => '', 'c' => 'd'),
            $res
        );
    }

    public function testWhenAQueryValueHasNoKey()
    {
        $query = '=a&c=d';
        $res = $this->invokeParseQuery(array($query));

        $this->assertSame(
            array('c' => 'd'),
            $res
        );
    }

    public function testMultipleQueryParamsParsed()
    {
        $query = 'a[]=1&a[]=2&b=c';
        $res = $this->invokeParseQuery(array($query));

        $this->assertSame(
            array('a[]' => array('1', '2'), 'b' => 'c'),
            $res
        );
    }

    public function testMultipleQueryParamsParsedWhenThereAreManyArrays()
    {
        $query = 'a[]=1&a[]=2&b[]=c&b[]=d';
        $res = $this->invokeParseQuery(array($query));

        $this->assertSame(
            array('a[]' => array('1', '2'), 'b[]' => array('c', 'd')),
            $res
        );
    }

    public function testAssociativeArraysAreParsed()
    {
        $query = 'a[hello]=1&a[hello]=2&b[hi][wut]=c&b[hi][yay]=d';
        $res = $this->invokeParseQuery(array($query));

        $this->assertSame(
            array('a[hello]' => array('1', '2'), 'b[hi][wut]' => 'c', 'b[hi][yay]' => 'd'),
            $res
        );
    }

    public function testSigningRequest()
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

        $uri = 'http://www.example.com/?qux=corge';
        $parameters = array('foo' => 'bar', 'baz' => null);

        // Base string: POST&http%3A%2F%2Fwww.example.com%2F&baz%3D%26foo%3Dbar%26qux%3Dcorge

        $this->assertEquals('A3Y7C1SUHXR1EBYIUlT3d6QT1cQ=', $signature->sign($uri, $parameters));
    }

    public function testSigningRequestWithArrayParams()
    {
        $signature = new HmacSha1Signature($this->getMockClientCredentials());

        $uri = 'http://www.example.com/?qux[][f]=corge&qux[][f]=egroc';
        $parameters = array('foo' => 'bar', 'baz' => null);

        // Base string: POST&http%3A%2F%2Fwww.example.com%2F&baz%3D%26foo%3Dbar%26qux%255B%255D%255Bf%255D%3Dcorge%26qux%255B%255D%255Bf%255D%3Degroc

        $this->assertEquals('JXp+kndmPKBtHoLiXBt7CCJAplw=', $signature->sign($uri, $parameters));
    }

    protected function invokeParseQuery(array $args)
    {
        $signature = new HmacSha1Signature(m::mock('League\OAuth1\Client\Credentials\ClientCredentialsInterface'));
        $refl = new \ReflectionObject($signature);
        $method = $refl->getMethod('parseQuery');
        $method->setAccessible(true);
        return $method->invokeArgs($signature, $args);
    }

    protected function getMockClientCredentials()
    {
        $clientCredentials = m::mock('League\OAuth1\Client\Credentials\ClientCredentialsInterface');
        $clientCredentials->shouldReceive('getSecret')->andReturn('clientsecret');
        return $clientCredentials;
    }
}
