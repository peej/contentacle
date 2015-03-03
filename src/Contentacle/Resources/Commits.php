<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits
 */
class Commits extends Resource {

    const PAGESIZE = 25;

    /**
     * Get a list of commits.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @embeds cont:commit List of commits
     */
    function get($username, $repoName, $branchName)
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page - 1) * self::PAGESIZE;
        $end = $start + self::PAGESIZE - 1;
        
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);

            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }

            $response = $this->response(200, 'commits');

            $response->addData('name', $branchName);
            $response->addData('repo', $repo->name);
            $response->addData('username', $repo->username);
            $response->addVar('nav', true);

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/commits'.$this->formatExtension());
            $response->addLink('cont:doc', '/rels/commits');

            if ($this->embed) {
                $commits = $repo->commits($branchName, $start, $end);
                
                foreach ($commits as $commit) {
                    $response->embed('cont:commit', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $commit['sha'])));
                }
            }
            
            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}