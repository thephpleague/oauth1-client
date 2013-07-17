<?php

namespace League\OAuth1\Client\Request;

class TemporaryRequest implements RequestInterface
{
    public function handleResponse($response = array())
    {
        var_dump($response);die();
    }

    public function prepRequestParams($defaultParams, $params)
    {
        var_dump(func_get_args())die();
    }
}