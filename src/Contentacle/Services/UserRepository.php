<?php

namespace Contentacle\Services;

class UserRepository
{
    private $repoDir, $userProvider;

    private $users = array();

    function __construct($repoDir, $userProvider)
    {
        $this->repoDir = $repoDir;
        $this->userProvider = $userProvider;
    }

    function getUsers($search = null, $from = 0, $to = 19)
    {
        $users = array();
        $count = 0;

        foreach (glob($this->repoDir.'/'.$search.'*', GLOB_ONLYDIR) as $userDir) {
            if ($count >= $from && $count <= $to) {
                try {
                    $user = $this->getUser(basename($userDir));
                    $users[$user->username] = $user;
                } catch (\Contentacle\Exceptions\ValidationException $e) {}
            }
            $count++;
        }
        return $users;
    }

    function getUser($username)
    {
        if (!isset($this->users[$username])) {
            $data = $this->readProfile($username);
            $this->users[$username] = $this->userProvider->__invoke($data);
        }

        return $this->users[$username];
    }

    /**
     * Given an email address, return the username of the Contentacle user who has that email address.
     * Used for tieing Git users to Contentacle users.
     *
     * Totally in-efficient way of doing this, needs a better solution.
     */
    function getUsernameFromEmail($emailAddress)
    {
        foreach ($this->getUsers() as $user) {
            if ($user->email == $emailAddress) {
                return $user->username;
            }
        }
        return null;
    }

    /**
     * Get the user that is currently signed into the site
     *
     * @param Contentacle\Services\OAuthServer oauth
     * @return Contentacle\Models\User
     */
    function getSignedInUser($oauth)
    {
        if ($oauth->verifyToken()) {
            $username = $oauth->getUsername();
            return $this->getUser($username);
        } elseif (
            isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] != '' &&
            isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] != ''
        ) {
            $user = $this->getUser($_SERVER['PHP_AUTH_USER']);
            if ($user->verifyPassword($_SERVER['PHP_AUTH_PW'])) {
                return $user;
            }
        }
        return null;
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