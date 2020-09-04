<?php

namespace League\OAuth1\Client\Tests;

use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use InvalidArgumentException;
use League\OAuth1\Client\RequestInjector;
use PHPUnit\Framework\TestCase;

class RequestInjectorTest extends TestCase
{
    private const CLIENT_IDENTIFIER = 'client-identifier';
    private const CALLBACK_URI      = 'https://api.client.com/callback';
    private const REALM             = 'Photos';
    private const SIGNATURE         = '"=ab/cd+';

    /** @var RequestInjector */
    private $injector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->injector = new RequestInjector();
    }

    /** @test */
    public function it_injects_into_the_header(): void
    {
        $request = (new Request('GET', 'https://api.example.com/request-temporary-credentials'))
            ->withHeader('Authorization', 'Existing authorization header');

        $oauthParameters = [
            'realm'              => self::REALM,
            'oauth_consumer_key' => self::CLIENT_IDENTIFIER,
            'oauth_callback'     => self::CALLBACK_URI,
        ];

        $request = $this->injector->inject(
            $request,
            $oauthParameters,
            self::SIGNATURE,
            RequestInjector::LOCATION_HEADER
        );

        self::assertTrue($request->hasHeader('Authorization'));

        $expected = 'OAuth realm="Photos", oauth_consumer_key="client-identifier", oauth_callback="https%3A%2F%2Fapi.client.com%2Fcallback", oauth_signature="%22%3Dab%2Fcd%2B"';

        self::assertEquals(
            $expected,
            $request->getHeaderLine('Authorization'),
            'The correct authorization header is injected and replaces any pre-existing header'
        );
    }

    /** @test */
    public function it_injects_into_the_query_string(): void
    {
        $request = new Request('GET', 'https://api.example.com/request-temporary-credentials?existing=string');

        $oauthParameters = [
            'realm'              => self::REALM,
            'oauth_consumer_key' => self::CLIENT_IDENTIFIER,
            'oauth_callback'     => self::CALLBACK_URI,
        ];

        $request = $this->injector->inject(
            $request,
            $oauthParameters,
            self::SIGNATURE,
            RequestInjector::LOCATION_QUERY
        );

        $expected = 'realm=Photos&oauth_consumer_key=client-identifier&oauth_callback=https%3A%2F%2Fapi.client.com%2Fcallback&oauth_signature=%22%3Dab%2Fcd%2B';

        self::assertEquals(
            $expected,
            $request->getUri()->getQuery(),
            'The correct query string is injected and replaces any pre-existing query string'
        );
    }

    /** @test */
    public function it_injects_into_the_body(): void
    {
        $request = (new Request('GET', 'https://api.example.com/request-temporary-credentials'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for('{"some":"json"}'));

        $oauthParameters = [
            'realm'              => self::REALM,
            'oauth_consumer_key' => self::CLIENT_IDENTIFIER,
            'oauth_callback'     => self::CALLBACK_URI,
        ];

        $request = $this->injector->inject(
            $request,
            $oauthParameters,
            self::SIGNATURE,
            RequestInjector::LOCATION_BODY
        );

        $expected = 'realm=Photos&oauth_consumer_key=client-identifier&oauth_callback=https%3A%2F%2Fapi.client.com%2Fcallback&oauth_signature=%22%3Dab%2Fcd%2B';

        self::assertTrue($request->hasHeader('Content-Type'));
        self::assertEquals(
            'application/x-www-form-urlencoded',
            $request->getHeaderLine('Content-Type'),
            'The correct header is injected and replaces any pre-existign header'
        );

        self::assertEquals(
            $expected,
            $request->getBody(),
            'The correct body injected and replaces any pre-existing body'
        );
    }

    /** @test */
    public function it_validates_the_location(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->injector->inject(
            new Request('GET', 'https://api.example.com/request-temporary-credentials'),
            [],
            self::SIGNATURE,
            'invalid'
        );
    }
}
