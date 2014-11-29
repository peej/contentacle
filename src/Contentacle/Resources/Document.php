<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents
 * @uri /users/:username/repos/:repo/branches/:branch/documents/?(.*)$
 */
class Document extends Resource
{
    /**
     * Generate a successful response.
     */
    private function buildResponse($code, $username, $repoName, $branchName, $document)
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
     * Get the contents of a document.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field filename The filename of the document.
     * @field path The path of the document.
     * @field type Directory (dir) or file.
     * @field sha Hash of this documents content blob
     * @field username Username of committer
     * @field email Email of committer
     * @field author Name of committer
     * @field date Date of the commit (as unix timestamp)
     * @field branch The branch the document is committed to
     * @field commit Hash of the commit
     * @field content The content of the document
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:user Link to creator of the document.
     * @links cont:history Link to the history of the document.
     * @links cont:raw Link to the raw content of the document.
     * @links cont:commit Link to the commit this document was a part of.
     * @embeds cont:document Documents within this document (if it is a directory).
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
            $response->addLink('cont:doc', '/rels/document');

            if ($this->embed) {
                foreach ($documents as $filename) {
                    $response->embed('cont:document', $this->getChildResource('\Contentacle\Resources\Document', array($username, $repoName, $branchName, $filename, false)));
                }
            }

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            try {
                $document = $repo->document($branchName, $path);
                $response = $this->buildResponse(200, $username, $repoName, $branchName, $document);
                return $response;

            } catch (\Contentacle\Exceptions\RepoException $e) {}
        }
        throw new \Tonic\NotFoundException;
    }

    /**
     * Update or create a document.
     *
     * @method put
     * @accepts application/json
     * @accepts application/yaml
     * @accepts *
     * @field content The content of the document.
     * @field message The commit message.
     * @secure
     * @response 200 OK
     * @response 201 Created
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field filename The filename of the document.
     * @field path The path of the document.
     * @field type Directory (dir) or file.
     * @field sha Hash of this documents content blob
     * @field username Username of committer
     * @field email Email of committer
     * @field author Name of committer
     * @field date Date of the commit (as unix timestamp)
     * @field branch The branch the document is committed to
     * @field commit Hash of the commit
     * @field content The content of the document
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:user Link to creator of the document.
     * @links cont:history Link to the history of the document.
     * @links cont:raw Link to the raw content of the document.
     * @links cont:commit Link to the commit this document was a part of.
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
        return $this->buildResponse($code, $username, $repoName, $branchName, $document);
    }

    /**
     * Delete the document
     *
     * @method delete
     * @accepts application/json
     * @accepts application/yaml
     * @field message The commit message.
     * @secure
     * @response 204 No content
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
