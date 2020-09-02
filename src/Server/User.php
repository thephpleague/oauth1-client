<?php

namespace League\OAuth1\Client\Server;

use ArrayIterator;
use IteratorAggregate;

class User implements IteratorAggregate
{
    /**
     * The user's unique ID.
     *
     * @var mixed
     */
    public $uid;

    /**
     * The user's nickname (screen name, username etc).
     *
     * @var mixed
     */
    public $nickname;

    /**
     * The user's name.
     *
     * @var mixed
     */
    public $name;

    /**
     * The user's first name.
     *
     * @var string
     */
    public $firstName;

    /**
     * The user's last name.
     *
     * @var string
     */
    public $lastName;

    /**
     * The user's email.
     *
     * @var string
     */
    public $email;

    /**
     * The user's location.
     *
     * @var string|array
     */
    public $location;

    /**
     * The user's description.
     *
     * @var string
     */
    public $description;

    /**
     * The user's image URL.
     *
     * @var string|null
     */
    public $imageUrl;

    /**
     * The users' URLs.
     *
     * @var string[]
     */
    public $urls = [];

    /**
     * Any extra data.
     *
     * @var array
     */
    public $extra = [];

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator(get_object_vars($this));
    }
}
