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
     * @param  string  $type Type of credentials
     *
     * @throws CredentialsException
     */
    public static function handleResponseParseError($type)
    {
        return new static("Unable to parse $type credentials response.");
    }

    /**
     * Handles a bad response coming back when getting temporary credentials.
     *
     * @param GuzzleHttp\Exception\BadResponseException $e
     *
     * @throws CredentialsException
     */
    public static function handleTemporaryCredentialsBadResponse(BadResponseException $e)
    {
        $response = $e->getResponse();
        $body = $response->getBody();
        $statusCode = $response->getStatusCode();

        return new static(
            "Received HTTP status code [$statusCode] with message \"$body\" when getting temporary credentials."
        );
    }

    /**
     * Handles an error in retrieving credentials from a resource.
     *
     * @throws CredentialsException
     */
    public static function handleTemporaryCredentialsRetrievalError()
    {
        return new static('Error in retrieving temporary credentials.');
    }

    /**
     * Handles a bad response coming back when getting token credentials.
     *
     * @param GuzzleHttp\Exception\BadResponseException $e
     *
     * @throws CredentialsException
     */
    public static function handleTokenCredentialsBadResponse(BadResponseException $e)
    {
        $response = $e->getResponse();
        $body = $response->getBody();
        $statusCode = $response->getStatusCode();

        return new static(
            "Received HTTP status code [$statusCode] with message \"$body\" when getting token credentials."
        );
    }

    /**
     * Handles an error in retrieving credentials from a resource.
     *
     * @param  string  $error Error message from resource
     *
     * @throws CredentialsException
     */
    public static function handleTokenCredentialsRetrievalError($error)
    {
        return new static("Error [{$error}] in retrieving token credentials.");
    }
}
