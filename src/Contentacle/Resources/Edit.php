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
     */
    function commit($username, $repoName, $branchName, $path = null)
    {
        if (!isset($_POST['content'])) {
            return new \Tonic\Response(400);
        }

        if (!isset($_POST['message'])) {
            $_POST['message'] = 'Update to '.$path;
        }

        if (isset($_POST['metadata']) && is_array($_POST['metadata'])) {
            $metadata = array();

            foreach ($_POST['metadata'] as $item) {
                if (isset($item['name']) && $item['name'] && isset($item['value']) && $item['value']) {
                    $metadata[$item['name']] = $item['value'];
                }
            }

            if ($metadata) {
                $_POST['content'] = $this->yaml->encode($metadata)."---\n".$_POST['content'];
            }
        }

        $repo = $this->repoRepository->getRepo($username, $repoName);
        $path = $this->fixPath($path, $username, $repoName, $branchName, 'edit');

        $repo->updateDocument($branchName, $path, $_POST['content'], $_POST['message']);

        return new \Tonic\Response(302, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'documents', $path)
        ));
    }

}