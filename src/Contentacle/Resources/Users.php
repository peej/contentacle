<?php

namespace Contentacle\Resources;

/**
 * @uri /users
 */
class Users extends Resource
{

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get()
    {
        $userRepo = $this->container['user_repository'];
        $users = $userRepo->getUsers();
        return new \Tonic\Response(200, $users);
    }

}