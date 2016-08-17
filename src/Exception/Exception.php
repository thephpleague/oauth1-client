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
use Psr\Http\Message\ResponseInterface;

abstract class Exception extends BaseException
{
    /**
     * HTTP response associated with Exception
     *
     * @var ResponseInterface
     */
    private $response;

    /**
     * Create a new Exception with the given HTTP response and an option message.
     *
     * @param ResponseInterface $response
     * @param string            $customMessage
     *
     * @return Exception
     */
    public static function withResponse(ResponseInterface $response, $customMessage = '')
    {
        $new = new static($customMessage);
        $new->response = $response;

        return $new;
    }

    /**
     * Retrieve the HTTP Response associated with the resource owner.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
