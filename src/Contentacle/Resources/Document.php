<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents
 * @uri /users/:username/repos/:repo/branches/:branch/documents/?(.*)$
 */
class Document extends Resource
{
    protected function documentResponse($response, $username, $repoName, $branchName, $document)
    {
        $response->addVar('nav', true);

        $response->addData(array(
            'username' => $username,
            'repo' => $repoName,
            'branch' => $branchName
        ));
        $response->addData($document);

        $documentUrl = $this->buildUrl($username, $repoName, $branchName, 'documents', $document['path']);

        $response->addLink('self', $documentUrl);
        $response->addLink('cont:doc', '/rels/document');
        $response->addLink('cont:user', $this->buildUrlWithFormat($username));
        $response->addLink('cont:repo', $this->buildUrlWithFormat($username, $repoName));
        $response->addLink('cont:branch', $this->buildUrlWithFormat($username, $repoName, $branchName));
        $response->addLink('cont:history', $this->buildUrl($username, $repoName, $branchName, 'history', $document['path']));
        $response->addLink('cont:raw', $this->buildUrl($username, $repoName, $branchName, 'raw', $document['path']));
        $response->addLink('cont:documents', $this->buildUrl($username, $repoName, $branchName, 'documents'));
        $response->addLink('cont:document', $documentUrl);
        $response->addLink('cont:edit', $this->buildUrl($username, $repoName, $branchName, 'edit', $document['path']));
        $response->addLink('cont:commits', $this->buildUrlWithFormat($username, $repoName, $branchName, 'commits'));
        $response->addLink('cont:commit', $this->buildUrlWithFormat($username, $repoName, $branchName, 'commits', $document['commit']));

        $response->embed('cont:commit', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $document['commit'])));

        return $response;
    }

    /**
     * Get the contents of a document.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
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
        if ($fixPath) {
            $path = $this->fixPath($path, $username, $repoName, $branchName, 'documents');
        }

        $repo = $this->repoRepository->getRepo($username, $repoName);
        try {
            $response = $this->response(200, 'directory');

            $response->addData(array(
                'username' => $repo->username,
                'repo' => $repo->name,
                'branch' => $branchName,
                'path' => $path,
                'filename' => basename($path),
                'dir' => true
            ));
            $response->addVar('nav', true);

            $breadcrumb = array();
            $breadcrumbUrl = '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents';
            foreach (explode('/', $path) as $part) {
                if ($part) {
                    $breadcrumbUrl .= '/'.$part;
                    $breadcrumb[$breadcrumbUrl] = $part;
                }
            }
            $response->addVar('breadcrumb', $breadcrumb);

            $response->addLink('self', $this->buildUrl($username, $repoName, $branchName, 'documents', $path));
            $response->addLink('cont:doc', '/rels/document');
            $response->addLink('cont:user', $this->buildUrl($username));
            $response->addLink('cont:repo', $this->buildUrl($username, $repoName));
            $response->addLink('cont:branch', $this->buildUrl($username, $repoName, $branchName));
            $response->addLink('cont:documents', $this->buildUrl($username, $repoName, $branchName, 'documents', $path));
            $response->addLink('cont:commits', $this->buildUrl($username, $repoName, $branchName, 'commits'));

            $documents = $repo->documents($branchName, $path);
            $commits = $repo->commits($branchName, null, 1);

            foreach ($documents as $filename) {
                if ($this->embed) {
                    $response->embed('cont:document', $this->getChildResource('\Contentacle\Resources\Document', array($username, $repoName, $branchName, $filename, false)));
                } else {
                    $response->addLink('cont:document', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents/'.$filename);
                }
            }

            foreach ($commits as $commit) {
                if ($this->embed) {
                    $response->embed('cont:commit', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $commit['sha'])));
                }
                $response->addLink('cont:commit', $this->buildUrl($username, $repoName, $branchName, 'commits', $commit['sha']));
            }

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            try {
                $document = $repo->document($branchName, $path);
                $response = $this->response('200', 'document');
                return $this->documentResponse($response, $username, $repoName, $branchName, $document);

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
        $path = $this->fixPath($path, $username, $repoName, $branchName, 'documents');

        $repo = $this->repoRepository->getRepo($username, $repoName);
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
        $response = $this->response($code, 'document');
        return $this->documentResponse($response, $username, $repoName, $branchName, $document);
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
        $path = $this->fixPath($path, $username, $repoName, $branchName, 'documents');

        $repo = $this->repoRepository->getRepo($username, $repoName);
        $data = $this->request->getData();

        $commitMessage = null;

        if (isset($data['message'])) {
            $commitMessage = $data['message'];
        } elseif (is_string($data)) {
            $commitMessage = $data;
        }

        $repo->deleteDocument($branchName, $path, $commitMessage);

        return $this->response(204);
    }
}