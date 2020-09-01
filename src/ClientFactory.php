<?php

namespace League\OAuth1\Client;

interface ClientFactory
{
    public function createClient(ClientConfig $config): Client;
}