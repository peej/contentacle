<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents/?(.*)$
 */
class Documents extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
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
            $response->addForm('cont:add-document', 'post', null, 'Add a document');

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
                $response->addLink('cont:history', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/history/'.$document['path'].$this->formatExtension());
                $response->addLink('cont:raw', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/raw/'.$document['path'].$this->formatExtension());
                $response->addForm('cont:update-document', 'patch', null, 'Update the document');
                $response->addForm('cont:delete-document', 'delete', null, 'Delete the document');
                $response->addForm('cont:edit-document', 'put', '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/raw/'.$document['path'].$this->formatExtension(), 'Add a document', '*/*');

                return $response;

            } catch (\Contentacle\Exceptions\RepoException $e) {}
        }
        throw new \Tonic\NotFoundException;
    }
}
