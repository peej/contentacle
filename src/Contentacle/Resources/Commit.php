<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits/([0-9a-f]{40})
 */
class Commit extends Resource {

    /**
     * @provides contentacle/commit+yaml
     * @provides contentacle/commit+json
     */
    function get($username, $repoName, $branchName, $sha)
    {
        $repoRepo = $this->container['repo_repository'];

        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $commit = $repo->commit($branchName, $sha);
            
            $response = new \Contentacle\Responses\Hal(200, $commit);

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/commits/'.$sha.$this->formatExtension());
            $response->addLink('cont:user', '/users/'.$commit['username'].$this->formatExtension());

            $response->contentType = 'contentacle/commit+yaml';
            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}