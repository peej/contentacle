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
            try {
                $user = $this->getUser(basename($userDir));
                $users[$user->username] = $user;
            } catch (\Contentacle\Exceptions\ValidationException $e) {}
        }
        return $users;
    }

    function getUser($username)
    {
        $data = $this->readProfile($username);
        return $this->userProvider->__invoke($data);
    }

    function getUsernameFromEmail($emailAddress)
    {
        
    }

    private function readProfile($username)
    {
        $profilePath = $this->repoDir.'/'.$username.'/profile.json';
        if (!file_exists($profilePath)) {
            throw new \Contentacle\Exceptions\UserException('User profile "'.$username.'" not found.');
        }
        return json_decode(file_get_contents($profilePath), true);
    }

    private function writeProfile($data)
    {
        if (!isset($data['username'])) {
            throw new \Contentacle\Exceptions\UserException('Username not provided in profile data');
        }
        if (!isset($data['password'])) {
            throw new \Contentacle\Exceptions\UserException('Password not provided in profile data');
        }

        $userPath = $this->repoDir.'/'.$data['username'];
        $profilePath = $userPath.'/profile.json';

        if (!file_exists($userPath)) {
            mkdir($userPath);
        }
        file_put_contents($profilePath, json_encode($data));
    }

    public function createUser($data)
    {
        $user = $this->userProvider->__invoke($data);
        $user->setPassword($data['password']);
        $this->writeProfile($user->props());
        return $user;
    }

    public function updateUser($user, $data, $patch = false)
    {
        if ($patch) {
            $user->patch($data);
        } else {
            $user->setProps($data);
        }
        $this->writeProfile($user->props());
        return $user;
    }

    private function removeDir($path)
    {
        foreach (scandir($path) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (is_dir($path.DIRECTORY_SEPARATOR.$item)) {
                $this->removeDir($path.DIRECTORY_SEPARATOR.$item);
            } else {
                unlink($path.DIRECTORY_SEPARATOR.$item);
            }
        }
        rmdir($path);
    }

    public function deleteUser($user)
    {
        $this->removeDir($this->repoDir.'/'.$user->username);
    }
}