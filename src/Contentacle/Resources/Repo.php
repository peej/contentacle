<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repoName
 */
class Repo extends Resource
{
    /**
     * Generate a successful response.
     */
    private function buildResponse($repo)
    {
        $response = $this->createResponse(200, 'repo');

        $response->addData($repo);
        $response->addLink('self', '/users/'.$repo->username.'/repos/'.$repo->name.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/repo');
        $response->addLink('cont:branches', '/users/'.$repo->username.'/repos/'.$repo->name.'/branches'.$this->formatExtension());

        if ($this->embed) {
            foreach ($repo->branches() as $branchName) {
                $response->embed('cont:branch', $this->getChildResource('\Contentacle\Resources\Branch', array($repo->username, $repo->name, $branchName)));
            }
        }

        return $response;
    }

    /**
     * Get a repository
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field username The username of the repo owner.
     * @field name The short name of the repo.
     * @field title The display name of the repo.
     * @field description A description of the repo.
     * @links self Link to itself.
     * @links cont:doc Link to this documentation.
     * @links cont:branches Link to the repositories branches.
     * @embeds cont:branch A list of the repositories branches.
     */
    function get($username, $repoName)
    {
        try {
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            return $this->buildResponse($repo);

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
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
     * Update a repositories metadata.
     *
     * @method patch
     * @field username The username of the repo owner.
     * @field name The short name of the repo.
     * @field title The display name of the repo.
     * @field description A description of the repo.
     * @accepts application/json-patch+yaml
     * @accepts application/json-patch+json
     * @secure
     * @response 200 OK
     * @response 400 Bad Request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @links self Link to itself.
     * @links cont:doc Link to this documentation.
     * @links cont:branches Link to the repositories branches.
     * @embeds cont:branch A list of the repositories branches.
     * @embeds cont:error A list of errored fields.
     */
    public function patchRepo($username, $repoName)
    {
        $repoRepo = $this->getRepoRepository();
        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $repo->patch($this->request->getData());
            $repo->writeMetadata();
            $response = $this->buildResponse($repo);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createResponse(400, 'repo');
            $response->contentType = 'application/hal';
            foreach ($e->errors as $field) {
                $response->embed('cont:error', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }

        return $response;
    }

    /**
     * Update a repositories metadata.
     *
     * @method put
     * @field username The username of the repo owner.
     * @field name The short name of the repo.
     * @field title The display name of the repo.
     * @field description A description of the repo.
     * @accepts application/hal+json
     * @accepts application/hal+yaml
     * @accepts application/yaml
     * @accepts application/json
     * @secure
     * @response 200 OK
     * @response 400 Bad Request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @links self Link to itself.
     * @links cont:doc Link to this documentation.
     * @links cont:branches Link to the repositories branches.
     * @embeds cont:branch A list of the repositories branches.
     * @embeds cont:error A list of errored fields.
     */
    public function updateRepo($username, $repoName)
    {
        $repoRepo = $this->getRepoRepository();
        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $repo->setProps($this->request->getData());
            $repo->writeMetadata();
            $response = $this->buildResponse($repo);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createResponse(400, 'repo');
            $response->contentType = 'application/hal';
            foreach ($e->errors as $field) {
                $response->embed('cont:error', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }

        return $response;
    }

    /**
     * Delete a repository.
     *
     * @method delete
     * @secure
     * @response 204 No content
     * @response 400 Bad request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @embeds cont:error A list of errored fields.
     */
    public function deleteRepo($username, $repoName)
    {
        $repoRepo = $this->getRepoRepository();
        
        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $repo->delete();
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
        
        return new \Tonic\Response(204);
    }
}
