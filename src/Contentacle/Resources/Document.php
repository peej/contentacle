<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents/?(.*)$
 */
class Document extends Resource
{
    protected function configureResponseWithDocument($response, $repo, $branchName, $document)
    {
        $response->addData($document);

        $response->addLink('self', $this->buildUrl($repo->username, strtolower($repo->name), $branchName, 'documents', $document['path']));
        $response->addLink('cont:doc', '/rels/document');

        parent::configureResponseWithDocument($response, $repo, $branchName, $document);
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
     * @links cont:user Link to the owner of the repository.
     * @links cont:repo Link to the repository.
     * @links cont:branch Link to the branch within the repository.
     * @links cont:documents Link to the documents within the branch.
     * @links cont:commits Link to the commits within the branch.
     * @links cont:history Link to the history of the document.
     * @links cont:raw Link to the raw content of the document.
     * @links cont:document Link to itself.
     * @links create-form Link to the form to create a new document.
     * @links edit-form Link to the form to edit itself.
     * @links cont:commit Link to the commit of this version of this document.
     * @links author Link to the author of this version of this document.
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

            $this->configureResponse($response, $repo, $branchName);

            $response->addData(array(
                'path' => $path,
                'filename' => basename($path),
                'dir' => true
            ));

            $response->addLink('self', $this->buildUrl($username, $repoName, $branchName, 'documents', $path));
            $response->addLink('cont:doc', '/rels/document');
            $response->addLink('create-form', $this->buildUrl($username, $repoName, $branchName, 'new', $path));

            foreach ($repo->branches() as $branch) {
                $response->addLink(
                    'cont:branches',
                    $this->buildUrlWithFormat($username, $repoName, $branch),
                    false,
                    $branch
                );
            }

            $documents = $repo->documents($branchName, $path);

            if ($this->embed) {
                $commits = $repo->commits($branchName, null, 1);

                $breadcrumb = array();
                $breadcrumbUrl = '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents';
                foreach (explode('/', $path) as $part) {
                    if ($part) {
                        $breadcrumbUrl .= '/'.$part;
                        $breadcrumb[$breadcrumbUrl] = $part;
                    }
                }

                $response->addVar('breadcrumb', $breadcrumb);
                $response->addVar('description', $repo->description);

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
            }

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            try {
                $document = $repo->document($branchName, $path);
                $response = $this->response(200, 'document');

                $this->configureResponseWithDocument($response, $repo, $branchName, $document);
                
                return $response;

            } catch (\Contentacle\Exceptions\RepoException $e) {}
        } catch (\Git\Exception $e) {
            return $response;
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

        $this->configureResponseWithDocument($response, $repo, $branchName, $document);

        return $response;
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