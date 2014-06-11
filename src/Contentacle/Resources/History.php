<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/history/(.+)$
 */
class History extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName, $branchName, $path = null)
    {
        $repoRepo = $this->container['repo_repository'];

        $path = $this->fixPath($path, $username, $repoName, $branchName, 'history');

        $repo = $repoRepo->getRepo($username, $repoName);
        try {
            $history = $repo->history($branchName, $path);

            $response = new \Contentacle\Responses\Hal(200, array(
                'filename' => basename($path),
                'path' => $path
            ));

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/history/'.$path.$this->formatExtension());
            $response->addLink('cont:document', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents/'.$path.$this->formatExtension());
            $response->addLink('cont:raw', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/raw/'.$path.$this->formatExtension());

            foreach ($history as $item) {
                $response->embed('commits', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $item['sha'])));
            }

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}