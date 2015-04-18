<?php

namespace Contentacle\Services;

class RepoRepository
{
    private $repoDir, $repoProvider;

    private $repos = array();

    function __construct($repoDir, $repoProvider)
    {
        $this->repoDir = $repoDir;
        $this->repoProvider = $repoProvider;
    }

    public function getRepos($username, $search = null)
    {
        $userDir = $this->repoDir.'/'.$username;
        if (!is_dir($userDir)) {
            throw new \Contentacle\Exceptions\RepoException('User "'.$username.'" does not exist');
        }
        $repos = array();
        foreach (glob($userDir.'/'.$search.'*', GLOB_ONLYDIR) as $repoDir) {
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
        if (!isset($this->repos[$username][$repoName])) {
            $normalRepoDir = $this->repoDir.'/'.$username.'/'.$repoName.'.git';
            $bareRepoDir = $this->repoDir.'/'.$username.'/'.$repoName.'/.git';
            if (!is_dir($normalRepoDir) && !is_dir($bareRepoDir)) {
                throw new \Contentacle\Exceptions\RepoException('Repo "'.$username.'/'.$repoName.'" does not exist');
            }
            $data = array(
                'username' => $username,
                'name' => $repoName
            );
            $this->repos[$username][$repoName] = $this->repoProvider->__invoke($data);
        }

        return $this->repos[$username][$repoName];
    }

    public function createRepo($user, $data)
    {
        $data['username'] = $user->username;

        if (!isset($data['name']) || $data['name'] == '') {
            $e = new \Contentacle\Exceptions\ValidationException('Can not create a repo without a name');
            $e->errors[] = 'name';
            throw $e;
        }

        try {
            $this->getRepo($data['username'], $data['name']);
            $repoExists = true;
        } catch (\Contentacle\Exceptions\RepoException $e) {
            $repoExists = false;
        }

        if ($repoExists) {
            throw new \Contentacle\Exceptions\RepoException('Repo already exists, can\'t create again');
        }

        if (!isset($data['description']) || $data['description'] == '') {
            $data['description'] = 'No description';
        }

        $readme = $data['name']."\n".
            str_repeat('=', strlen($data['name']))."\n".
            "\n".
            $data['description']."\n";

        $repo = $this->repoProvider->__invoke($data);
        $repo->writeMetadata();
        $repo->createDocument('master', 'README.md', $readme, 'Initial commit');

        return $repo;
    }
}