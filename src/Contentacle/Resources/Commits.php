<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commits
 */
class Commits extends Resource {

    const PAGESIZE = 25;

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName, $branchName)
    {

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page - 1) * self::PAGESIZE;
        $end = $start + self::PAGESIZE - 1;
        
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);

            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }

            $response = new \Contentacle\Responses\Hal();
            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/commits'.$this->formatExtension());
            $response->addForm('cont:commit', 'post', null, 'Create a commit');

            if ($this->embed) {
                $commits = $repo->commits($branchName, $start, $end);
                
                foreach ($commits as $commit) {
                    $response->embed('commits', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $commit['sha'])));
                }
            }
            
            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}