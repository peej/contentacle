<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/edit/?(.*)$
 */
class Edit extends Resource
{
    /**
     * Get the document edit form
     *
     * @method get
     * @response 200 OK
     * @provides text/html
     * @secure
     */
    function get($username, $repoName, $branchName, $path = null)
    {
        $repo = $this->repoRepository->getRepo($username, $repoName);
        try {
            $path = $this->fixPath($path, $username, $repoName, $branchName, 'edit');
            $document = $repo->document($branchName, $path);
            $response = $this->response('200', 'edit');

            $this->configureResponseWithDocument($response, $repo, $branchName, $document);

            $response->addVar('footer', false);
            $response->addData($document);
            $response->addLink('self', $this->buildUrl($username, $repoName, $branchName, 'edit', $document['path']));

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * Commit the changes
     *
     * @method post
     * @response 302 Found
     * @response 400 Bad Request
     * @provides text/html
     * @secure
     */
    function commit($username, $repoName, $branchName, $path = null)
    {
        if (isset($this->request->data['filename'])) {
            $filename = $this->request->data['filename'];
        } else {
            $filename = null;
        }

        if (isset($this->request->data['content'])) {
            $content = $this->request->data['content'];
        } else {
            $content = null;
        }

        if (!isset($this->request->data['message'])) {
            $this->request->data['message'] = 'Update to '.$path;
        }

        if (isset($this->request->data['metadata']) && is_array($this->request->data['metadata'])) {
            $metadata = array();

            foreach ($this->request->data['metadata']['name'] as $key => $name) {
                if ($name != '' && isset($this->request->data['metadata']['value'][$key])) {
                    $metadata[$name] = $this->request->data['metadata']['value'][$key];
                }
            }

            if ($metadata) {
                $content = $this->yaml->encode($metadata)."---\n".$content;
            }
        }

        if ($filename == null && $content == null) {
            $error = $this->response(400, 'error');
            $error->addVar('message', 'Could not edit document');
            $error->addError('nothing-to-do', 'Either a changed filename, content or metadata must be provided');
            return $error;
        }

        $path = $this->fixPath($path, $username, $repoName, $branchName, 'edit');
        $newPath = $path;

        if ($filename != null && $path != $filename) {
            $newPath = $filename;
        }

        $repo = $this->repoRepository->getRepo($username, $repoName);

        $repo->updateDocument($branchName, $path, $content, $this->request->data['message'], $newPath);

        return new \Tonic\Response(302, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'documents', $newPath)
        ));
    }

}