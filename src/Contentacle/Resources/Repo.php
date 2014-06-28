<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repoName
 */
class Repo extends Resource
{
    private function response($repo)
    {
        $response = new \Contentacle\Responses\Hal(200, $repo);

        $response->addLink('self', '/users/'.$repo->username.'/repos/'.$repo->name.$this->formatExtension());
        $response->addLink('cont:branches', '/users/'.$repo->username.'/repos/'.$repo->name.'/branches'.$this->formatExtension());
        $response->addForm('cont:edit-repo', 'patch', null, 'application/json-patch', 'Edit the repo');
        $response->addForm('cont:delete-repo', 'delete', null, 'Remove the repo');

        if ($this->embed) {
            foreach ($repo->branches() as $branchName) {
                $response->embed('branches', $this->getChildResource('\Contentacle\Resources\Branch', array($repo->username, $repo->name, $branchName)));
            }
        }
        
        return $response;
    }

    /**
     * @provides contentacle/repo+yaml
     * @provides contentacle/repo+json
     */
    function get($username, $repoName)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            return $this->response($repo);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * @method patch
     * @accepts application/json-patch+yaml
     * @accepts application/json-patch+json
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function updateRepo($username, $repoName)
    {
        $repoRepo = $this->container['repo_repository'];
        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $repo->patch($this->request->getData());
            $repo->writeMetadata();
            $response = $this->response($repo);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = new \Contentacle\Responses\Hal(400);
            $response->contentType = 'application/hal';
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        }

        return $response;
    }
}
