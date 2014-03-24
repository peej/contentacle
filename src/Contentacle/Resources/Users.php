<?php

namespace Contentacle\Resources;

/**
 * @uri /users
 */
class Users extends Resource {

    function get()
    {
        $userRepo = $this->app->container['user_repository'];
        $users = $userRepo->getUsers();
        return new \Tonic\Response(200, $users);
    }

}