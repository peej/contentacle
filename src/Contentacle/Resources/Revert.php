<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits/([0-9a-f]{40})/revert
 */
class Revert extends Resource {

    /**
     * Revert back to a previous repo state by creating a new commit that undoes all commits to this point.
     *
     * @method post
     * @accepts application/hal+yaml
     * @accepts application/hal+json
     * @accepts application/yaml
     * @accepts application/json
     * @accepts application/x-www-form-urlencoded
     * @accepts text/plain
     * @accepts
     * @secure
     */
    function post($username, $repoName, $branchName, $sha)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
            $commit = $repo->commit($branchName, $sha);
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }

        if (isset($this->request->data['message']) && is_string($this->request->data['message'])) {
            $commitMessage = $this->request->data['message'];
        } elseif (is_string($this->request->data)) {
            $commitMessage = $this->request->data;
        } else {
            $commitMessage = 'Revert back to “'.$commit['message'].'”';
        }

        if ($revertSha = $repo->revert($commit['sha'], $commitMessage)) {
            $response = $this->response(201);
            $response->location = $this->buildUrl($username, $repoName, $branchName, 'commits', $revertSha);
        } else {
            $response = $this->response(409, 'error');
            $response->addVar('message', 'Conflict');
            $response->addError('conflict', 'Could not revert to this commit.');
            $response->addLink('exit', $this->buildUrl($username, $repoName, $branchName, 'commits'));
        }

        return $response;
    }

}