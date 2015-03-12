<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches
 */
class Branches extends Resource
{
    /**
     * Get a list of branches.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
     * @field name Name of the branch
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @embeds cont:branch The list of branches.
     */
    function get($username, $repoName)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
            
            $response = $this->response(200, 'branches');

            $response->addData('username', $repo->username);
            $response->addData('repo', $repo->name);

            $response->addLink('self', $this->buildUrlWithFormat($username, $repoName, true));
            $response->addLink('cont:doc', '/rels/branches');
            $response->addLink('cont:user', $this->buildUrlWithFormat($username));
            $response->addLink('cont:repo', $this->buildUrlWithFormat($username, $repoName));

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
     * Create a branch.
     *
     * @method post
     * @accepts application/hal+yaml
     * @accepts application/hal+json
     * @accepts application/yaml
     * @accepts application/json
     * @field name Name of the branch
     * @secure
     * @response 201 Created
     * @response 400 Bad request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @header Location The URL of the created branch.
     * @embeds cont:error A list of errored fields.
     */
    public function createBranch($username, $repoName)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
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

            $response = $this->response(201);
            $response->location = '/users/'.$repo->username.'/repos/'.$repo->name.'/branches/'.$data['name'];

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->response(400, 'branches');
            foreach ($e->errors as $field) {
                $response->embed('cont:error', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        }

        return $response;
    }
}
