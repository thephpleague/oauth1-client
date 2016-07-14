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

class ConfigurationException extends Exception
{
    /**
     * Handles an invalid response type.
     *
     * @param string $responseType
     *
     * @return static
     */
    public static function invalidResponseType($responseType)
    {
        return new static(sprintf(
            'Invalid response type "%s".',
            $responseType
        ));
    }

    /**
     * Handles a missing required option.
     *
     * @param string $requiredOption
     *
     * @return static
     */
    public static function missingRequiredOption($requiredOption)
    {
        return new static(sprintf(
            'Expected "%s" option to create client credentials.',
            $requiredOption
        ));
    }

    /**
     * Handles a temporary identifier mismatch.
     *
     * @return ConfigurationException
     */
    public static function temporaryIdentifierMismatch()
    {
        return new static(
            'Temporary identifier passed back by server does not match that of '.
            'stored temporary credentials. Potential man-in-the-middle.'
        );
    }
}
