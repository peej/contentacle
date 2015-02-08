<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/merges/:merge
 */
class Merge extends Resource
{
    /**
     * Get details of a merge that can be performed on this branch.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field canMerge If the merge can be accomplished successfully.
     * @field conflicts Conflicts which stop these branches from being merged.
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
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
        
        $response = $this->createResponse();

        $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branch1.'/merges/'.$branch2.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/merge');

        try {
            $repo->canMerge($branch1, $branch2);
            $response->addData('canMerge', true);

        } catch (\Git\Exception $e) {
            $response->addData('canMerge', false);
            $conflicts = $repo->conflicts($branch1, $branch2);

            if ($conflicts) {
                $response->addData('conflicts', $conflicts);
            }
        }

        return $response;
    }

    /**
     * Perform a merge between two branches.
     *
     * @method post
     * @response 204 No content
     * @response 400 Bad request
     * @response 404 Not found
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
