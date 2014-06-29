<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches
 */
class Branches extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            
            $response = new \Contentacle\Responses\Hal();

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches'.$this->formatExtension());
            $response->addForm('cont:create-branch', 'post', null, 'contentacle/branch', 'Create a branch');

            if ($this->embed) {
                foreach ($repo->branches() as $branchName) {
                    $response->embed('branches', $this->getChildResource('\Contentacle\Resources\Branch', array($username, $repoName, $branchName)));
                }
            }
            
            $response->contentType = 'contentacle/branches';
            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * @method post
     * @accepts contentacle/branch+yaml
     * @accepts contentacle/branch+json
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function createBranch($username, $repoName)
    {
        $repoRepo = $this->container['repo_repository'];

        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $data = $this->request->getData();

            if (!isset($data['name'])) {
                $e = new \Contentacle\Exceptions\ValidationException;
                $e->errors = array('name');
                throw $e;
            }

            try {
                $repo->createBranch($data['name']);
            } catch (\Contentacle\Exceptions\RepoException $e) {
                $e = new \Contentacle\Exceptions\ValidationException;
                $e->errors = array('name');
                throw $e;
            }

            $response = new \Contentacle\Responses\Hal(201);
            $response->location = '/users/'.$repo->username.'/repos/'.$repo->name.'/branches/'.$data['name'];

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
