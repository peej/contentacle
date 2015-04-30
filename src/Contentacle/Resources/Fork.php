<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/fork$
 */
class Fork extends Resource
{
    /**
     * Redirect
     *
     * @method get
     * @response 303 See Other
     * @provides text/html
     */
    function redirect($username, $repoName)
    {
        return new \Tonic\Response(303, null, array(
            'Location' => $this->buildUrlWithFormat($username, $repoName)
        ));
    }

    /**
     * Fork a repository
     *
     * @method post
     * @response 302 Found
     * @provides text/html
     * @secure
     */
    function fork($username, $repoName)
    {
        $repo = $this->repoRepository->getRepo($username, $repoName);
        $user = $this->app->user;

        if ($user->username == $username) {
            throw new \Tonic\NotFoundException;
        }

        try {
            if ($repo->fork($user->username)) {
                return new \Tonic\Response(302, null, array(
                    'Location' => $this->buildUrlWithFormat($user->username, $repoName)
                ));
            } else {
                $error = $this->response(400, 'error');
                $error->addVar('message', 'Could not fork');
                $error->addError('could-not-fork', 'Sorry, we could not fork this repository.');
                $error->addLink('exit', $this->buildUrlWithFormat($username, $repoName), false, 'Back');
                return $error;
            }
        } catch (\Contentacle\Exceptions\RepoException $e) {
            return new \Tonic\Response(302, null, array(
                'Location' => $this->buildUrlWithFormat($user->username, $repoName)
            ));
        }
    }

}