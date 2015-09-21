<?php
/**
 * This file is part of the league/oauth1-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */

namespace League\OAuth1\Client\Test\Server;

use League\OAuth1\Client\Credentials\ClientCredentials;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class ServerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Close mockery.
     */
    public function tearDown()
    {
        m::close();
    }

    public function testCreatingWithArray()
    {
        $server = new Fake($this->getMockClientCredentials());

        $credentials = $server->getClientCredentials();
        $this->assertInstanceOf('League\OAuth1\Client\Credentials\ClientCredentials', $credentials);
        $this->assertEquals('myidentifier', $credentials->getIdentifier());
        $this->assertEquals('mysecret', $credentials->getSecret());
        $this->assertEquals('http://app.dev/', $credentials->getCallbackUri());
    }

    public function testCreatingWithObject()
    {
        $credentials = new ClientCredentials('myidentifier', 'mysecret', 'http://app.dev/');

        $server = new Fake($credentials);

        $this->assertEquals($credentials, $server->getClientCredentials());
    }

    /**
     * @expectedException InvalidArgumentException
     **/
    public function testCreatingWithInvalidInput()
    {
        $server = new Fake(uniqid());
    }

    public function testGettingTemporaryCredentials()
    {
        $server = m::mock('League\OAuth1\Client\Test\Server\Fake[createHttpClient]', array($this->getMockClientCredentials()));

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock('stdClass'));

        $me = $this;
        $client->shouldReceive('post')->with('http://www.example.com/temporary', m::on(function ($headers) use ($me) {
            $me->assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_callback="'.preg_quote('http%3A%2F%2Fapp.dev%2F', '/').'", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            $me->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }))->once()->andReturn($request = m::mock('stdClass'));

        $request->shouldReceive('send')->once()->andReturn($response = m::mock('stdClass'));
        $response->shouldReceive('getBody')->andReturn('oauth_token=temporarycredentialsidentifier&oauth_token_secret=temporarycredentialssecret&oauth_callback_confirmed=true');

        $credentials = $server->getTemporaryCredentials();
        $this->assertInstanceOf('League\OAuth1\Client\Credentials\TemporaryCredentials', $credentials);
        $this->assertEquals('temporarycredentialsidentifier', $credentials->getIdentifier());
        $this->assertEquals('temporarycredentialssecret', $credentials->getSecret());
    }

    public function testGettingAuthorizationUrl()
    {
        $server = new Fake($this->getMockClientCredentials());

        $expected = 'http://www.example.com/authorize?oauth_token=foo';

        $this->assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials');
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        $this->assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGettingTokenCredentialsFailsWithManInTheMiddle()
    {
        $server = new Fake($this->getMockClientCredentials());

        $credentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials');
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        $server->getTokenCredentials($credentials, 'bar', 'verifier');
    }

    public function testGettingTokenCredentials()
    {
        $server = m::mock('League\OAuth1\Client\Test\Server\Fake[createHttpClient]', array($this->getMockClientCredentials()));

        $temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials');
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('temporarycredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('temporarycredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock('stdClass'));

        $me = $this;
        $client->shouldReceive('post')->with('http://www.example.com/token', m::on(function ($headers) use ($me) {
            $me->assertTrue(isset($headers['Authorization']));
            $me->assertFalse(isset($headers['User-Agent']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            $me->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }), array('oauth_verifier' => 'myverifiercode'))->once()->andReturn($request = m::mock('stdClass'));

        $request->shouldReceive('send')->once()->andReturn($response = m::mock('stdClass'));
        $response->shouldReceive('getBody')->andReturn('oauth_token=tokencredentialsidentifier&oauth_token_secret=tokencredentialssecret');

        $credentials = $server->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');
        $this->assertInstanceOf('League\OAuth1\Client\Credentials\TokenCredentials', $credentials);
        $this->assertEquals('tokencredentialsidentifier', $credentials->getIdentifier());
        $this->assertEquals('tokencredentialssecret', $credentials->getSecret());
    }

    public function testGettingTokenCredentialsWithUserAgent()
    {
        $userAgent = 'FooBar';
        $server = m::mock('League\OAuth1\Client\Test\Server\Fake[createHttpClient]', array($this->getMockClientCredentials()));

        $temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials');
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('temporarycredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('temporarycredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock('stdClass'));

        $me = $this;
        $client->shouldReceive('post')->with('http://www.example.com/token', m::on(function ($headers) use ($me, $userAgent) {
            $me->assertTrue(isset($headers['Authorization']));
            $me->assertTrue(isset($headers['User-Agent']));
            $me->assertEquals($userAgent, $headers['User-Agent']);

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            $me->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }), array('oauth_verifier' => 'myverifiercode'))->once()->andReturn($request = m::mock('stdClass'));

        $request->shouldReceive('send')->once()->andReturn($response = m::mock('stdClass'));
        $response->shouldReceive('getBody')->andReturn('oauth_token=tokencredentialsidentifier&oauth_token_secret=tokencredentialssecret');

        $credentials = $server->setUserAgent($userAgent)->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');
        $this->assertInstanceOf('League\OAuth1\Client\Credentials\TokenCredentials', $credentials);
        $this->assertEquals('tokencredentialsidentifier', $credentials->getIdentifier());
        $this->assertEquals('tokencredentialssecret', $credentials->getSecret());
    }

    public function testGettingUserDetails()
    {
        $server = m::mock('League\OAuth1\Client\Test\Server\Fake[createHttpClient,protocolHeader]', array($this->getMockClientCredentials()));

        $temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials');
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('tokencredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('tokencredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock('stdClass'));

        $me = $this;
        $client->shouldReceive('get')->with('http://www.example.com/user', m::on(function ($headers) use ($me) {
            $me->assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="tokencredentialsidentifier", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            $me->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }))->once()->andReturn($request = m::mock('stdClass'));

        $request->shouldReceive('send')->once()->andReturn($response = m::mock('stdClass'));
        $response->shouldReceive('json')->once()->andReturn(array('foo' => 'bar', 'id' => 123, 'contact_email' => 'baz@qux.com', 'username' => 'fred'));

        $user = $server->getUserDetails($temporaryCredentials);
        $this->assertInstanceOf('League\OAuth1\Client\Server\User', $user);
        $this->assertEquals('bar', $user->firstName);
        $this->assertEquals(123, $server->getUserUid($temporaryCredentials));
        $this->assertEquals('baz@qux.com', $server->getUserEmail($temporaryCredentials));
        $this->assertEquals('fred', $server->getUserScreenName($temporaryCredentials));
        $this->assertEquals([
            'uid' => null,
            'nickname' => null,
            'name' => null,
            'firstName' => 'bar',
            'lastName' => null,
            'email' => null,
            'location' => null,
            'description' => null,
            'imageUrl' => null,
            'urls' => [],
            'extra' => [],
        ], $user->toArray());
    }

    public function testGettingHeaders()
    {
        $server = new Fake($this->getMockClientCredentials());

        $tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials');
        $tokenCredentials->shouldReceive('getIdentifier')->andReturn('mock_identifier');
        $tokenCredentials->shouldReceive('getSecret')->andReturn('mock_secret');

        // OAuth protocol specifies a strict number of
        // headers should be sent, in the correct order.
        // We'll validate that here.
        $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="mock_identifier", oauth_signature=".*?"/';

        // With a GET request
        $headers = $server->getHeaders($tokenCredentials, 'GET', 'http://example.com/');
        $this->assertTrue(isset($headers['Authorization']));

        $matches = preg_match($pattern, $headers['Authorization']);
        $this->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

        // With a POST request
        $headers = $server->getHeaders($tokenCredentials, 'POST', 'http://example.com/', array('body' => 'params'));
        $this->assertTrue(isset($headers['Authorization']));

        $matches = preg_match($pattern, $headers['Authorization']);
        $this->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');
    }

    protected function getMockClientCredentials()
    {
        return array(
            'identifier' => 'myidentifier',
            'secret' => 'mysecret',
            'callback_uri' => 'http://app.dev/',
        );
    }
}
