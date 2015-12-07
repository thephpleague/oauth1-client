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

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Exceptions\Exception;
use League\OAuth1\Client\Exceptions\ConfigurationException;
use League\OAuth1\Client\Exceptions\CredentialsException;
use League\OAuth1\Client\Server\GenericResourceOwner;
use League\OAuth1\Client\Server\GenericServer;
use League\OAuth1\Client\Signature\HmacSha1Signature;
use League\OAuth1\Client\Signature\SignatureInterface;
use League\OAuth1\Client\Tool\Crypto;
use League\OAuth1\Client\Tool\RequestFactory;
use League\OAuth1\Client\Tool\RequestFactoryInterface;
use League\OAuth1\Client\Test\Server\Fake;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class AbstractServerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Close mockery.
     */
    public function tearDown()
    {
        m::close();
    }

    protected function getServerMock(array $collaborators = array(), $useFake = false)
    {
        if ($useFake) {
            return m::mock(
                new Fake($this->getMockClientCredentials(), $collaborators)
            );
        }

        return m::mock(
            new GenericServer($this->getGenericServerCredentials(), $collaborators)
        );
    }

    protected function getRequestMock()
    {
        $request = RequestFactory::getRequest('GET', 'http://foo.bar');

        return $request;
    }

    protected function getResponseMock()
    {
        return m::mock(ResponseInterface::class);
    }


    protected function getHttpClientMock($request, $payload = '', $status = 200)
    {
        $response = $this->getResponseMock();
        $response->shouldReceive('getBody')->andReturn($payload);

        if ($status < 300) {
            $client = m::mock(HttpClient::class);
            $client->shouldReceive('send')->with($request)->once()->andReturn($response);
        } else {
            $mock = new MockHandler([
                new Response($status, [], $payload),
            ]);
            $handler = HandlerStack::create($mock);
            $client = new HttpClient(['handler' => $handler]);
        }

        return $client;
    }

    protected function getTemporaryCredentialsMock()
    {
        $temporaryCredentials = m::mock(TemporaryCredentials::class)->makePartial();
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('temporarycredentialsidentifier');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('temporarycredentialssecret');

        return $temporaryCredentials;
    }

    protected function getTokenCredentialsMock()
    {
        $tokenCredentials = m::mock(TokenCredentials::class);
        $tokenCredentials->shouldReceive('getIdentifier')->andReturn('tokencredentialsidentifier');
        $tokenCredentials->shouldReceive('getSecret')->andReturn('tokencredentialssecret');

        return $tokenCredentials;
    }

    protected function getMockClientCredentials()
    {
        return array(
            'identifier' => 'myidentifier',
            'secret' => 'mysecret',
            'callbackUri' => 'http://app.dev/',
        );
    }

    protected function getGenericServerCredentials($domain = null)
    {
        $domain = $domain ?: 'http://your.service';

        return array_merge($this->getMockClientCredentials(), [
            'temporaryCredentialsUri' => $domain.'/temporary-credentials',
            'authorizationUri'        => $domain.'/authorize',
            'tokenCredentialsUri'     => $domain.'/token-credentials',
            'resourceOwnerDetailsUri' => $domain.'/me',
        ]);
    }

    protected function isTempAuthenticatedRequest($pattern, $headers, $userAgent = null)
    {
        $this->assertTrue(isset($headers['Authorization']));
        $matches = preg_match($pattern, $headers['Authorization']);
        $this->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

        return true;
    }

    protected function isTokenAuthenticatedRequest($pattern, $headers, $userAgent = null)
    {
        $this->assertTrue(isset($headers['Authorization']));

        if ($userAgent) {
            $this->assertTrue(isset($headers['User-Agent']));
            $this->assertEquals($userAgent, $headers['User-Agent']);
        } else {
            $this->assertFalse(isset($headers['User-Agent']));
        }

        $matches = preg_match($pattern, $headers['Authorization']);
        $this->assertEquals(1, $matches, 'Asserting that the authorization header contains the correct expression.');

        return true;
    }

    protected function getProtectedProperty($object, $property)
    {
        $ref = new \ReflectionClass($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }

    public function testCreatingWithArray()
    {
        $server = new GenericServer($this->getGenericServerCredentials());

        $credentials = $server->getClientCredentials();
        $this->assertInstanceOf(ClientCredentials::class, $credentials);
        $this->assertEquals('myidentifier', $credentials->getIdentifier());
        $this->assertEquals('mysecret', $credentials->getSecret());
        $this->assertEquals('http://app.dev/', $credentials->getCallbackUri());
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\ConfigurationException
     */
    public function testCreatingWithEmptyArrayThrowsException()
    {
        $server = new Fake([]);
    }

    public function testGettingSignature()
    {
        $server = new GenericServer($this->getGenericServerCredentials());

        $signature = $server->getSignature();
        $this->assertInstanceOf(SignatureInterface::class, $signature);
    }

    public function testGettingCustomAttributes()
    {
        $name = 'foo';
        $options = array_merge($this->getMockClientCredentials(), ['name' => $name]);
        $server = new Fake($options);

        $actualName = $server->getName();
        $this->assertEquals($name, $actualName);
    }

    public function testGettingAuthorizationUrl()
    {
        $domain = 'http://www.example.com';
        $server = new GenericServer($this->getGenericServerCredentials($domain));

        $expected = $domain.'/authorize?oauth_token=foo';

        $this->assertEquals($expected, $server->getAuthorizationUrl('foo'));

        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');
        $this->assertEquals($expected, $server->getAuthorizationUrl($credentials));
    }

    public function testRedirectingUsers()
    {
        global $mockerHeaderRedirect;
        $server = $this->getServerMock();
        $tempId = 'foo';
        $url = $server->getAuthorizationUrl($tempId);
        $mockerHeaderRedirect = 'Location: '.$url;

        $redirect = $server->authorize($tempId);
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\ConfigurationException
     */
    public function testGettingTokenCredentialsFailsWithManInTheMiddle()
    {
        $server = new Fake($this->getMockClientCredentials());

        $credentials = m::mock(TemporaryCredentials::class)->makePartial();
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        $server->getTokenCredentials($credentials, 'bar', 'verifier');
    }

    public function testGettingTemporaryCredentials()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_callback="'.preg_quote('http%3A%2F%2Fapp.dev%2F', '/').'", oauth_signature=".*?"/';
        $payload = 'oauth_token=temporarycredentialsidentifier&oauth_token_secret=temporarycredentialssecret&oauth_callback_confirmed=true';
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('GET', 'http://your.service/temporary-credentials', m::on(function ($headers) use ($headerPattern) {
            return $this->isTempAuthenticatedRequest($headerPattern, $headers);
        }))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTemporaryCredentials();
        $this->assertInstanceOf(TemporaryCredentials::class, $credentials);
        $this->assertEquals('temporarycredentialsidentifier', $credentials->getIdentifier());
        $this->assertEquals('temporarycredentialssecret', $credentials->getSecret());
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\Exception
     */
    public function testGettingTemporaryCredentialsThrowsExceptionOnHttpError()
    {
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('GET', 'http://your.service/temporary-credentials', m::on(function ($headers) {
            return is_array($headers);
        }))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, null, 400);

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTemporaryCredentials();
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\CredentialsException
     */
    public function testGettingTemporaryCredentialsThrowsExceptionOnInvalidResponse()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_callback="'.preg_quote('http%3A%2F%2Fapp.dev%2F', '/').'", oauth_signature=".*?"/';
        $payload = '';
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('GET', 'http://your.service/temporary-credentials', m::on(function ($headers) use ($headerPattern) {
            return $this->isTempAuthenticatedRequest($headerPattern, $headers);
        }))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTemporaryCredentials();
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\CredentialsException
     */
    public function testGettingTemporaryCredentialsThrowsExceptionOnMissingOauthKeys()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_callback="'.preg_quote('http%3A%2F%2Fapp.dev%2F', '/').'", oauth_signature=".*?"/';
        $payload = 'foo=bar';
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('GET', 'http://your.service/temporary-credentials', m::on(function ($headers) use ($headerPattern) {
            return $this->isTempAuthenticatedRequest($headerPattern, $headers);
        }))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTemporaryCredentials();
    }

    public function testGettingTokenCredentials()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';
        $payload = 'oauth_token=tokencredentialsidentifier&oauth_token_secret=tokencredentialssecret';
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('POST', 'http://your.service/token-credentials', m::on(function ($headers) use ($headerPattern) {
            return $this->isTokenAuthenticatedRequest($headerPattern, $headers);
        }), json_encode(array('oauth_verifier' => 'myverifiercode')))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $temporaryCredentials = $this->getTemporaryCredentialsMock();

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');

        $this->assertInstanceOf(TokenCredentials::class, $credentials);
        $this->assertEquals('tokencredentialsidentifier', $credentials->getIdentifier());
        $this->assertEquals('tokencredentialssecret', $credentials->getSecret());
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\Exception
     */
    public function testGettingTokenCredentialsThrowsExceptionOnHttpError()
    {
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('POST', 'http://your.service/token-credentials', m::on(function ($headers) {
            return is_array($headers);
        }), json_encode(array('oauth_verifier' => 'myverifiercode')))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, null, 400);

        $temporaryCredentials = $this->getTemporaryCredentialsMock();

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\CredentialsException
     */
    public function testGettingTokenCredentialsThorwsExceptionOnInvalidResponse()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';
        $payload = '';
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('POST', 'http://your.service/token-credentials', m::on(function ($headers) use ($headerPattern) {
            return $this->isTokenAuthenticatedRequest($headerPattern, $headers);
        }), json_encode(array('oauth_verifier' => 'myverifiercode')))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $temporaryCredentials = $this->getTemporaryCredentialsMock();

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\CredentialsException
     */
    public function testGettingTokenCredentialsThorwsExceptionOnErrorInResponse()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';
        $payload = 'error=foo';
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('POST', 'http://your.service/token-credentials', m::on(function ($headers) use ($headerPattern) {
            return $this->isTokenAuthenticatedRequest($headerPattern, $headers);
        }), json_encode(array('oauth_verifier' => 'myverifiercode')))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $temporaryCredentials = $this->getTemporaryCredentialsMock();

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');
    }

    public function testGettingTokenCredentialsWithUserAgent()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="temporarycredentialsidentifier", oauth_signature=".*?"/';
        $userAgent = 'FooBar';
        $payload = 'oauth_token=tokencredentialsidentifier&oauth_token_secret=tokencredentialssecret';
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('POST', 'http://your.service/token-credentials', m::on(function ($headers) use ($userAgent, $headerPattern) {
            return $this->isTokenAuthenticatedRequest($headerPattern, $headers, $userAgent);
        }), json_encode(array('oauth_verifier' => 'myverifiercode')))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $temporaryCredentials = $this->getTemporaryCredentialsMock();

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $credentials = $server->setUserAgent($userAgent)->getTokenCredentials($temporaryCredentials, 'temporarycredentialsidentifier', 'myverifiercode');

        $this->assertInstanceOf(TokenCredentials::class, $credentials);
        $this->assertEquals('tokencredentialsidentifier', $credentials->getIdentifier());
        $this->assertEquals('tokencredentialssecret', $credentials->getSecret());
    }

    public function testGettingUserDetails()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="tokencredentialsidentifier", oauth_signature=".*?"/';
        $userData = ['foo' => 'bar', 'id' => 123, 'contact_email' => 'baz@qux.com', 'username' => 'fred'];
        $payload = json_encode($userData);
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('GET', 'http://your.service/me', m::on(function ($headers) use ($headerPattern) {
            return $this->isTokenAuthenticatedRequest($headerPattern, $headers);
        }))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, $payload);

        $tokenCredentials = $this->getTokenCredentialsMock();

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $user = $server->getResourceOwner($tokenCredentials);

        $this->assertInstanceOf(GenericResourceOwner::class, $user);
        $this->assertEquals(123, $user->getId());
        $this->assertEquals($userData, $user->getIterator());
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\Exception
     */
    public function testGettingUserDetailsThrowsException()
    {
        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with('GET', 'http://your.service/me', m::on(function ($headers) {
            return is_array($headers);
        }))->once()->andReturn($request);

        $httpClient = $this->getHttpClientMock($request, null, 400);

        $tokenCredentials = $this->getTokenCredentialsMock();

        $collaborators = ['httpClient' => $httpClient, 'requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $user = $server->getResourceOwner($tokenCredentials);
    }

    public function testGettingHeaders()
    {
        // OAuth protocol specifies a strict number of
        // headers should be sent, in the correct order.
        // We'll validate that here.
        $pattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="tokencredentialsidentifier", oauth_signature=".*?"/';

        $tokenCredentials = $this->getTokenCredentialsMock();

        $server = new Fake($this->getMockClientCredentials());

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

    public function testGettingAuthenticatedRequest()
    {
        $headerPattern = '/OAuth oauth_consumer_key=".*?", oauth_nonce="[a-zA-Z0-9]+", oauth_signature_method="HMAC-SHA1", oauth_timestamp="\d{10}", oauth_version="1.0", oauth_token="tokencredentialsidentifier", oauth_signature=".*?"/';
        $url = 'foo';
        $method = 'bar';

        $request = $this->getRequestMock();

        $requestFactory = m::mock(RequestFactoryInterface::class);
        $requestFactory->shouldReceive('getRequest')->with($method, $url, m::on(function ($headers) use ($headerPattern) {
            return $this->isTokenAuthenticatedRequest($headerPattern, $headers);
        }))->once()->andReturn($request);

        $collaborators = ['requestFactory' => $requestFactory];

        $server = $this->getServerMock($collaborators);

        $tokenCredentials = $this->getTokenCredentialsMock();

        $authenticatedRequest = $server-> getAuthenticatedRequest($method, $url, $tokenCredentials);

        $this->assertEquals($request, $authenticatedRequest);
    }

    public function testParsingResourceOwnerDetailsJson()
    {
        $payload = ['foo' => 'bar'];
        $json = json_encode($payload);
        $response = $this->getResponseMock();
        $response->shouldReceive('getBody')->andReturn($json);

        $server = new Fake($this->getMockClientCredentials());

        $server->parseResourceOwnersDetailsResponse($response);

        $resourceOwner = $this->getProtectedProperty($server, 'cachedUserDetailsResponse');

        $this->assertEquals($payload, $resourceOwner);
    }

    public function testParsingResourceOwnerDetailsXml()
    {
        $payload = ['foo' => 'bar'];
        $xml = new \SimpleXMLElement('<root/>');
        $flipped = array_flip($payload);
        array_walk_recursive($flipped, array ($xml, 'addChild'));
        $response = $this->getResponseMock();
        $response->shouldReceive('getBody')->andReturn($xml->asXML());

        $server = new Fake($this->getMockClientCredentials());

        $server->setResponseType('xml')->parseResourceOwnersDetailsResponse($response);

        $resourceOwner = json_decode(json_encode($this->getProtectedProperty($server, 'cachedUserDetailsResponse')), true);

        $this->assertEquals($payload, $resourceOwner);
    }

    public function testParsingResourceOwnerDetailsString()
    {
        $payload = ['foo' => 'bar'];
        $string = http_build_query($payload);
        $response = $this->getResponseMock();
        $response->shouldReceive('getBody')->andReturn($string);

        $server = new Fake($this->getMockClientCredentials());

        $server->setResponseType('string')->parseResourceOwnersDetailsResponse($response);

        $resourceOwner = $this->getProtectedProperty($server, 'cachedUserDetailsResponse');

        $this->assertEquals($payload, $resourceOwner);
    }

    /**
     * @expectedException League\OAuth1\Client\Exceptions\ConfigurationException
     */
    public function testParsingResourceOwnerDetailsInvalid()
    {
        $response = $this->getResponseMock();

        $server = new Fake($this->getMockClientCredentials());

        $server->setResponseType(uniqid())->parseResourceOwnersDetailsResponse($response);
    }
}
