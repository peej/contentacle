<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch
 */
class Branch extends Resource
{
    protected function configureResponseWithBranch($response, $repo, $branchName)
    {
        parent::configureResponseWithBranch($response, $repo, $branchName);

        $username = $repo->username;
        $repoName = $repo->name;

        $response->addData('name', $branchName);

        $response->addLink('self', $this->buildUrlWithFormat($username, $repoName, $branchName));
        $response->addLink('cont:doc', '/rels/branch');
        $response->addLink('cont:merges', $this->buildUrlWithFormat($username, $repoName, $branchName, 'merges'));
        $response->addLink('create-form', $this->buildUrl($username, $repoName, false, 'new?branch='.$branchName));
        $response->addLink('delete-form', $this->buildUrl($username, $repoName, false, 'delete?branch='.$branchName));
    }

    /**
     * Get a branch.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field name Name of the branch
     * @field repo Name of the repo the branch is of
     * @field username Username of the branch creator
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:commits Link to the branches commits.
     * @links cont:document Link to the branches documents.
     * @links cont:merges Link to merges possible with this branch..
     */
    function get($username, $repoName, $branchName)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }

            $response = $this->response(200, 'branch');

            $this->configureResponseWithBranch($response, $repo, $branchName);

            $commits = $repo->commits($branchName, null, 1);
            if ($commits && $commits[0]) {
                $response->addData(array(
                    'date' => $commits[0]['date'],
                    'author' => $commits[0]['author']
                ));
                $response->addLink('author', $this->buildUrlWithFormat($commits[0]['authorname']));
            }

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * Redirect HTML client to documents view
     *
     * @method get
     * @response 302 Found
     * @provides text/html
    */
    function redirectToDocuments($username, $repoName, $branchName)
    {
        return new \Tonic\Response(302, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'documents')
        ));
    }

    /**
     * Rename a branch.
     *
     * @method patch
     * @accepts application/json-patch+yaml
     * @accepts application/json-patch+json
     * @field name Name of the branch
     * @secure
     * @response 200 OK
     * @response 400 Bad request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:commits Link to the branches commits.
     * @links cont:document Link to the branches documents.
     * @links cont:merges Link to merges possible with this branch..
     * @embeds cont:error A list of errored fields.
     */
    public function renameBranch($username, $repoName, $branchName)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);

            $patch = $this->request->getData();
            foreach ($patch as $item) {
                if ($item['path'] == 'name') {
                    $repo->renameBranch($branchName, $item['value']);
                    break;
                }
            }

            $response = $this->response(200, 'branch');

            $this->configureResponseWithBranch($response, $repo, $item['value']);

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->response(400, 'branch');
            foreach ($e->errors as $field) {
                $response->embed('cont:error', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        } catch (\Git\Exception $e) {
            if (preg_match('/fatal: (A branch named \''.$item['value'].'\' already exists)/', $e->getMessage(), $match)) {
                $response = $this->response(400, 'branch');
                $response->embed('cont:error', array(
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
     * Delete a branch
     *
     * @method delete
     * @secure
     * @response 204 No content
     * @response 400 Bad request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @embeds cont:error A list of errored fields.
     */
    public function deleteBranch($username, $repoName, $branchName)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);

            if ($repo->hasBranch($branchName)) {
                $repo->deleteBranch($branchName);
                $response = $this->response(204, 'branch');
            } else {
                throw new \Tonic\NotFoundException;
            }
        
        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->response(400, 'branch');
            foreach ($e->errors as $field) {
                $response->embed('cont:error', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }

        } catch (\Contentacle\Exceptions\RepoException $e) {
            $response = $this->response(400, 'branch');
            $response->embed('cont:error', array(
                'logref' => 'name',
                'message' => 'Can not delete "'.$branchName.'" branch'
            ));

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }

        return $response;
    }

}
