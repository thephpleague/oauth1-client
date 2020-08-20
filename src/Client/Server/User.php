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
     * @var string
     */
    public $imageUrl;

    /**
     * The users' URLs.
     *
     * @var array
     */
    public $urls = [];

    /**
     * Any extra data.
     *
     * @var array
     */
    public $extra = [];

    /**
     * Set a property on the user.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value): void
    {
        if (isset($this->{$key})) {
            $this->{$key} = $value;
        }
    }

    public function __isset($key): bool
    {
        return isset($this->{$key});
    }

    /**
     * Get a property from the user.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->{$key} ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this);
    }
}
