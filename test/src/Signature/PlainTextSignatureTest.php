<?php
/**
 * This file is part of the league/oauth1-client library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */
namespace League\OAuth1\Client\Test\Signature;

use League\OAuth1\Client\Signature\PlainTextSignature;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class PlainTextSignatureTest extends PHPUnit_Framework_TestCase
{
    /**
     * Close mockery.
     */
    public function tearDown()
    {
        m::close();
    }

    public function testSigningRequest()
    {
        $signature = new PlainTextSignature($this->getMockClientCredentials());
        $this->assertEquals('clientsecret&', $signature->sign($uri = 'http://www.example.com/'));

        $signature->setCredentials($this->getMockCredentials());
        $this->assertEquals('clientsecret&tokensecret', $signature->sign($uri));
        $this->assertEquals('PLAINTEXT', $signature->method());
    }

    protected function getMockClientCredentials()
    {
        $clientCredentials = m::mock('League\OAuth1\Client\Credentials\ClientCredentials');
        $clientCredentials->shouldReceive('getSecret')->andReturn('clientsecret');

        return $clientCredentials;
    }

    protected function getMockCredentials()
    {
        $credentials = m::mock('League\OAuth1\Client\Credentials\Credentials');
        $credentials->shouldReceive('getSecret')->andReturn('tokensecret');

        return $credentials;
    }
}
