<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents
 * @uri /users/:username/repos/:repo/branches/:branch/documents/?(.*)$
 */
class Documents extends Resource {

    private function response($code, $username, $repoName, $branchName, $document)
    {
        $response = $this->createHalResponse($code, $document);

        $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents/'.$document['path'].$this->formatExtension());
        $response->addLink('cont:doc', '/rels/document');
        $response->addLink('cont:user', '/users/'.$document['username'].$this->formatExtension());
        $response->addLink('cont:history', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/history/'.$document['path'].$this->formatExtension());
        $response->addLink('cont:raw', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/raw/'.$document['path'].$this->formatExtension());
        $response->addLink('cont:commit', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/commits/'.$document['commit'].$this->formatExtension());

        return $response;
    }

    /**
     * @provides application/hal+yaml
     * @provides contentacle/document+yaml
     * @provides application/hal+json
     * @provides contentacle/document+json
     */
    function get($username, $repoName, $branchName, $path = null, $fixPath = true)
    {
        $repoRepo = $this->getRepoRepository();

        if ($fixPath) {
            $path = $this->fixPath($path, $username, $repoName, $branchName, 'documents');
        }

        $repo = $repoRepo->getRepo($username, $repoName);
        try {
            $documents = $repo->documents($branchName, $path);

            $response = $this->createHalResponse(200, array(
                'filename' => basename($path),
                'path' => $path,
                'type' => 'dir'
            ));

            if ($path) {
                $path = '/'.$path;
            }
            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents'.$path.$this->formatExtension());
            $response->addLink('cont:doc', '/rels/documents');

            if ($this->embed) {
                foreach ($documents as $filename) {
                    $response->embed('cont:document', $this->getChildResource('\Contentacle\Resources\Documents', array($username, $repoName, $branchName, $filename, false)));
                }
            }

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            try {
                $document = $repo->document($branchName, $path);
                $response = $this->response(200, $username, $repoName, $branchName, $document);
                return $response;

            } catch (\Contentacle\Exceptions\RepoException $e) {}
        }
        throw new \Tonic\NotFoundException;
    }

    /**
     * @method put
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function createDocument($username, $repoName, $branchName, $path = null)
    {
        $repoRepo = $this->getRepoRepository();

        $path = $this->fixPath($path, $username, $repoName, $branchName, 'documents');

        $repo = $repoRepo->getRepo($username, $repoName);
        $data = $this->request->getData();

        $commitMessage = null;

        if (is_string($data)) {
            $content = $data;
        } elseif (isset($data['content'])) {
            $content = $data['content'];
            if (isset($data['message'])) {
                $commitMessage = $data['message'];
            }
        } else {
            $e = new \Contentacle\Exceptions\ValidationException;
            $e->errors = array('content');
            throw $e;
        }

        try {
            $repo->updateDocument($branchName, $path, $content, $commitMessage);
            $code = 200;
        } catch (\Contentacle\Exceptions\RepoException $e) {
            $repo->createDocument($branchName, $path, $content, $commitMessage);
            $code = 201;
        }

        $document = $repo->document($branchName, $path);
        return $this->response($code, $username, $repoName, $branchName, $document);
    }


    /**
     * @method delete
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function deleteDocument($username, $repoName, $branchName, $path = null)
    {
        $repoRepo = $this->getRepoRepository();

        $path = $this->fixPath($path, $username, $repoName, $branchName, 'documents');

        $repo = $repoRepo->getRepo($username, $repoName);
        $data = $this->request->getData();

        $commitMessage = null;

        if (isset($data['message'])) {
            $commitMessage = $data['message'];
        } elseif (is_string($data)) {
            $commitMessage = $data;
        }

        $repo->deleteDocument($branchName, $path, $commitMessage);

        return $this->createHalResponse(204);
    }
}
