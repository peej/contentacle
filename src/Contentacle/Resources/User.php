<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username
 */
class User extends Resource {

    function get($username)
    {
        return [200, $username];
    }

}