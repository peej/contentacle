<?php

namespace Contentacle\Services;

class RepoRepository
{
    private $repoDir, $repoProvider, $yaml;

    function __construct($repoDir, $repoProvider, $yaml)
    {
        $this->repoDir = $repoDir;
        $this->repoProvider = $repoProvider;
        $this->yaml = $yaml;
    }

    function getRepos($username)
    {
        $userDir = $this->repoDir.'/'.$username;
        $repos = array();
        foreach (glob($userDir.'/*', GLOB_ONLYDIR) as $repoDir) {
            $repoName = basename($repoDir);
            $repos[$repoName] = $this->getRepo($username, $repoName);
        }
        return $repos;
    }

    function getRepo($username, $repoName)
    {
        $metadataPath = $this->repoDir.'/'.$username.'/'.$repoName.'/contentacle.yaml';

        if (file_exists($metadataPath)) {
            $data = $this->yaml->decode(file_get_contents($metadataPath), true);
        } else {
            $data = array();
        }

        $data['username'] = $username;
        $data['name'] = $repoName;

        return $this->repoProvider->__invoke($data);
    }
}
