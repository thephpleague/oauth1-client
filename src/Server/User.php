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

namespace League\OAuth1\Client\Server;

use IteratorAggregate;

class User implements IteratorAggregate, ResourceOwnerInterface
{
    /**
     * The user's unique ID.
     *
     * @var mixed
     */
    public $uid = null;

    /**
     * The user's nickname (screen name, username etc).
     *
     * @var mixed
     */
    public $nickname = null;

    /**
     * The user's name.
     *
     * @var mixed
     */
    public $name = null;

    /**
     * The user's first name.
     *
     * @var string
     */
    public $firstName = null;

    /**
     * The user's last name.
     *
     * @var string
     */
    public $lastName = null;

    /**
     * The user's email.
     *
     * @var string
     */
    public $email = null;

    /**
     * The user's location.
     *
     * @var string|array
     */
    public $location = null;

    /**
     * The user's description.
     *
     * @var string
     */
    public $description = null;

    /**
     * The user's image URL.
     *
     * @var string
     */
    public $imageUrl = null;

    /**
     * The users' URLs.
     *
     * @var string|array
     */
    public $urls = array();

    /**
     * Any extra data.
     *
     * @var array
     */
    public $extra = array();

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    /**
     * Returns the identifier of the authorised resource owner.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
