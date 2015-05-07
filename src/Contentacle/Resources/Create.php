<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/new/?(.*)$
 */
class Create extends Resource
{
    /**
     * Get the document create form
     *
     * @method get
     * @response 200 OK
     * @provides text/html
     * @secure
     */
    function get($username, $repoName, $branchName, $path = null)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
        $path = $this->fixPath($path, $username, $repoName, $branchName, 'new');
        
        if (!$repo->hasBranch($branchName)) {
            throw new \Tonic\NotFoundException;
        }

        try {
            $document = $repo->document($branchName, $path);

            return new \Tonic\Response(302, null, array(
                'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'edit', $path)
            ));

        } catch (\Contentacle\Exceptions\RepoException $e) {
            $response = $this->response('200', 'edit');

            $this->configureResponseWithBranch($response, $repo, $branchName);

            $response->addVar('footer', false);
            $response->addData('path', $path);
            $response->addLink('self', $this->buildUrl($username, $repoName, $branchName, 'new', $path));

            return $response;
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
    function commit($username, $repoName, $branchName)
    {
        if (!isset($this->request->data['content'])) {
            $error = $this->response(400, 'error');
            $error->addVar('message', 'Could not create document');
            $error->addError('content', 'Content not provided');
            return $error;
        }

        if (!isset($this->request->data['filename'])) {
            $error = $this->response(400, 'error');
            $error->addVar('message', 'Could not create document');
            $error->addError('filename', 'Filename not provided');
            return $error;
        }

        if (!isset($this->request->data['message'])) {
            $this->request->data['message'] = 'Created '.$this->request->data['filename'];
        }

        if (isset($this->request->data['metadata']) && is_array($this->request->data['metadata'])) {
            $metadata = array();

            foreach ($this->request->data['metadata'] as $item) {
                if (isset($item['name']) && $item['name'] && isset($item['value']) && $item['value']) {
                    $metadata[$item['name']] = $item['value'];
                }
            }

            if ($metadata) {
                $this->request->data['content'] = $this->yaml->encode($metadata)."---\n".$this->request->data['content'];
            }
        }

        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);

            $repo->createDocument($branchName, $this->request->data['filename'], $this->request->data['content'], $this->request->data['message']);
        } catch (\Contentacle\Exceptions\RepoException $e) {
            $error = $this->response(400, 'error');
            $error->addVar('message', 'Could not create document');
            $error->addError('repository-error', 'Filename not provided');
            return $error;
        }

        return new \Tonic\Response(302, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'documents', $this->request->data['filename'])
        ));
    }

}