<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch
 */
class Branch extends Resource {

    function get($username, $repoName, $branch)
    {
        $repoRepo = $this->container['repo_repository'];
        $repo = $repoRepo->getRepo($username, $repoName);
        if (!$repo->hasBranch($branch)) {
            throw new \Tonic\NotFoundException;
        }

        return new \Tonic\Response(200, array(
            'repo' => $repo->name,
            'branch' => $branch,
            'documents' => $repo->url.'/branches/'.$branch.'/documents',
            'commits' => $repo->url.'/branches/'.$branch.'/commits'
        ));
    }
}
