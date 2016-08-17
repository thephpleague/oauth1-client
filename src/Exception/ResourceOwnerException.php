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

class ResourceOwnerException extends Exception
{
    /**
     * Creates an Exception when fetching user details failed.
     *
     * @param ResponseInterface $response
     * @param string            $customMessage
     *
     * @return ResourceOwnerException
     */
    public static function failedFetchingUserDetails(ResponseInterface $response, $customMessage = null)
    {
        if (null !== $customMessage) {
            $customMessage = 'Failed to fetch user details.';
        }

        return static::withResponse($response, $customMessage);
    }

    /**
     * Creates an Exception when parsing user details failed.
     *
     * @param ResponseInterface $response
     * @param string            $customMessage
     *
     * @return ResourceOwnerException
     */
    public static function failedParsingUserDetailsResponse(ResponseInterface $response, $customMessage = null)
    {
        if (null === $customMessage) {
            $customMessage = 'Failed to parse user details response.';
        }

        return static::withResponse($response, $customMessage);
    }
}
