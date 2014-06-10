<?php

namespace Contentacle\Services;

class RepoRepository
{
    private $repoDir, $repoProvider;

    function __construct($repoDir, $repoProvider)
    {
        $this->repoDir = $repoDir;
        $this->repoProvider = $repoProvider;
    }

    function getRepos($username)
    {
        $userDir = $this->repoDir.'/'.$username;
        if (!is_dir($userDir)) {
            throw new \Contentacle\Exceptions\RepoException('User "'.$username.'" does not exist');
        }
        $repos = array();
        foreach (glob($userDir.'/*', GLOB_ONLYDIR) as $repoDir) {
            $repoName = basename($repoDir);
            $repos[$repoName] = $this->getRepo($username, $repoName);
        }
        return $repos;
    }

    function getRepo($username, $repoName)
    {
        $data = array();
        $data['username'] = $username;
        $data['name'] = $repoName;;
        return $this->repoProvider->__invoke($data);
    }
}