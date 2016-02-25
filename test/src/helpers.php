<?php

namespace {
    $mockerHeaderRedirect = null;
}

namespace League\OAuth1\Client\Server {
    function header($location)
    {
        global $mockerHeaderRedirect;

        if (isset($mockerHeaderRedirect)) {
            return strcmp($location, $mockerHeaderRedirect);
        }

        return call_user_func_array('\header', func_get_args());
    }
}
