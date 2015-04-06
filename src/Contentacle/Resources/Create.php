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
            $response->addLink('cont:doc', '/rels/new');

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
    function commit($username, $repoName, $branchName, $path = null)
    {
        if (
            !isset($this->request->data['content']) ||
            !isset($this->request->data['filename'])
        ) {
            return new \Tonic\Response(400);
        }

        if (!isset($this->request->data['message'])) {
            $this->request->data['message'] = 'Created '.$path;
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

        $repo = $this->repoRepository->getRepo($username, $repoName);
        if ($path) {
            $path .= '/'.$this->request->data['filename'];
        } else {
            $path = $this->request->data['filename'];
        }

        $repo->createDocument($branchName, $path, $this->request->data['content'], $this->request->data['message']);

        return new \Tonic\Response(302, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'documents', $path)
        ));
    }

}