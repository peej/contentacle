<?php

namespace Contentacle\Resources;

/**
 * @uri /:username
 */
class User extends Resource {

    /**
     * @method get
     * @template user.html
     */
    function get($username)
    {
        $user = new \Contentacle\Models\User($this->app->container, $username);
        return [200, [
            'user' => $user
        ]];
    }

}