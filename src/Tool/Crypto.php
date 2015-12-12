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

use RandomLib\Factory;

/**
 * Used to perform cryptographic operations.
 */
class Crypto
{
    /**
     * Generate a random string.
     *
     * @param int $length Optional, defaults to 32
     *
     * @return string
     *
     * @see    OAuth 1.0 RFC 5849 Section 3.3
     */
    public static function nonce($length = 32)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $factory = new Factory();
        $generator = $factory->getMediumStrengthGenerator();

        return $generator->generateString($length, $pool);
    }
}
