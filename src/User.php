<?php

namespace League\OAuth1\Client;

/**
 * @todo Implement a generic user class…
 */
class User
{
    /** @var string|int|null */
    private $id;

    /** @var string|null */
    private $username;

    /** @var string|null */
    private $email;

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
}