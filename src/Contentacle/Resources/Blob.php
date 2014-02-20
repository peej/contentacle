<?php

namespace Contentacle\Resources;

/**
 * @uri /:username/:repo/blob/:branch/(.+)
 */
class Files extends Resource {

    /**
     * @method get
     * @template blob.html
     */
    function get($username, $repoName, $branchName, $path)
    {
        $user = new \Contentacle\Models\User($this->app->container, $username);
        if (isset($user->repos[$repoName])) {
            $repo = $user->repos[$repoName];
            try {
                $commit = $repo->commit();
                $blob = $repo->file($path);
                return [200, [
                    'user' => $user,
                    'repo' => $repo,
                    'branch' => $branchName,
                    'commit' => $commit,
                    'content' => \Michelf\MarkdownExtra::defaultTransform($blob->content),
                    'path' => explode('/', $path),
                    'pageName' => basename($path)
                ]];
            } catch (\Git\Exception $e) {
                throw new \Tonic\NotFoundException;
            }
        } else {
            throw new \Tonic\NotFoundException;
        }
    }

}