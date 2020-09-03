<?php namespace League\OAuth1\Client\Tests;

use GuzzleHttp\Client;
use InvalidArgumentException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\RsaClientCredentials;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\RsaSha1Signature;
use League\OAuth1\Client\Tests\Stubs\ServerStub;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ServerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function testCreatingWithArray(): void
    {
        $server = new ServerStub($this->getMockClientCredentials());
        $credentials = $server->getClientCredentials();

        static::assertInstanceOf(ClientCredentialsInterface::class, $credentials);
        static::assertEquals('myidentifier', $credentials->getIdentifier());
        static::assertEquals('mysecret', $credentials->getSecret());
        static::assertEquals('http://app.dev/', $credentials->getCallbackUri());
    }

    public function testCreatingWithArrayRsa(): void
    {
        $server = new ServerStub([
            'identifier' => 'app_key',
            'secret' => 'secret',
            'callback_uri' => 'https://example.com/callback',
            'rsa_public_key' => __DIR__.'/test_rsa_publickey.pem',
            'rsa_private_key' => __DIR__.'/test_rsa_privatekey.pem',
        ]);

        $credentials = $server->getClientCredentials();

        static::assertInstanceOf(RsaClientCredentials::class, $credentials);

        $signature = $server->getSignature();

        static::assertInstanceOf(RsaSha1Signature::class, $signature);
    }

    public function testCreatingWithObject(): void
    {
        $credentials = new ClientCredentials;
        $credentials->setIdentifier('myidentifier');
        $credentials->setSecret('mysecret');
        $credentials->setCallbackUri('http://app.dev/');

        $server = new ServerStub($credentials);

        static::assertEquals($credentials, $server->getClientCredentials());
    }

    public function testCreatingWithInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServerStub(uniqid('', true));
    }

    public function testGettingTemporaryCredentials(): void
    {
        /** @var \League\OAuth1\Client\Tests\Stubs\ServerStub|m\MockInterface $server */
        $server = m::mock('League\OAuth1\Client\Tests\Stubs\ServerStub[createHttpClient]', [$this->getMockClientCredentials()]);

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(Client::class));

        $client->shouldReceive('post')->with('http://www.example.com/temporary', m::on(function ($options) {
            $headers = $options['headers'];

            static::assertTrue(isset($headers['Authorization']));

            // OAuth protocol specifies a strict number of
            // headers should be sent, in the correct order.
            // We'll validate that here.
            $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_callback="'.preg_quote('http%3A%2F%2Fapp.dev%2F', '/').'", oauth_signature=".*?"/';

            $matches = preg_match($pattern, $headers['Authorization']);
            static::assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

            return true;
        }))->once()->andReturn($response = m::mock(ResponseInterface::class));
        $response->shouldReceive('getBody')->andReturn('oauth_token=temporarycredentialsidentifier&oauth_token_secret=temporarycredentialssecret&oauth_callback_confirmed=true');

        $credentials = $server->getTemporaryCredentials();

        static::assertEquals('temporarycredentialsidentifier', $credentials->getIdentifier());
        static::assertEquals('temporarycredentialssecret', $credentials->getSecret());
    }

    public function testGettingAuthorizationUrl(): void
    {
        $server = new ServerStub($this->getMockClientCredentials());

        $expected = 'http://www.example.com/authorize?oauth_token=foo';

        static::assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        static::assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testGettingTokenCredentialsFailsWithManInTheMiddle(): void
    {
        $server = new ServerStub($this->getMockClientCredentials());

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        $this->expectException(InvalidArgumentException::class);

        $server->getTokenCredentials($credentials, 'bar', 'verifier');
    }

    public function testGettingTokenCredentials(): void
    {
        /** @var ServerStub|m\MockInterface $server */
        $server = m::mock('League\OAuth1\Client\Tests\Stubs\ServerStub[createHttpClient]', [$this->getMockClientCredentials()]);

        $temporaryCredentials = m::mock(TemporaryCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('temporarycredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('temporarycredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(Client::class));

        $client->shouldReceive('post')->with('http://www.example.com/token', m::on(function ($options) {
            $headers = $options['headers'];
            $body = $options['form_params'];

            static::assertTrue(isset($headers['Authorization']));
            static::assertFalse(isset($headers['User-Agent']));

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

    public function testGettingTokenCredentialsWithUserAgent()
    {
        $userAgent = 'FooBar';

        /** @var \League\OAuth1\Client\Tests\Stubs\ServerStub|m\MockInterface $server */
        $server = m::mock('League\OAuth1\Client\Tests\Stubs\ServerStub[createHttpClient]', [$this->getMockClientCredentials()]);

        $temporaryCredentials = m::mock(TemporaryCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('temporarycredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('temporarycredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(Client::class));

        $client->shouldReceive('post')->with('http://www.example.com/token', m::on(function ($options) use ($userAgent) {
            $headers = $options['headers'];
            $body = $options['form_params'];

            static::assertTrue(isset($headers['Authorization']));
            static::assertTrue(isset($headers['User-Agent']));
            static::assertEquals($userAgent, $headers['User-Agent']);

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

        $credentials = $server->setUserAgent($userAgent)->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');

        static::assertEquals('tokencredentialsidentifier', $credentials->getIdentifier());
        static::assertEquals('tokencredentialssecret', $credentials->getSecret());
    }

    public function testGettingUserDetails(): void
    {
        /** @var \League\OAuth1\Client\Tests\Stubs\ServerStub|m\MockInterface $server */
        $server = m::mock('League\OAuth1\Client\Tests\Stubs\ServerStub[createHttpClient,protocolHeader]', [$this->getMockClientCredentials()]);

        $temporaryCredentials = m::mock(TokenCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('tokencredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('tokencredentialssecret');

        $server->shouldReceive('createHttpClient')->andReturn($client = m::mock(Client::class));

        $client->shouldReceive('get')->with('http://www.example.com/user', m::on(function ($options) {
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
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['foo' => 'bar', 'id' => 123, 'contact_email' => 'baz@qux.com', 'username' => 'fred']));

        $user = $server->getUserDetails($temporaryCredentials);

        static::assertEquals('bar', $user->firstName);
        static::assertEquals(123, $server->getUserUid($temporaryCredentials));
        static::assertEquals('baz@qux.com', $server->getUserEmail($temporaryCredentials));
        static::assertEquals('fred', $server->getUserScreenName($temporaryCredentials));
    }

    public function testGettingHeaders(): void
    {
        $server = new ServerStub($this->getMockClientCredentials());

        $tokenCredentials = m::mock(TokenCredentials::class);
        $tokenCredentials->shouldReceive('getIdentifier')->andReturn('mock_identifier');
        $tokenCredentials->shouldReceive('getSecret')->andReturn('mock_secret');

        // OAuth protocol specifies a strict number of
        // headers should be sent, in the correct order.
        // We'll validate that here.
        $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="mock_identifier", oauth_signature=".*?"/';

        // With a GET request
        $headers = $server->getHeaders($tokenCredentials, 'GET', 'http://example.com/');

        static::assertTrue(isset($headers['Authorization']));

        $matches = preg_match($pattern, $headers['Authorization']);

        static::assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

        // With a POST request
        $headers = $server->getHeaders($tokenCredentials, 'POST', 'http://example.com/', ['body' => 'params']);

        static::assertTrue(isset($headers['Authorization']));

        $matches = preg_match($pattern, $headers['Authorization']);

        static::assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');
    }

    protected function getMockClientCredentials(): array
    {
        return [
            'identifier' => 'myidentifier',
            'secret' => 'mysecret',
            'callback_uri' => 'http://app.dev/',
        ];
    }
}
