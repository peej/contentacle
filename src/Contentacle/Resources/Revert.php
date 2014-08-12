<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits/([0-9a-f]{40})/revert
 */
class Revert extends Resource {

    /**
     * @method POST
     */
    function post($username, $repoName, $branchName, $sha)
    {
        $repoRepo = $this->container['repo_repository'];

        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $commit = $repo->commit($branchName, $sha);
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }

        if (isset($this->request->data['message']) && is_string($this->request->data['message'])) {
            $commitMessage = $this->request->data['message'];
        } elseif (is_string($this->request->data)) {
            $commitMessage = $this->request->data;
        } else {
            $commitMessage = 'Undo change '.$commit['sha'];
        }

        if ($revertSha = $repo->revert($commit['sha'], $commitMessage)) {
            $response = new \Tonic\Response(201);
            $response->location = '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/commits/'.$revertSha;
            return $response;
        } else {
            return new \Tonic\Response(400);
        }
        
    }

}