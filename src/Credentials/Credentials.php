<?php
/**
 * This file is part of the league/oauth1-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */

namespace League\OAuth1\Client\Credentials;

abstract class Credentials implements CredentialsInterface
{
    /**
     * The credentials identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * The credentials secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * {@inheritDoc}
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }
}
