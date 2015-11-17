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

namespace League\OAuth1\Client\Test\Exceptions;

use GuzzleHttp\Exception\BadResponseException;
use League\OAuth1\Client\Exceptions\Exception;
use League\OAuth1\Client\Exceptions\ConfigurationException;
use League\OAuth1\Client\Exceptions\CredentialsException;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ExceptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Close mockery.
     */
    public function tearDown()
    {
        m::close();
    }

    protected function getBadResponseException($body = null, $statusCode = null)
    {
        $message = 'foo bar';
        $body = $body ?: 'foo';
        $statusCode = $statusCode ?: 400;

        $request = m::mock(RequestInterface::class);

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($body);
        $response->shouldReceive('getStatusCode')->andReturn($statusCode);

        return new BadResponseException($message, $request, $response);
    }

    public function testConfigurationExceptionHandlesInvalidResponseType()
    {
        $responseType = 'foo';

        try {
            ConfigurationException::handleInvalidResponseType($responseType);
        } catch (ConfigurationException $e) {
            $this->assertContains($responseType, $e->getMessage());
        }
    }

    public function testConfigurationExceptionHandlesMissingRequiredOption()
    {
        $requiredOption = 'foo';

        try {
            ConfigurationException::handleMissingRequiredOption($requiredOption);
        } catch (ConfigurationException $e) {
            $this->assertContains($requiredOption, $e->getMessage());
        }
    }

    public function testConfigurationExceptionHandlesTemporaryIdentifierMismatch()
    {
        try {
            ConfigurationException::handleTemporaryIdentifierMismatch();
        } catch (ConfigurationException $e) {
            $this->assertContains('man-in-the-middle', $e->getMessage());
        }
    }

    public function testCredentialsExceptionHandlesResponseParseError()
    {
        $type = 'foo';

        try {
            CredentialsException::handleResponseParseError($type);
        } catch (CredentialsException $e) {
            $this->assertContains($type, $e->getMessage());
        }
    }

    public function testCredentialsExceptionHandlesTemporaryCredentialsBadResponse()
    {
        $body = 'foo';
        $statusCode = '400';
        $exception = $this->getBadResponseException($body, $statusCode);

        try {
            CredentialsException::handleTemporaryCredentialsBadResponse($exception);
        } catch (CredentialsException $e) {
            $this->assertContains($body, $e->getMessage());
            $this->assertContains($statusCode, $e->getMessage());
        }
    }

    public function testCredentialsExceptionHandlesTemporaryCredentialsRetrievalError()
    {
        try {
            CredentialsException::handleTemporaryCredentialsRetrievalError();
        } catch (CredentialsException $e) {
            $this->assertContains('Error in retrieving temporary credentials.', $e->getMessage());
        }
    }

    public function testCredentialsExceptionHandlesTokenCredentialsBadResponse()
    {
        $body = 'foo';
        $statusCode = '400';
        $exception = $this->getBadResponseException($body, $statusCode);

        try {
            CredentialsException::handleTokenCredentialsBadResponse($exception);
        } catch (CredentialsException $e) {
            $this->assertContains($body, $e->getMessage());
            $this->assertContains($statusCode, $e->getMessage());
        }
    }

    public function testCredentialsExceptionHandlesTokenCredentialsRetrievalError()
    {
        $error = 'foo';

        try {
            CredentialsException::handleTokenCredentialsRetrievalError($error);
        } catch (CredentialsException $e) {
            $this->assertContains($error, $e->getMessage());
        }
    }
}
