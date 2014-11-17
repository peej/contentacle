<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/merges/:merge
 */
class Merge extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName, $branch1, $branch2)
    {
        try {
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            if ($branch1 == $branch2 || !$repo->hasBranch($branch1) || !$repo->hasBranch($branch2)) {
                throw new \Tonic\NotFoundException;
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
        
        $response = $this->createHalResponse();

        $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branch1.'/merges/'.$branch2.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/merge');

        try {
            $repo->canMerge($branch1, $branch2);
            $response->addData('canMerge', true);

        } catch (\Git\Exception $e) {
            $response->addData('canMerge', false);
            $response->addData('conflicts', $repo->conflicts($branch1, $branch2));
        }

        return $response;
    }

    /**
     * @method post
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function post($username, $repoName, $branch1, $branch2)
    {
        try {
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            if ($branch1 == $branch2 || !$repo->hasBranch($branch1) || !$repo->hasBranch($branch2)) {
                throw new \Tonic\NotFoundException;
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
        try {
            $repo->canMerge($branch1, $branch2);
            $repo->merge($branch1, $branch2);
            return new \Tonic\Response(204);
        } catch (\Git\Exception $e) {
            return new \Tonic\Response(400);
        }
    }
}
