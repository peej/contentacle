<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents/?(.*)$
 */
class Documents extends Resource {

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username, $repoName, $branchName, $path = null, $fixPath = true)
    {
        $repoRepo = $this->container['repo_repository'];

        if ($fixPath) {
            $path = $this->fixPath($path, $username, $repoName, $branchName, 'documents');
        }

        $repo = $repoRepo->getRepo($username, $repoName);
        try {
            $documents = $repo->documents($branchName, $path);

            $response = new \Contentacle\Responses\Hal(200, array(
                'filename' => basename($path),
                'path' => $path,
                'type' => 'dir'
            ));

            if ($path) {
                $path = '/'.$path;
            }
            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents'.$path.$this->formatExtension());

            if ($this->embed) {
                foreach ($documents as $filename) {
                    $response->embed('documents', $this->getChildResource('\Contentacle\Resources\Documents', array($username, $repoName, $branchName, $filename, false)));
                }
            }
            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            try {
                $document = $repo->document($branchName, $path);

                $response = new \Contentacle\Responses\Hal(200, $document);
                $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/documents/'.$document['path'].$this->formatExtension());
                $response->addLink('history', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/history/'.$document['path'].$this->formatExtension());
                $response->addLink('raw', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/raw/'.$document['path'].$this->formatExtension());

                return $response;

            } catch (\Contentacle\Exceptions\RepoException $e) {}
        }
        throw new \Tonic\NotFoundException;
    }
}
