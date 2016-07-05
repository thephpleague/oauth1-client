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

namespace League\OAuth1\Client\Tool;

use GuzzleHttp\Psr7\Request;

/**
 * Used to produce PSR-7 Request instances.
 *
 * @link https://github.com/guzzle/guzzle/pull/1101
 */
class RequestFactory implements RequestFactoryInterface
{
    /**
     * Creates a PSR-7 Request instance.
     *
     * @param null|string                     $method  HTTP method for the request.
     * @param null|string                     $url     URI for the request.
     * @param array                           $headers Headers for the message.
     * @param string|resource|StreamInterface $body    Message body.
     * @param string                          $version HTTP protocol version.
     *
     * @return Request
     */
    public static function getRequest(
        $method,
        $url,
        array $headers = array(),
        $body = null,
        $version = '1.1'
    ) {
        return new Request($method, $url, $headers, $body, $version);
    }
}
