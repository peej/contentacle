<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch
 */
class Branch extends Resource
{
    private function response($repo, $branchName)
    {
        $response = $this->createHalResponse(200);

        $response->addData('name', $branchName);
        $response->addData('repo', $repo->name);
        $response->addData('username', $repo->username);

        $branchUrl = '/users/'.$repo->username.'/repos/'.$repo->name.'/branches/'.$branchName;

        $response->addLink('self', $branchUrl.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/branch');
        $response->addLink('cont:commits', $branchUrl.'/commits'.$this->formatExtension());
        $response->addLink('cont:documents', $branchUrl.'/documents'.$this->formatExtension());
        $response->addLink('cont:merges', $branchUrl.'/merges'.$this->formatExtension());

        return $response;
    }

    /**
     * @method get
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName, $branchName)
    {
        try {
            $repoRepo = $this->getRepoRepository();
            $repo = $repoRepo->getRepo($username, $repoName);
            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }
            return $this->response($repo, $branchName);

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
    public function renameBranch($username, $repoName, $branchName)
    {
        $repoRepo = $this->getRepoRepository();
        try {
            $repo = $repoRepo->getRepo($username, $repoName);

            $patch = $this->request->getData();
            foreach ($patch as $item) {
                if ($item['path'] == 'name') {
                    $repo->renameBranch($branchName, $item['value']);
                    break;
                }
            }

            $response = $this->response($repo, $item['value']);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createHalResponse(400);
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        } catch (\Git\Exception $e) {
            if (preg_match('/fatal: (A branch named \''.$item['value'].'\' already exists)/', $e->getMessage(), $match)) {
                $response = $this->createHalResponse(400);
                $response->embed('errors', array(
                    'logref' => 'name',
                    'message' => $match[1]
                ));
            } else {
                throw new \Tonic\NotFoundException;
            }
        }

        return $response;
    }

    /**
     * @method delete
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function deleteBranch($username, $repoName, $branchName)
    {
        $repoRepo = $this->getRepoRepository();
        try {
            $repo = $repoRepo->getRepo($username, $repoName);

            if ($repo->hasBranch($branchName)) {
                $repo->deleteBranch($branchName);
                $response = $this->createHalResponse(204);
            } else {
                throw new \Tonic\NotFoundException;
            }
        
        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createHalResponse(400);
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }

        } catch (\Contentacle\Exceptions\RepoException $e) {
            $response = $this->createHalResponse(400);
            $response->embed('errors', array(
                'logref' => 'name',
                'message' => 'Can not delete "'.$branchName.'" branch'
            ));

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }

        return $response;
    }

}
