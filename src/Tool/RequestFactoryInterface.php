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

/**
 * Used to produce PSR-7 Request instances.
 *
 * @link https://github.com/guzzle/guzzle/pull/1101
 */
interface RequestFactoryInterface
{
    /**
     * Creates a PSR-7 Request instance.
     *
     * @param null|string                     $method  HTTP method for the request.
     * @param null|string                     $uri     URI for the request.
     * @param array                           $headers Headers for the message.
     * @param string|resource|StreamInterface $body    Message body.
     * @param string                          $version HTTP protocol version.
     *
     * @return Psr\Http\Message\RequestInterface
     */
    public static function getRequest(
        $method,
        $uri,
        array $headers = array(),
        $body = null,
        $version = '1.1'
    );
}
