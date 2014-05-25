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

    public function loadDocuments($branch = 'master', $path = '')
    {
        $this->branch = $branch;
        $this->path = $path;

        $this->git->setBranch($this->branch);
        $tree = $this->git->tree($this->path);
        if ($tree && method_exists($tree, 'entries')) {
            $this->documents = array();
            foreach ($tree->entries() as $filename => $item) {
                $this->documents[$filename] = array(
                    'url' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/documents/'.$item->filename,
                    'filename' => $item->filename
                );
            }
        } else {
            $document = $this->git->file($this->path);
            if ($document) {
                $this->document = array(
                    'url' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/documents/'.$this->path,
                    'filename' => $document->filename,
                    'content' => $document->getContent(),
                    'raw' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/raw/'.$this->path,
                    'history' => '/users/'.$this->username.'/repos/'.$this->name.'/branches/'.$branch.'/history/'.$this->path
                );
            }
        }
    }
}