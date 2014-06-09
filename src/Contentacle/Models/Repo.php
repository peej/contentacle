<?php

namespace Contentacle\Models;

class Repo extends Model
{
    private $git;

    function __construct($data, $gitProvider, $yaml)
    {
        $this->git = $gitProvider($data['username'], $data['name']);

        try {
            $repoMetadata = $yaml->decode($this->git->file('contentacle.yaml'));
        } catch (\Git\Exception $e) {
            $repoMetadata = array();
        }

        $data = array_merge($data, $repoMetadata);

        parent::__construct(array(
            'username' => true,
            'name' => true,
            'title' => 'Un-named repo',
            'description' => true
        ), $data);
    }

    public function branches()
    {
        return $this->git->getBranches();
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
                $documents[$filename] = $item->filename;
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
                'filename' => $document->filename,
                'sha' => $document->sha,
                'username' => 'tbd',
                'email' => $document->email,
                'author' => $document->user,
                'branch' => $branch,
                'content' => $document->getContent()
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
                    'email' => $commit->email
                );
            }
            return $history;
        }
        throw new \Exception("Path '$path' not found in branch '$branch'");
    }

    public function commits($branch = 'master', $sha = null, $number = 25)
    {
        $this->git->setBranch($branch);
        $commits = array();
        foreach ($this->git->commits($sha, $number) as $commit) {
            $commits[] = array(
                'sha' => $commit->sha,
                'message' => $commit->message,
                'date' => $commit->date,
                'username' => $commit->user,
                'email' => $commit->email
            );
        }
        return $commits;
    }

    public function commit($branch, $sha)
    {
        $commit = $this->git->commit($sha);

        return array(
            'sha' => $commit->sha,
            'parents' => $commit->parents,
            'message' => $commit->message,
            'date' => $commit->date,
            'username' => $commit->user,
            'email' => $commit->email,
            'files' => $commit->getFiles(),
            'diff' => $commit->diff
        );
    }
}