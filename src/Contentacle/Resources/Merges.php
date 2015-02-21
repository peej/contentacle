<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/merges
 */
class Merges extends Resource
{
    /**
     * Get a list of merges that can be performed on this branch.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:merge Link to merges that can be performed.
     */
    function get($username, $repoName, $branchName)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
        
        $response = $this->response();

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
