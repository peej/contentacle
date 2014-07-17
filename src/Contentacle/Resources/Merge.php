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
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            if (!$repo->hasBranch($branch1) || !$repo->hasBranch($branch2)) {
                throw new \Tonic\NotFoundException;
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
        
        $response = new \Contentacle\Responses\Hal();

        $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branch1.'/merges/'.$branch2.$this->formatExtension());

        $canMerge = $repo->canMerge($branch1, $branch2);
        $response->addData('canMerge', $canMerge);
        if (!$canMerge) {
            $response->addData('conflicts', $repo->mergeConflicts($branch2));
        }

        $response->contentType = 'contentacle/merge';
        return $response;
    }
}
