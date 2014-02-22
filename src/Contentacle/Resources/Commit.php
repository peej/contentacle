<?php

namespace Contentacle\Resources;

/**
 * @uri /:username/:repo/history/:sha
 */
class Commit extends Resource {

    /**
     * @method get
     * @template commit.html
     */
    function get($username, $repoName, $sha)
    {
        $user = new \Contentacle\Models\User($this->app->container, $username);
        if (isset($user->repos[$repoName])) {
            $repo = $user->repos[$repoName];
            try {
                $commit = $repo->commit($sha);
                $changes = array();
                $additions = $deletions = 0;
                foreach ($commit->diff as $filename => $lines) {
                    foreach ($lines as $line) {
                        preg_match('/^([0-9]+)([+ -])(.*)$/', $line, $matches);
                        $changes[$filename][$matches[1]] = array(
                            'type' => $matches[2],
                            'line' => $matches[3]
                        );
                        $additions += ($matches[2] == '+');
                        $deletions += ($matches[2] == '-');
                    }
                }
                return [200, [
                    'user' => $user,
                    'repo' => $repo,
                    'commit' => $commit,
                    'changes' => $changes,
                    'additions' => $additions,
                    'deletions' => $deletions
                ]];
            } catch (\Git\Exception $e) {
                throw new \Tonic\NotFoundException;
            }
        } else {
            throw new \Tonic\NotFoundException;
        }
    }

}