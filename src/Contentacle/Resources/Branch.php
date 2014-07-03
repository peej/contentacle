<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch
 */
class Branch extends Resource
{
    private function response($repo, $branchName)
    {
        $response = new \Contentacle\Responses\Hal(200, array(
            'name' => $branchName,
            'repo' => $repo->name,
            'username' => $repo->username,
        ));

        $branchUrl = '/users/'.$repo->username.'/repos/'.$repo->name.'/branches/'.$branchName;

        $response->addLink('self', $branchUrl.$this->formatExtension());
        $response->addForm('cont:edit-branch', 'patch', null, 'application/json-patch', 'Rename the branch');
        $response->addForm('cont:delete-branch', 'delete', null, null, 'Remove the branch');
        $response->addLink('cont:commits', $branchUrl.'/commits'.$this->formatExtension());
        $response->addLink('cont:documents', $branchUrl.'/documents'.$this->formatExtension());
        
        $response->contentType = 'contentacle/branch';
        return $response;
    }

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
        $repoRepo = $this->container['repo_repository'];
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
            $response = new \Contentacle\Responses\Hal(400);
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
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
        $repoRepo = $this->container['repo_repository'];
        try {
            $repo = $repoRepo->getRepo($username, $repoName);

            if ($repo->hasBranch($branchName)) {
                $repo->deleteBranch($branchName);
                $response = new \Contentacle\Responses\Hal(204);
            } else {
                throw new \Tonic\NotFoundException;
            }
        
        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = new \Contentacle\Responses\Hal(400);
            $response->contentType = 'application/hal';
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }

        } catch (\Contentacle\Exceptions\RepoException $e) {
            $response = new \Contentacle\Responses\Hal(400);
            $response->contentType = 'application/hal';
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
