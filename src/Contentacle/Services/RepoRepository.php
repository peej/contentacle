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

    public function getRepos($username)
    {
        $userDir = $this->repoDir.'/'.$username;
        if (!is_dir($userDir)) {
            throw new \Contentacle\Exceptions\RepoException('User "'.$username.'" does not exist');
        }
        $repos = array();
        foreach (glob($userDir.'/*', GLOB_ONLYDIR) as $repoDir) {
            $repoName = basename($repoDir);
            if (substr($repoName, -4) == '.git') {
                $repoName = substr($repoName, 0, -4);
            }
            $repos[$repoName] = $this->getRepo($username, $repoName);
        }
        return $repos;
    }

    public function getRepo($username, $repoName)
    {
        $repoDir = $this->repoDir.'/'.$username.'/'.$repoName.'.git';
        if (!is_dir($repoDir)) {
            throw new \Contentacle\Exceptions\RepoException('Repo "'.$username.'/'.$repoName.'" does not exist');
        }
        $data = array(
            'username' => $username,
            'name' => $repoName
        );
        return $this->repoProvider->__invoke($data);
    }

    public function createRepo($user, $data)
    {
        $data['username'] = $user->username;

        $repo = $this->repoProvider->__invoke($data);
        $repo->writeMetadata('master');

        return $repo;
    }
}