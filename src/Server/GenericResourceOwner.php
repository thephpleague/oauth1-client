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

namespace League\OAuth1\Client\Server;

use IteratorAggregate;

class GenericResourceOwner implements IteratorAggregate, ResourceOwnerInterface
{
    /**
     * Resource owner response data.
     *
     * @var array
     */
    protected $response;

    /**
     * Creates a new generic resource owner.
     *
     * @param array
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->toArray();
    }

    /**
     * Returns the identifier of the authorised resource owner.
     *
     * @return mixed|null
     */
    public function getId()
    {
        return $this->response['id'] ?: null;
    }

    /**
     * Returns all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
