<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch
 */
class Branch extends Resource {

    /**
     * @provides contentacle/branch+yaml
     * @provides contentacle/branch+json
     */
    function get($username, $repoName, $branchName)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            if (!$repo->hasBranch($branchName)) {
                throw new \Git\Exception;
            }

            $response = new \Contentacle\Responses\Hal(200, array(
                'name' => $branchName,
                'repo' => $repo->name,
                'username' => $repo->username,
            ));

            $branchUrl = '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName;

            $response->addLink('self', $branchUrl.$this->formatExtension());
            $response->addForm('cont:edit-branch', 'patch', null, 'application/json-patch', 'Rename the branch');
            $response->addForm('cont:delete-branch', 'delete', null, null, 'Remove the branch');
            $response->addLink('cont:commits', $branchUrl.'/commits'.$this->formatExtension());
            $response->addLink('cont:documents', $branchUrl.'/documents'.$this->formatExtension());
            
            $response->contentType = 'contentacle/branch';
            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}
