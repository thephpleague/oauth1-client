<?php namespace League\OAuth1\Client\Tests;

use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Trello;
use League\OAuth1\Client\Server\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class TrelloServerTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testCreatingWithArray(): void
    {
        $server = new Trello($this->getMockClientCredentials());

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

        $server = new Trello($credentials);

        static::assertEquals($credentials, $server->getClientCredentials());
    }

    public function testGettingTemporaryCredentials(): void
    {
        $server = m::mock('League\OAuth1\Client\Server\Trello[createHttpClient]', [$this->getMockClientCredentials()]);

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(GuzzleClient::class));

        $me = $this;
        $client->shouldReceive('post')->with('https://trello.com/1/OAuthGetRequestToken', m::on(static function ($options) use ($me) {
            $headers = $options['headers'];

            $me->assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_callback="' . preg_quote('http%3A%2F%2Fapp.dev%2F',
                    '/') . '", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            $me->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }))->once()->andReturn($response = m::mock(ResponseInterface::class));
        $response->shouldReceive('getBody')->andReturn('oauth_token=temporarycredentialsidentifier&oauth_token_secret=temporarycredentialssecret&oauth_callback_confirmed=true');

        $credentials = $server->getTemporaryCredentials();
        static::assertInstanceOf(TemporaryCredentials::class, $credentials);
        static::assertEquals('temporarycredentialsidentifier', $credentials->getIdentifier());
        static::assertEquals('temporarycredentialssecret', $credentials->getSecret());
    }

    public function testGettingDefaultAuthorizationUrl(): void
    {
        $server = new Trello($this->getMockClientCredentials());

        $expected = 'https://trello.com/1/OAuthAuthorizeToken?response_type=fragment&scope=read&expiration=1day&oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingAuthorizationUrlWithExpirationAfterConstructingWithExpiration(): void
    {
        $credentials = $this->getMockClientCredentials();
        $expiration = $this->getApplicationExpiration(2);
        $credentials['expiration'] = $expiration;
        $server = new Trello($credentials);

        $expected = 'https://trello.com/1/OAuthAuthorizeToken?response_type=fragment&scope=read&expiration=' . urlencode($expiration) . '&oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingAuthorizationUrlWithExpirationAfterSettingExpiration(): void
    {
        $expiration = $this->getApplicationExpiration(2);
        $server = new Trello($this->getMockClientCredentials());
        $server->setApplicationExpiration($expiration);

        $expected = 'https://trello.com/1/OAuthAuthorizeToken?response_type=fragment&scope=read&expiration=' . urlencode($expiration) . '&oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingAuthorizationUrlWithNameAfterConstructingWithName(): void
    {
        $credentials = $this->getMockClientCredentials();
        $name = $this->getApplicationName();
        $credentials['name'] = $name;
        $server = new Trello($credentials);

        $expected = 'https://trello.com/1/OAuthAuthorizeToken?response_type=fragment&scope=read&expiration=1day&name=' . urlencode($name) . '&oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingAuthorizationUrlWithNameAfterSettingName(): void
    {
        $name = $this->getApplicationName();
        $server = new Trello($this->getMockClientCredentials());
        $server->setApplicationName($name);

        $expected = 'https://trello.com/1/OAuthAuthorizeToken?response_type=fragment&scope=read&expiration=1day&name=' . urlencode($name) . '&oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingAuthorizationUrlWithScopeAfterConstructingWithScope(): void
    {
        $credentials = $this->getMockClientCredentials();
        $scope = $this->getApplicationScope(false);
        $credentials['scope'] = $scope;
        $server = new Trello($credentials);

        $expected = 'https://trello.com/1/OAuthAuthorizeToken?response_type=fragment&scope=' . urlencode($scope) . '&expiration=1day&oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingAuthorizationUrlWithScopeAfterSettingScope(): void
    {
        $scope = $this->getApplicationScope(false);
        $server = new Trello($this->getMockClientCredentials());
        $server->setApplicationScope($scope);

        $expected = 'https://trello.com/1/OAuthAuthorizeToken?response_type=fragment&scope=' . urlencode($scope) . '&expiration=1day&oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingTokenCredentialsFailsWithManInTheMiddle()
    {
        $this->expectException(InvalidArgumentException::class);

        $server = new Trello($this->getMockClientCredentials());

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        $server->getTokenCredentials($credentials, 'bar', 'verifier');
    }

    public function testGettingTokenCredentials(): void
    {
        $server = m::mock('League\OAuth1\Client\Server\Trello[createHttpClient]', [$this->getMockClientCredentials()]);

        $temporaryCredentials = m::mock(TemporaryCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('temporarycredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('temporarycredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(GuzzleClient::class));

        $me = $this;
        $client->shouldReceive('post')->with('https://trello.com/1/OAuthGetAccessToken', m::on(static function ($options) use ($me) {
            $headers = $options['headers'];
            $body = $options['form_params'];

            $me->assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            $me->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            $me->assertSame($body, ['oauth_verifier' => 'myverifiercode']);

            return true;
        }))->once()->andReturn($response = m::mock(ResponseInterface::class));
        $response->shouldReceive('getBody')->andReturn('oauth_token=tokencredentialsidentifier&oauth_token_secret=tokencredentialssecret');

        $credentials = $server->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');
        static::assertInstanceOf(TokenCredentials::class, $credentials);
        static::assertEquals('tokencredentialsidentifier', $credentials->getIdentifier());
        static::assertEquals('tokencredentialssecret', $credentials->getSecret());
    }

    public function testGettingUserDetails(): void
    {
        $server = m::mock('League\OAuth1\Client\Server\Trello[createHttpClient,protocolHeader]', [$this->getMockClientCredentials()]);

        $temporaryCredentials = m::mock(TokenCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('tokencredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('tokencredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(GuzzleClient::class));

        $me = $this;
        $client->shouldReceive('get')->with('https://trello.com/1/members/me?key=' . $this->getApplicationKey() . '&token=' . $this->getAccessToken(), m::on(static function ($options) use ($me) {
            $headers = $options['headers'];

            $me->assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="tokencredentialsidentifier", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            $me->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }))->once()->andReturn($response = m::mock(ResponseInterface::class));
        $response->shouldReceive('getBody')->once()->andReturn($this->getUserPayload());

        $user = $server
            ->setAccessToken($this->getAccessToken())
            ->getUserDetails($temporaryCredentials);
        static::assertInstanceOf(User::class, $user);
        static::assertEquals('Matilda Wormwood', $user->name);
        static::assertEquals('545df696e29c0dddaed31967', $server->getUserUid($temporaryCredentials));
        static::assertEquals(null, $server->getUserEmail($temporaryCredentials));
        static::assertEquals('matildawormwood12', $server->getUserScreenName($temporaryCredentials));
    }

    protected function getMockClientCredentials(): array
    {
        return [
            'identifier' => $this->getApplicationKey(),
            'secret' => 'mysecret',
            'callback_uri' => 'http://app.dev/',
        ];
    }

    protected function getAccessToken(): string
    {
        return 'lmnopqrstuvwxyz';
    }

    protected function getApplicationKey(): string
    {
        return 'abcdefghijk';
    }

    protected function getApplicationExpiration($days = 0): string
    {
        return is_numeric($days) && $days > 0 ? $days . 'day' . ($days === 1 ? '' : 's') : 'never';
    }

    protected function getApplicationName(): string
    {
        return 'fizz buzz';
    }

    protected function getApplicationScope($readonly = true): string
    {
        return $readonly ? 'read' : 'read,write';
    }

    private function getUserPayload(): string
    {
        return '{
            "id": "545df696e29c0dddaed31967",
            "avatarHash": null,
            "bio": "I have magical powers",
            "bioData": null,
            "confirmed": true,
            "fullName": "Matilda Wormwood",
            "idPremOrgsAdmin": [],
            "initials": "MW",
            "memberType": "normal",
            "products": [],
            "status": "idle",
            "url": "https://trello.com/matildawormwood12",
            "username": "matildawormwood12",
            "avatarSource": "none",
            "email": null,
            "gravatarHash": "39aaaada0224f26f0bb8f1965326dcb7",
            "idBoards": [
                "545df696e29c0dddaed31968",
                "545e01d6c7b2dd962b5b46cb"
            ],
            "idOrganizations": [
                "54adfd79f9aea14f84009a85",
                "54adfde13b0e706947bc4789"
            ],
            "loginTypes": null,
            "oneTimeMessagesDismissed": [],
            "prefs": {
                "sendSummaries": true,
                "minutesBetweenSummaries": 1,
                "minutesBeforeDeadlineToNotify": 1440,
                "colorBlind": false,
                "timezoneInfo": {
                    "timezoneNext": "CDT",
                    "dateNext": "2015-03-08T08:00:00.000Z",
                    "offsetNext": 300,
                    "timezoneCurrent": "CST",
                    "offsetCurrent": 360
                }
            },
            "trophies": [],
            "uploadedAvatarHash": null,
            "premiumFeatures": [],
            "idBoardsPinned": null
        }';
    }
}
