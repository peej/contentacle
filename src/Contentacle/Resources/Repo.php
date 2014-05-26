<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repoName
 */
class Repo extends Resource
{

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username, $repoName)
    {
        $repoRepo = $this->container['repo_repository'];

        $repo = $repoRepo->getRepo($username, $repoName);
        $repo->loadBranches();
        
        return new \Tonic\Response(200, $repo);
    }
}
