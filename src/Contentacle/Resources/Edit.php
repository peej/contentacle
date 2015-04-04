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
            $response->addLink('cont:doc', '/rels/edit');

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
        if (!isset($this->request->data['content'])) {
            return new \Tonic\Response(400);
        }

        if (!isset($this->request->data['message'])) {
            $this->request->data['message'] = 'Update to '.$path;
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
        $path = $this->fixPath($path, $username, $repoName, $branchName, 'edit');

        $repo->updateDocument($branchName, $path, $this->request->data['content'], $this->request->data['message']);

        return new \Tonic\Response(302, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'documents', $path)
        ));
    }

}