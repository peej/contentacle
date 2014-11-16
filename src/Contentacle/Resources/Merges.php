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
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
        
        $response = $this->createHalResponse();

        $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/merges'.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/merges');

        foreach ($repo->branches() as $branch) {
            if ($branchName != $branch) {
                $response->addLink(
                    'cont:merge',
                    '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/merges/'.$branch.$this->formatExtension(),
                    false,
                    $branch
                );
            }
        }

        return $response;

    }
}
