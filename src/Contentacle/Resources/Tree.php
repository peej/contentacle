<?php

namespace Contentacle\Resources;

/**
 * @uri /:username/:repo
 * @uri /:username/:repo/tree
 * @uri /:username/:repo/tree/:branch
 * @uri /:username/:repo/tree/:branch/:path
 */
class Tree extends Resource {

    /**
     * @method get
     * @template tree.html
     */
    function get($username, $repoName, $branchName = 'master', $path = '')
    {
        $user = new \Contentacle\Models\User($this->app->container, $username);
        if (isset($user->repos[$repoName])) {
            $repo = $user->repos[$repoName];
            try {
                $commit = $repo->commit();
                $tree = $repo->tree($path);
                var_dump($tree->entries()['indir.txt']->history[0]->sha);
                return [200, [
                    'user' => $user,
                    'repo' => $repo,
                    'branch' => $branchName,
                    'commit' => $commit,
                    'pathParts' => explode('/', $path),
                    'path' => $path ? $path.'/' : '',
                    'tree' => $tree
                ]];
            } catch (\Git\Exception $e) {
                throw new \Tonic\NotFoundException;
            }
        } else {
            throw new \Tonic\NotFoundException;
        }
    }

}