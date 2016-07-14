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

use Exception as BaseException;
use GuzzleHttp\Exception\BadResponseException;

class Exception extends BaseException
{
    /**
     * Handles http response exception encountered when attempting to retrieve
     * user details.
     *
     * @param GuzzleHttp\Exception\BadResponseException $e
     *
     * @throws Exception
     */
    public static function handleUserDetailsRetrievalException(BadResponseException $e)
    {
        $response = $e->getResponse();
        $body = $response->getBody();
        $statusCode = $response->getStatusCode();

        throw new static(
            "Received error [$body] with status code [$statusCode] when retrieving token credentials."
        );
    }
}
