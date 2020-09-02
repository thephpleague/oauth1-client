<?php

namespace League\OAuth1\Client;

use ArrayAccess;

class User implements ArrayAccess
{
    /** @var string|int|null */
    private $id;

    /** @var string|null */
    private $username;

    /** @var string|null */
    private $email;

    /** @var array */
    private $metadata = [];

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string|null $id
     */
    public function setId($id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): User
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): User
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->metadata[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->metadata[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->metadata[$offset]);
    }
}
