<?php

namespace Contentacle\Models;

class Repo extends Model
{
    private $git;

    function __construct($data, $gitProvider, $yaml)
    {
        $this->git = $gitProvider($data['username'], $data['name']);

        $repoMetadata = $yaml->decode($this->git->file('contentacle.yaml'));

        $data = array_merge($data, $repoMetadata);

        parent::__construct(array(
            'username' => true,
            'name' => true,
            'title' => 'Un-named repo',
            'url' => function ($data) {
                return '/users/'.$data['username'].'/repos/'.$data['name'];
            },
            'description' => true
        ), $data);
    }

    public function loadBranches()
    {
        $this->branches = array();
        $branches = $this->git->getBranches();
        foreach ($branches as $branch) {
            $this->branches[$branch] = array(
                'name' => $branch,
                'url' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch
            );
        }
    }

    public function hasBranch($branchName)
    {
        return in_array($branchName, $this->git->getBranches());
    }

    public function documents($branch = 'master', $path = '')
    {
        $this->git->setBranch($branch);
        $tree = $this->git->tree($path);
        if ($tree && method_exists($tree, 'entries')) {
            $documents = array();
            foreach ($tree->entries() as $filename => $item) {
                $documents[$filename] = array(
                    'url' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/documents/'.$item->filename,
                    'filename' => $item->filename
                );
            }
            return $documents;
        }
        throw new \Exception("Path '$path' does not exist");
    }

    public function document($branch = 'master', $path = '')
    {
        $this->git->setBranch($branch);
        $document = $this->git->file($path);
        if ($document) {
            return array(
                'url' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/documents/'.$path,
                'filename' => $document->filename,
                'content' => $document->getContent(),
                'raw' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/raw/'.$path,
                'history' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/history/'.$path
            );
        }
        throw new \Exception("Document '$path' does not exist");
    }

    public function history($branch = 'master', $path = '')
    {
        $this->git->setBranch($branch);
        $file = $this->git->file($path);
        if ($file) {
            $history = array();
            foreach ($file->getHistory() as $commit) {
                $history[] = array(
                    'sha' => $commit->sha,
                    'message' => $commit->message,
                    'date' => $commit->date,
                    'username' => $commit->user,
                    'email' => $commit->email,
                    'url' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/commits/'.$commit->sha
                );
            }
            return $history;
        }
        throw new \Exception("Path '$path' not found in branch '$branch'");
    }
}