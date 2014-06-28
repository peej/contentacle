<?php

namespace Contentacle\Models;

class Repo extends Model
{
    private $git, $gitProvider, $repoDir, $yaml;

    function __construct($data, $gitProvider, $repoDir, $yaml)
    {
        if (!isset($data['username'])) {
            throw new \Contentacle\Exceptions\RepoException("No username provided when creating repo");
        }
        if (!isset($data['name'])) {
            throw new \Contentacle\Exceptions\RepoException("No repo name provided when creating repo");
        }

        $this->git = $gitProvider($data['username'], $data['name'].'.git');
        $this->gitProvider = $gitProvider;
        $this->repoDir = $repoDir;
        $this->yaml = $yaml;

        try {
            $repoMetadata = $yaml->decode($this->git->file('contentacle.yaml'));
        } catch (\Git\Exception $e) {
            $repoMetadata = array();
        }

        $data = array_merge($data, $repoMetadata);

        parent::__construct(array(
            'username' => '/^[a-z]{2,40}$/',
            'name' => '/^[a-z-]{2,40}$/',
            'title' => '/^[A-Za-z0-9 ]{2,100}$/',
            'description' => true
        ), $data);
    }

    public function setProp($name, $value)
    {
        if ($this->username && $this->name) {
            $oldPath = $this->repoDir.'/'.$this->username.'/'.$this->name.'.git';
        }

        $set = parent::setProp($name, $value);

        if ($set && isset($oldPath) && ($name == 'username' || $name == 'name')) {
            $newPath = $this->repoDir.'/'.$this->username.'/'.$this->name.'.git';
            if ($oldPath != $newPath) {
                rename($oldPath, $newPath);
                $this->git = $this->gitProvider->__invoke($this->username, $this->name.'.git');
            }
        }

        return $set;
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
        throw new \Contentacle\Exceptions\RepoException("Path '$path' does not exist");
    }

    public function document($branch = 'master', $path = '')
    {
        $this->git->setBranch($branch);
        try {
            $document = $this->git->file($path);
            if (is_a($document, '\Git\Blob')) {
                return array(
                    'filename' => basename($document->filename),
                    'path' => $document->filename,
                    'type' => 'file',
                    'sha' => $document->sha,
                    'username' => 'tbd',
                    'email' => $document->email,
                    'author' => $document->user,
                    'branch' => $branch,
                    'content' => $document->getContent()
                );
            }
        } catch (\Git\Exception $e) {}
        throw new \Contentacle\Exceptions\RepoException("Document '$path' does not exist");
    }

    public function history($branch = 'master', $path = '')
    {
        $this->git->setBranch($branch);
        try {
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
        } catch (\Git\Exception $e) {}
        throw new \Contentacle\Exceptions\RepoException("Path '$path' not found in branch '$branch'");
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

    public function writeMetadata($branch = 'master')
    {
        return $this->save(
            $branch,
            'contentacle.yaml',
            $this->yaml->encode($this->props()),
            'Update repo metadata'
        );
    }

    public function save($branch, $path, $content, $commitMessage)
    {
        $this->git->setBranch($branch);
        try {
            $this->git->file($path);
            return $this->update($branch, $path, $content, $commitMessage);
        } catch (\Git\Exception $e) {
            return $this->create($branch, $path, $content, $commitMessage);
        }
    }

    public function create($branch, $path, $content, $commitMessage)
    {
        $this->git->setBranch($branch);
        return $this->git->add($path, $content, $commitMessage);
    }

    public function update($branch, $path, $content, $commitMessage)
    {
        $this->git->setBranch($branch);
        return $this->git->update($path, $content, $commitMessage);
    }

    public function delete($branch, $path, $commitMessage)
    {
        $this->git->setBranch($branch);
        return $this->git->remove($path, $commitMessage);
    }

}