<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos
 */
class Repos extends Resource {

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username)
    {
        $repoRepo = $this->container['repo_repository'];

        $repos = $repoRepo->getRepos($username);

        return new \Tonic\Response(200, $repos);
    }

}