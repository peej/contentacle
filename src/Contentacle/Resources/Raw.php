<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/raw/(.+)$
 */
class Raw extends Resource
{
    /**
     * Get the raw contents of a document.
     *
     * @method get
     * @response 200 OK
     */
    function get($username, $repoName, $branch, $path)
    {
        $path = $this->fixPath($path, $username, $repoName, $branch, 'raw');

        $repo = $this->repoRepository->getRepo($username, $repoName);

        try {
            $document = $repo->document($branch, $path);
            if ($document) {
                $response = new \Tonic\Response(200, $document['content']);

                if (isset($document['metadata']['content-type'])) {
                    $response->contentType = $document['metadata']['content-type'];
                } elseif (class_exists('finfo')) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $response->contentType = $finfo->buffer($document['content']);
                }
                if (!$response->contentType) {
                    $response->contentType = 'text/plain';
                }

                return $response;
            }
        
        } catch (\Contentacle\Exceptions\RepoException $e) {}
        throw new \Tonic\NotFoundException;
    }
}