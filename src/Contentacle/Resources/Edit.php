<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/edit/?(.*)$
 */
class Edit extends Document
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
            $response = $this->documentResponse('edit', $username, $repoName, $branchName, $document);

            $response->addVar('footer', false);

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

        $repo = $this->repoRepository->getRepo($username, $repoName);
        $path = $this->fixPath($path, $username, $repoName, $branchName, 'edit');

        $repo->updateDocument($branchName, $path, $_POST['content'], $_POST['message']);

        return new \Tonic\Response(302, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName, $branchName, 'documents', $path)
        ));
    }

}