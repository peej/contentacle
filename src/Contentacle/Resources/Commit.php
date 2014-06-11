<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits/([0-9a-f]{40})
 */
class Commit extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName, $branchName, $sha)
    {
        $repoRepo = $this->container['repo_repository'];

        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $commit = $repo->commit($branchName, $sha);
            
            $response = new \Contentacle\Responses\Hal(200, $commit);

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/commits/'.$sha.$this->formatExtension());

            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}