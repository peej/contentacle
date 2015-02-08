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
     * @field name Name of the branch
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @embeds cont:branch The list of branches.
     */
    function get($username, $repoName)
    {
        try {
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            
            $response = $this->createResponse(200, 'branches');

            $response->addVar('name', $repoName);

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
     * Redirect HTML client to master branch
     *
     * @method get
     * @response 302 Found
     * @provides text/html
    */
    function redirectToMasterBranch($username, $repoName)
    {
        return new \Tonic\Response(302, null, array(
            'Location' => '/users/'.$username.'/repos/'.$repoName.'/branches/master'
        ));
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

            $response = $this->createResponse(201);
            $response->location = '/users/'.$repo->username.'/repos/'.$repo->name.'/branches/'.$data['name'];

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createResponse(400, 'branches');
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
