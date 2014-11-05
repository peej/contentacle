<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/merges
 */
class Merges extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName, $branchName)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
        
        $response = new \Contentacle\Responses\Hal();

        $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/merges'.$this->formatExtension());

        foreach ($repo->branches() as $branch) {
            if ($branchName != $branch) {
                $response->addLink($branch, '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/merges/'.$branch.$this->formatExtension());
            }
        }

        return $response;

    }
}
