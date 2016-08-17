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

namespace League\OAuth1\Client\Exception;

use Psr\Http\Message\ResponseInterface;

class CredentialsException extends Exception
{
    /**
     * Handles an error in parsing credentials from a given response.
     *
     * @param ResponseInterface $response
     * @param string            $type
     *
     * @return CredentialsException
     */
    public static function failedToParseResponse(ResponseInterface $response, $type)
    {
        $message = sprintf('Unable to parse "%s" credentials response.', $type);

        return static::withResponse($response, $message);
    }

    /**
     * Creates an Exception for when there is a bad response returned while fetching temporary credentials.
     *
     * @param ResponseInterface $response
     * @param string            $customMessage
     *
     * @return CredentialsException
     */
    public static function failedFetchingTemporaryCredentials(ResponseInterface $response, $customMessage = null)
    {
        if (null === $customMessage) {
            $customMessage = 'Received HTTP status code [%d] with message "%s" when getting temporary credentials.';
        }

        return static::withResponse($response, $customMessage);
    }

    /**
     * Creates an Exception for when there temporary credentials are missing from a response payload.
     *
     * @param ResponseInterface $response
     * @param string            $customMessage
     *
     * @return CredentialsException
     */
    public static function failedParsingTemporaryCredentialsResponse(ResponseInterface $response, $customMessage = null)
    {
        if (null === $customMessage) {
            $message = sprintf('Error "%s" while retrieving temporary credentials.', $customMessage);
        } else {
            $message = 'Could not find temporary credentials in response payload.';
        }

        return static::withResponse($response, $message);
    }

    /**
     * Creates an Exception for when there is a bad response returned while fetching token credentials.
     *
     * @param ResponseInterface $response
     * @param string            $customMessage
     *
     * @return CredentialsException
     */
    public static function failedFetchingTokenCredentials(ResponseInterface $response, $customMessage = null)
    {
        if (null === $customMessage) {
            $customMessage = 'Received HTTP status code [%d] with message "%s" when getting token credentials.';
        }

        return static::withResponse($response, $customMessage);
    }

    /**
     * Creates an Exception for when there token credentials are missing from a response payload.
     *
     * @param ResponseInterface $response
     * @param string            $customMessage
     *
     * @return CredentialsException
     */
    public static function failedParsingTokenCredentialsResponse(ResponseInterface $response, $customMessage)
    {
        $message = sprintf('Error "%s" while retrieving token credentials.', $customMessage);

        return static::withResponse($response, $message);
    }
}
