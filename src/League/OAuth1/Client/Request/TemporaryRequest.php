<?php

namespace League\OAuth1\Client\Request;

interface RequestInterface
{
    public function handleResponse($response = array());

    public function prepRequestParams($defaultParams, $params);
}