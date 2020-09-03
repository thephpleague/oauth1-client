<?php

namespace League\OAuth1\Client\Tests;

use GuzzleHttp\Client;
use InvalidArgumentException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\User;
use League\OAuth1\Client\Server\Xing;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class XingServerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function testCreatingWithArray()
    {
        $server = new Xing($this->getMockClientCredentials());

        $credentials = $server->getClientCredentials();

        static::assertInstanceOf(ClientCredentialsInterface::class, $credentials);
        static::assertEquals($this->getApplicationKey(), $credentials->getIdentifier());
        static::assertEquals('mysecret', $credentials->getSecret());
        static::assertEquals('http://app.dev/', $credentials->getCallbackUri());
    }

    public function testCreatingWithObject()
    {
        $credentials = new ClientCredentials;
        $credentials->setIdentifier('myidentifier');
        $credentials->setSecret('mysecret');
        $credentials->setCallbackUri('http://app.dev/');

        $server = new Xing($credentials);

        static::assertEquals($credentials, $server->getClientCredentials());
    }

    public function testGettingTemporaryCredentials()
    {
        $server = m::mock('League\OAuth1\Client\Server\Xing[createHttpClient]', [$this->getMockClientCredentials()]);

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(Client::class));

        $client->shouldReceive('post')->with('https://api.xing.com/v1/request_token', m::on(function ($options) {
            $headers = $options['headers'];

            static::assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_callback="' . preg_quote('http%3A%2F%2Fapp.dev%2F', '/') . '", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);

            static::assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }))->once()->andReturn($response = m::mock(ResponseInterface::class));
        $response->shouldReceive('getBody')->andReturn('oauth_token=temporarycredentialsidentifier&oauth_token_secret=temporarycredentialssecret&oauth_callback_confirmed=true');

        $credentials = $server->getTemporaryCredentials();

        static::assertInstanceOf(TemporaryCredentials::class, $credentials);
        static::assertEquals('temporarycredentialsidentifier', $credentials->getIdentifier());
        static::assertEquals('temporarycredentialssecret', $credentials->getSecret());
    }

    public function testGettingDefaultAuthorizationUrl()
    {
        $server = new Xing($this->getMockClientCredentials());

        $expected = 'https://api.xing.com/v1/authorize?oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingTokenCredentialsFailsWithManInTheMiddle()
    {
        $server = new Xing($this->getMockClientCredentials());

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        $this->expectException(InvalidArgumentException::class);

        $server->getTokenCredentials($credentials, 'bar', 'verifier');
    }

    public function testGettingTokenCredentials()
    {
        $server = m::mock('League\OAuth1\Client\Server\Xing[createHttpClient]', [$this->getMockClientCredentials()]);

        $temporaryCredentials = m::mock(TemporaryCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('temporarycredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('temporarycredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(Client::class));

        $client->shouldReceive('post')->with('https://api.xing.com/v1/access_token', m::on(function ($options) {
            $headers = $options['headers'];
            $body = $options['form_params'];

            static::assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';
            $matches = preg_match($pattern, $headers['Authorization']);

            static::assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');
            static::assertSame($body, ['oauth_verifier' => 'myverifiercode']);

            return true;
        }))->once()->andReturn($response = m::mock(ResponseInterface::class));
        $response->shouldReceive('getBody')->andReturn('oauth_token=tokencredentialsidentifier&oauth_token_secret=tokencredentialssecret');

        $credentials = $server->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');

        static::assertInstanceOf(TokenCredentials::class, $credentials);
        static::assertEquals('tokencredentialsidentifier', $credentials->getIdentifier());
        static::assertEquals('tokencredentialssecret', $credentials->getSecret());
    }

    public function testGettingUserDetails()
    {
        $server = m::mock('League\OAuth1\Client\Server\Xing[createHttpClient,protocolHeader]', [$this->getMockClientCredentials()]);

        $temporaryCredentials = m::mock(TokenCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('tokencredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('tokencredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(Client::class));

        $client->shouldReceive('get')->with('https://api.xing.com/v1/users/me', m::on(function ($options) {
            $headers = $options['headers'];

            static::assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="tokencredentialsidentifier", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            static::assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }))->once()->andReturn($response = m::mock(ResponseInterface::class));
        $response->shouldReceive('getBody')->once()->andReturn($this->getUserPayload());

        $user = $server->getUserDetails($temporaryCredentials);

        static::assertInstanceOf(User::class, $user);
        static::assertEquals('Roman Gelembjuk', $user->name);
        static::assertEquals('17144430_0f9409', $server->getUserUid($temporaryCredentials));
        static::assertEquals('XXXXXXXXXX@gmail.com', $server->getUserEmail($temporaryCredentials));
        static::assertEquals('Roman Gelembjuk', $server->getUserScreenName($temporaryCredentials));
    }

    protected function getMockClientCredentials()
    {
        return [
            'identifier' => $this->getApplicationKey(),
            'secret' => 'mysecret',
            'callback_uri' => 'http://app.dev/',
        ];
    }

    protected function getApplicationKey()
    {
        return 'abcdefghijk';
    }

    protected function getApplicationExpiration($days = 0)
    {
        return is_numeric($days) && $days > 0 ? $days . 'day' . ($days == 1 ? '' : 's') : 'never';
    }

    protected function getApplicationName()
    {
        return 'fizz buzz';
    }

    private function getUserPayload()
    {
        return '{
		"users":[
			{
			"id":"17144430_0f9409",
			"active_email":"XXXXXXXXXX@gmail.com",
			"time_zone":
				{
				"utc_offset":3.0,
				"name":"Europe/Kiev"
				},
			"display_name":"Roman Gelembjuk",
			"first_name":"Roman",
			"last_name":"Gelembjuk",
			"gender":"m",
			"page_name":"Roman_Gelembjuk",
			"birth_date":
				{"year":null,"month":null,"day":null},
			"wants":null,
			"haves":null,
			"interests":null,
			"web_profiles":{},
			"badges":[],
			"photo_urls":
				{
				"large":"https://x1.xingassets.com/assets/frontend_minified/img/users/nobody_m.140x185.jpg",
				"maxi_thumb":"https://x1.xingassets.com/assets/frontend_minified/img/users/nobody_m.70x93.jpg",
				"medium_thumb":"https://x1.xingassets.com/assets/frontend_minified/img/users/nobody_m.57x75.jpg"
				},
			"permalink":"https://www.xing.com/profile/Roman_Gelembjuk",
			"languages":{"en":null},
			"employment_status":"EMPLOYEE",
			"organisation_member":null,
			"instant_messaging_accounts":{},
			"educational_background":
				{"degree":null,"primary_school":null,"schools":[],"qualifications":[]},
			"private_address":{
				"street":null,
				"zip_code":null,
				"city":null,
				"province":null,
				"country":null,
				"email":"XXXXXXXX@gmail.com",
				"fax":null,
				"phone":null,
				"mobile_phone":null}
			,"business_address":
				{
					"street":null,
					"zip_code":null,
					"city":"Ivano-Frankivsk",
					"province":null,
					"country":"UA",
					"email":null,
					"fax":null,"phone":null,"mobile_phone":null
				},
			"premium_services":[]
			}]}';
    }
}
