<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch
 */
class Branch extends Resource {

    /**
     * @provides text/yaml
     * @provides application/json
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
            $response->addForm('rename', 'put', array('contentacle/branch+yaml', 'contentacle/branch+json'), 'Rename the branch');
            $response->addForm('delete', 'delete', null, 'Remove the branch');
            $response->addLink('commits', $branchUrl.'/commits'.$this->formatExtension());
            $response->addLink('documents', $branchUrl.'/documents'.$this->formatExtension());
            
            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}
