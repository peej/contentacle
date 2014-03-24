<?php

namespace Contentacle\Services;

class UserRepository {
    
    private $container;

    function __construct($container)
    {
        $this->container = $container;
    }

    function getUsers()
    {
        $repoDir = $this->container['repo_dir'];
        $users = array();
        foreach (glob($repoDir.'/*', GLOB_ONLYDIR) as $userDir) {
            if (file_exists($userDir.'/profile.json')) {
                $userDetails = json_decode(file_get_contents($userDir.'/profile.json'), true);
                $user = new \Contentacle\Models\User($userDetails);
                $users[$user->username] = $user;
            }
        }
        return $users;
    }
}