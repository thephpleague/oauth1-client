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

class ConfigurationException extends Exception
{
    /**
     * Handles an invalid response type.
     *
     * @param  string  $responseType
     *
     * @throws ConfigurationException
     */
    public static function handleInvalidResponseType($responseType)
    {
        return new static("Invalid response type [{$responseType}].");
    }

    /**
     * Handles a missing required option.
     *
     * @param  string  $requiredOption
     *
     * @throws ConfigurationException
     */
    public static function handleMissingRequiredOption($requiredOption)
    {
        return new static("Expected {$requiredOption} option to create client credentials.");
    }

    /**
     * Handles a temporary identifier mismatch.
     *
     * @throws ConfigurationException
     */
    public static function handleTemporaryIdentifierMismatch()
    {
        return new static('Temporary identifier passed back by server does not match
            that of stored temporary credentials. Potential man-in-the-middle.');
    }
}
