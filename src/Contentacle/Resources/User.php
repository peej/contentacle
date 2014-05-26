<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username
 */
class User extends Resource
{

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username)
    {
        $userRepo = $this->container['user_repository'];
        $repoRepo = $this->container['repo_repository'];

        $user = $userRepo->getUser($username);
        $user->loadRepos($repoRepo);

        return new \Tonic\Response(200, $user);
    }

}