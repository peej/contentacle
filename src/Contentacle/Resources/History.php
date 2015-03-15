<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/history/(.+)$
 */
class History extends WithinDocument {

    /**
     * Get the history of a document
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field filename The filename of the document.
     * @field path The path of the document.
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:document Link to this documentation.
     * @links cont:raw Link to creator of the document.
     * @links cont:raw Link to creator of the document.
     * @embeds cont:commit List of commits making up this documents history.
     */
    function get($username, $repoName, $branchName, $path = null)
    {
        $path = $this->fixPath($path, $username, $repoName, $branchName, 'history');

        $repo = $this->repoRepository->getRepo($username, $repoName);
        try {
            $history = $repo->history($branchName, $path);

            $response = $this->response(200, 'history');

            $this->configureResponse($response, $repo, $branchName);

            $response->addData(array(
                'filename' => basename($path),
                'path' => $path
            ));

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/history/'.$path);
            $response->addLink('cont:doc', '/rels/history');
            $response->addLink('cont:document', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents/'.$path);
            $response->addLink('cont:raw', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/raw/'.$path);

            foreach ($history as $item) {
                $response->embed('cont:commit', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $item['sha'])));
            }

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}