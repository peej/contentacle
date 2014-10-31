<?php

namespace Contentacle\Models;

class Repo extends Model
{
    private $git, $gitProvider, $repoDir, $yaml, $userRepo;

    function __construct($data, $gitProvider, $repoDir, $yaml, $userRepo)
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
        $this->userRepo = $userRepo;

        $user = $userRepo->getUser($data['username']);
        $this->git->setUser($user->name, $user->email);

        try {
            $repoMetadata = $yaml->decode($this->git->file('contentacle.yaml'));
        } catch (\Git\Exception $e) {
            $repoMetadata = array();
        }

        $data = array_merge($data, $repoMetadata);

        parent::__construct(array(
            'username' => '/^[a-z]{2,40}$/',
            'name' => '/^[a-z-]{2,40}$/',
            'title' => '/^[A-Za-z0-9 ]{0,100}$/',
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

    public function createBranch($branchName)
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9 .-]+$/', $branchName)) {
            throw new \Contentacle\Exceptions\RepoException("Branch name '$branchName' is not valid");
        }
        $this->git->createBranch($branchName);
    }

    public function renameBranch($branchName, $newName)
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9 .-]+$/', $newName)) {
            throw new \Contentacle\Exceptions\RepoException("Branch name '$newName' is not valid");
        }
        $this->git->renameBranch($branchName, $newName);
    }

    public function deleteBranch($branchName)
    {
        $branches = $this->git->getBranches();
        if (count($branches) == 1) {
            throw new \Contentacle\Exceptions\RepoException("Can not delete only branch");
        }
        try {
            $this->git->deleteBranch($branchName);
        } catch (\Git\Exception $e) {
            throw new \Contentacle\Exceptions\RepoException("Can not delete branch '$branchName'");
        }
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
                    'username' => $this->userRepo->getUsernameFromEmail($document->email),
                    'email' => $document->email,
                    'author' => $document->user,
                    'date' => $document->date,
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
                        'username' => $this->userRepo->getUsernameFromEmail($commit->email),
                        'author' => $commit->user,
                        'email' => $commit->email
                    );
                }
                return $history;
            }
        } catch (\Git\Exception $e) {}
        throw new \Contentacle\Exceptions\RepoException("Path '$path' not found in branch '$branch'");
    }

    public function blob($sha)
    {
        try {
            return $this->git->catFile($sha);
        } catch (\Git\Exception $e) {}
        throw new \Contentacle\Exceptions\RepoException("Blob '$sha' does not exist");
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
                'username' => $this->userRepo->getUsernameFromEmail($commit->email),
                'author' => $commit->user,
                'email' => $commit->email
            );
        }
        return $commits;
    }

    /**
     * Get a given commit
     * @param str $branch
     * @param str $sha
     * @return str[]
     */
    public function commit($branch, $sha)
    {
        $commit = $this->git->commit($sha);

        return array(
            'sha' => $commit->sha,
            'parents' => $commit->parents,
            'message' => $commit->message,
            'date' => $commit->date,
            'username' => $this->userRepo->getUsernameFromEmail($commit->email),
            'author' => $commit->user,
            'email' => $commit->email,
            'files' => $commit->getFiles(),
            'diff' => $commit->diff
        );
    }

    /**
     * Write the repos metadata into the contentacle.yaml file in the root of the repo and commit it
     * @param str $branch
     * @return bool
     */
    public function writeMetadata($branch = 'master')
    {
        try {
            return $this->updateDocument(
                $branch,
                'contentacle.yaml',
                $this->yaml->encode($this->props()),
                'Update repo metadata'
            );
        } catch (\Contentacle\Exceptions\RepoException $e) {
            return $this->createDocument(
                $branch,
                'contentacle.yaml',
                $this->yaml->encode($this->props()),
                'Created repo metadata'
            );
        }
    }

    /**
     * Delete the repo
     */
    public function delete()
    {
        if ($this->username && $this->name) {
            $path = $this->repoDir.'/'.$this->username.'/'.$this->name.'.git';
            $this->removeDirectory($path);
        }
    }

    private function removeDirectory($path)
    {
        foreach (glob($path.'/*') as $filename) {
            if (is_dir($filename)) {
                $this->removeDirectory($filename);
            } else {
                unlink($filename);
            }
        }
        rmdir($path);
    }

    public function createDocument($branch, $path, $content, $commitMessage = null)
    {
        if (!$commitMessage) {
            $commitMessage = 'Create '.$path;
        }
        $this->git->setBranch($branch);
        try {
            $this->git->add($path, $content, $commitMessage);
        } catch (\Git\Exception $e) {
            throw new \Contentacle\Exceptions\RepoException('Could not create document "'.$path.'"');
        }
    }

    public function updateDocument($branch, $path, $content, $commitMessage = null)
    {
        if (!$commitMessage) {
            $commitMessage = 'Update '.$path;
        }
        $this->git->setBranch($branch);
        try {
            $this->git->update($path, $content, $commitMessage);
        } catch (\Git\Exception $e) {
            throw new \Contentacle\Exceptions\RepoException('Could not update document "'.$path.'"');
        }
    }

    public function deleteDocument($branch, $path, $commitMessage)
    {
        if (!$commitMessage) {
            $commitMessage = 'Delete '.$path;
        }
        $this->git->setBranch($branch);
        if (!$this->git->remove($path, $commitMessage)) {
            throw new \Contentacle\Exceptions\RepoException('Could not delete document "'.$path.'"');
        }
    }

    public function canMerge($branch, $branch2)
    {
        $this->git->setBranch($branch);
        $this->git->canMerge($branch2);
    }

    public function conflicts($branch, $branch2)
    {
        $this->git->setBranch($branch);
        return $this->git->mergeConflicts($branch2);
    }

    public function merge($branch, $branch2)
    {
        $this->git->setBranch($branch);
        $this->git->resetIndex();
        return $this->git->merge($branch2);
    }

    public function revert($sha, $commitMessage)
    {
        return $this->git->revert($sha, $commitMessage);
    }

}