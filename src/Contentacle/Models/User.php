<?php

namespace Contentacle\Models;

class User {

    const PASSWORD_SALT = 's3cr3tDonk3y';
    
    private $container;

    public $username;
    public $password;
    public $name;
    public $repos;

    function __construct($container, $username)
    {
        $this->container = $container;

        $userData = $this->container['store']->load($username);

        $this->username = strtolower($username);
        $this->password = $userData->password;
        $this->name = $userData->name;

        $this->repos = $this->loadRepos($username);
    }

    private function loadRepos($username)
    {
        $repoDir = $this->container['repo_dir'].'/'.strtolower($username);
        $repoGlob = $repoDir.'/*';
        
        $repos = array();
        foreach (glob($repoGlob, GLOB_ONLYDIR) as $repoPath) {
            $repo = new \Contentacle\Models\Repo($repoPath);
            $repos[$repo->name] = $repo;
        }
        return $repos;
    }

    public function getRepo($name)
    {
        return isset($this->repos[$name]) ? $this->repos[$name] : null;
    }

    public function validate($password)
    {
        return sha1($password.$this::PASSWORD_SALT) === $this->password;
    }

    public function auth($cookie = null)
    {
        if (!$cookie) {
            $cookie = $_COOKIE;
        }
        $clientIp = isset($_SERVER['REMOTE_IP']) ? $_SERVER['REMOTE_IP'] : '127.0.0.1';
        return
            isset($cookie['username']) &&
            isset($cookie['session']) &&
            $cookie['username'] === $this->username &&
            $cookie['session'] === sha1($this->username.$this->password.$clientIp)
        ;
    }
}
