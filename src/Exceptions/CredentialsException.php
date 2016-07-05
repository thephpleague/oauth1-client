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

namespace League\OAuth1\Client\Exceptions;

use GuzzleHttp\Exception\BadResponseException;

class CredentialsException extends Exception
{
    /**
     * Handles an error in parsing credentials from a given response.
     *
     * @param string $type Type of credentials
     *
     * @return static
     */
    public static function responseParseError($type)
    {
        return new static(sprintf(
            'Unable to parse "%s" credentials response.',
            $type
        ));
    }

    /**
     * Handles a bad response coming back when getting temporary credentials.
     *
     * @param BadResponseException $e
     *
     * @return static
     */
    public static function temporaryCredentialsBadResponse(BadResponseException $e)
    {
        $response = $e->getResponse();
        $body = $response->getBody();
        $statusCode = $response->getStatusCode();

        return new static(sprintf(
            'Received HTTP status code [%d] with message "%s" when getting temporary credentials.',
            $statusCode,
            $body
        ));
    }

    /**
     * Handles an error in retrieving credentials from a resource.
     *
     * @param string $message
     *
     * @return static
     */
    public static function temporaryCredentialsRetrievalError($customMessage = null)
    {
        $message = 'OAuth keys missing from valid temporary credentials response payload.';

        if (isset($customMessage)) {
            $message = sprintf('Error "%s" in retrieving temporary credentials.', $customMessage);
        }

        return new static($message);
    }

    /**
     * Handles a bad response coming back when getting token credentials.
     *
     * @param BadResponseException $e
     *
     * @return static
     */
    public static function tokenCredentialsBadResponse(BadResponseException $e)
    {
        $response = $e->getResponse();
        $body = $response->getBody();
        $statusCode = $response->getStatusCode();

        return new static(sprintf(
            'Received HTTP status code [%d] with message "%s" when getting token credentials.',
            $statusCode,
            $body
        ));
    }

    /**
     * Handles an error in retrieving credentials from a resource.
     *
     * @param string $error Error message from resource
     *
     * @return static
     */
    public static function tokenCredentialsRetrievalError($error)
    {
        return new static(sprintf(
            'Error "%s" in retrieving token credentials.',
            $error
        ));
    }
}
