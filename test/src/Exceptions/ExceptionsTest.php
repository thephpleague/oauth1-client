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

        $exception = ConfigurationException::invalidResponseType($responseType);

        $this->assertContains($responseType, $exception->getMessage());
    }

    public function testConfigurationExceptionHandlesMissingRequiredOption()
    {
        $requiredOption = 'foo';

        $exception = ConfigurationException::missingRequiredOption($requiredOption);

        $this->assertContains($requiredOption, $exception->getMessage());
    }

    public function testConfigurationExceptionHandlesTemporaryIdentifierMismatch()
    {
        $exception = ConfigurationException::temporaryIdentifierMismatch();

        $this->assertContains('man-in-the-middle', $exception->getMessage());
    }

    public function testCredentialsExceptionHandlesResponseParseError()
    {
        $type = 'foo';

        $exception = CredentialsException::responseParseError($type);

        $this->assertContains($type, $exception->getMessage());
    }

    public function testCredentialsExceptionHandlesTemporaryCredentialsBadResponse()
    {
        $body = 'foo';
        $statusCode = '400';
        $exception = $this->getBadResponseException($body, $statusCode);

        $exception = CredentialsException::temporaryCredentialsBadResponse($exception);

        $this->assertContains($body, $exception->getMessage());
        $this->assertContains($statusCode, $exception->getMessage());
    }

    public function testCredentialsExceptionHandlesTemporaryCredentialsRetrievalError()
    {
        $exception = CredentialsException::temporaryCredentialsRetrievalError();

        $this->assertContains('Error in retrieving temporary credentials.', $exception->getMessage());
    }

    public function testCredentialsExceptionHandlesTokenCredentialsBadResponse()
    {
        $body = 'foo';
        $statusCode = '400';
        $exception = $this->getBadResponseException($body, $statusCode);

        $exception = CredentialsException::tokenCredentialsBadResponse($exception);

        $this->assertContains($body, $exception->getMessage());
        $this->assertContains($statusCode, $exception->getMessage());
    }

    public function testCredentialsExceptionHandlesTokenCredentialsRetrievalError()
    {
        $error = 'foo';

        $exception = CredentialsException::tokenCredentialsRetrievalError($error);
        $this->assertContains($error, $exception->getMessage());
    }
}
