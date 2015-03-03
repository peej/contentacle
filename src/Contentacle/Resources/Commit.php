<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits/([0-9a-f]{40})
 */
class Commit extends Resource
{
    /**
     * Get a commit.
     *
     * @method get
     * @response 200 OK
     * @field sha Hash of this commit
     * @field parents Hash(es) of parent commit(s)
     * @field message Commit message
     * @field date Date of the commit (as unix timestamp)
     * @field username Username of committer
     * @field author Name of committer
     * @field email Email of committer
     * @field files Paths of changed documents within the commit
     * @field diff The changes to documents within the commit
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:user Link to the creator of this commit.
     * @links cont:document Link to documents within this commit.
     */
    function get($username, $repoName, $branchName, $sha)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
            $commit = $repo->commit($branchName, $sha);

            $response = $this->response(200, 'commit');
            $response->addData($commit);

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/commits/'.$sha.$this->formatExtension());
            $response->addLink('cont:doc', '/rels/commit');
            $response->addLink('cont:user', '/users/'.$commit['username'].$this->formatExtension());

            if (isset($commit['files'])) {
                foreach ($commit['files'] as $filename) {
                    $response->addLink('cont:document', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents/'.$filename);
                }
            }

            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}