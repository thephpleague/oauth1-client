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
namespace League\OAuth1\Client\Credentials;

use League\OAuth1\Client\Exceptions\ConfigurationException;

class ClientCredentials extends Credentials
{
    /**
     * The credentials callback URI.
     *
     * @var string
     */
    protected $callbackUri;

    /**
     * Create a new client credentials instance.
     *
     * @param string $identifier
     * @param string $secret
     */
    public function __construct($identifier, $secret, $callbackUri = null)
    {
        parent::__construct($identifier, $secret);

        if ($callbackUri !== null) {
            $this->callbackUri = (string) $callbackUri;
        }
    }

    /**
     * Attempts to create client credentials from given options.
     *
     * @param  array   $options
     *
     * @return ClientCredentials
     * @throws League\OAuth1\Client\Exceptions\ConfigurationException
     */
    public static function createFromOptions(array $options)
    {
        array_map(function ($required) use ($options) {
            if (!array_key_exists($required, $options)) {
                throw ConfigurationException::handleMissingRequiredOption($required);
            }
        }, ['identifier', 'secret', 'callbackUri']);

        return new static(
            $options['identifier'],
            $options['secret'],
            $options['callbackUri']
        );
    }

    /**
     * Gets currently configured callback uri.
     *
     * @return string
     */
    public function getCallbackUri()
    {
        return $this->callbackUri;
    }
}
