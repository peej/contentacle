<?php

namespace Contentacle\Services;

class UserRepository
{
    private $repoDir, $userProvider;

    function __construct($repoDir, $userProvider)
    {
        $this->repoDir = $repoDir;
        $this->userProvider = $userProvider;
    }

    function getUsers()
    {
        $users = array();
        foreach (glob($this->repoDir.'/*', GLOB_ONLYDIR) as $userDir) {
            $user = $this->getUser(basename($userDir));
            $users[$user->username] = $user;
        }
        return $users;
    }

    function getUser($username)
    {
        $profilePath = $this->repoDir.'/'.$username.'/profile.json';

        if (!file_exists($profilePath)) {
            throw new \Contentacle\Exceptions\UserException('User profile "'.$username.'" not found.');
        }
        $data = json_decode(file_get_contents($profilePath), true);
        return $this->userProvider->__invoke($data);
    }

    function getUsernameFromEmail($emailAddress)
    {
        
    }
}