<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits/([0-9a-f]{40})
 */
class Commit extends Resource {

    /**
     * @method get
     * @provides contentacle/commit+yaml
     * @provides contentacle/commit+json
     */
    function get($username, $repoName, $branchName, $sha)
    {
        $repoRepo = $this->getRepoRepository();

        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $commit = $repo->commit($branchName, $sha);

            $response = $this->createHalResponse(200);
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