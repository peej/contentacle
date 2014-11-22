<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repoName
 */
class Repo extends Resource
{
    private function response($repo)
    {
        $response = $this->createHalResponse(200);
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
     * @method get
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName)
    {
        try {
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            return $this->response($repo);

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        } catch (\Git\Exception $e) {
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
    public function patchRepo($username, $repoName)
    {
        $repoRepo = $this->getRepoRepository();
        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $repo->patch($this->request->getData());
            $repo->writeMetadata();
            $response = $this->response($repo);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createHalResponse(400);
            $response->contentType = 'application/hal';
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
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
     * @method put
     * @accepts application/hal+json
     * @accepts application/hal+yaml
     * @accepts application/yaml
     * @accepts application/json
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function updateRepo($username, $repoName)
    {
        $repoRepo = $this->getRepoRepository();
        try {
            $repo = $repoRepo->getRepo($username, $repoName);
            $repo->setProps($this->request->getData());
            $repo->writeMetadata();
            $response = $this->response($repo);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createHalResponse(400);
            $response->contentType = 'application/hal';
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
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
     * @method delete
     * @secure
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
