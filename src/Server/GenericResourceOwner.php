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

class GenericResourceOwner implements ResourceOwner, IteratorAggregate
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
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the authorised resource owner.
     *
     * @return mixed|null
     */
    public function getId()
    {
        if (isset($this->response['id'])) {
            return $this->response['id'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->toArray();
    }
}
