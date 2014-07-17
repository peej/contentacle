<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/merges/:merge
 */
class Merge extends Resource {

    /**
     * @provides contentacle/merge+yaml
     * @provides contentacle/merge+json
     */
    function get($username, $repoName, $branch1, $branch2)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            if ($branch1 == $branch2 || !$repo->hasBranch($branch1) || !$repo->hasBranch($branch2)) {
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
            $response->addData('conflicts', $repo->conflicts($branch1, $branch2));
        }

        return $response;
    }
}
