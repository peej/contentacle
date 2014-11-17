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
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            
            $response = $this->createHalResponse();

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches'.$this->formatExtension());
            $response->addLink('cont:doc', '/rels/branches');

            if ($this->embed) {
                foreach ($repo->branches() as $branchName) {
                    $response->embed('cont:branch', $this->getChildResource('\Contentacle\Resources\Branch', array($username, $repoName, $branchName)));
                }
            }

            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * @method post
     * @accepts application/hal+yaml
     * @accepts application/hal+json
     * @accepts application/yaml
     * @accepts application/json
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function createBranch($username, $repoName)
    {
        $repoRepo = $this->getRepoRepository();

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

            $response = $this->createHalResponse(201);
            $response->location = '/users/'.$repo->username.'/repos/'.$repo->name.'/branches/'.$data['name'];

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createHalResponse(400);
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
