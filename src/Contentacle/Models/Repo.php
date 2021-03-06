<?php

namespace Contentacle\Models;

class Repo extends Model
{
    private $git, $gitProvider, $repoDir, $userRepo, $fileAccess, $yaml, $diffCalculator;

    function __construct($data, $gitProvider, $repoDir, $userRepo, $fileAccess, $yaml, $diffCalculator)
    {
        if (!isset($data['username'])) {
            throw new \Contentacle\Exceptions\RepoException("No username provided when creating repo");
        }
        if (!isset($data['name'])) {
            throw new \Contentacle\Exceptions\RepoException("No repo name provided when creating repo");
        }

        parent::__construct(array(
            'username' => '/^[a-z]{2,40}$/',
            'name' => '/^[a-z0-9-]{2,40}$/',
            'description' => true
        ), $data);

        $this->git = $gitProvider($data['username'], $data['name'].'.git');
        $this->gitProvider = $gitProvider;
        $this->repoDir = $repoDir;
        $this->userRepo = $userRepo;
        $this->fileAccess = $fileAccess;
        $this->yaml = $yaml;
        $this->diffCalculator = $diffCalculator;

        $user = $userRepo->getUser($data['username']);
        $this->git->setUser($user->name, $user->email);

        if (!isset($data['description'])) {
            $this->readMetadata();
        }
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

    public function createBranch($branchName, $branchFrom)
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9 .-]+$/', $branchName)) {
            throw new \Contentacle\Exceptions\RepoException("Branch name '$branchName' is not valid");
        }
        $this->git->createBranch($branchName, $branchFrom);
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

    /**
     * Extract YAML front matter metadata from document content.
     */
    private function splitDocumentContent($document)
    {
        $metadata = array();
        $content = $document->getContent();

        if (substr($content, 0, 4) == "---\n") {
            $parts = preg_split('/\n?-{3}\n/', $content, 3);
            $content = $parts[2];
            $metadata = $this->yaml->decode($parts[1]);
        }

        return array(
            $content,
            $metadata
        );
    }

    public function document($branch = 'master', $path = '')
    {
        $this->git->setBranch($branch);
        try {
            $document = $this->git->file($path);
            if (is_a($document, '\Git\Blob')) {
                
                list($content, $metadata) = $this->splitDocumentContent($document);
                
                return array(
                    'filename' => basename($document->filename),
                    'path' => $document->filename,
                    'dir' => false,
                    'sha' => $document->sha,
                    'authorname' => $this->userRepo->getUsernameFromEmail($document->email),
                    'email' => $document->email,
                    'author' => $document->user,
                    'date' => $document->date,
                    'branch' => $branch,
                    'commit' => $this->git->log($path)[0],
                    'content' => $content,
                    'metadata' => $metadata
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
                        'authorname' => $this->userRepo->getUsernameFromEmail($commit->email),
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
                'authorname' => $this->userRepo->getUsernameFromEmail($commit->email),
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
        $diffs = array();

        foreach ($commit->diff->diff as $filename => $lines) {
            $diffs[$filename] = $this->diffCalculator->calculate($lines);
        }

        return array(
            'sha' => $commit->sha,
            'parents' => $commit->parents,
            'message' => $commit->message,
            'date' => $commit->date,
            'authorname' => $this->userRepo->getUsernameFromEmail($commit->email),
            'author' => $commit->user,
            'email' => $commit->email,
            'files' => $commit->getFiles(),
            'diffs' => $diffs
        );
    }

    /**
     * Is the given sha the head of the given branch
     * @param str $branch
     * @param str $sha
     * @return bool
     */
    public function isHead($branch, $sha)
    {
        return $this->git->dereference('refs/heads/'.$branch) == $sha;
    }

    /**
     * Get the path of the repos .git/description file
     * @return str
     */
    private function metadataPath()
    {
        return $this->repoDir.'/'.$this->prop('username').'/'.$this->prop('name').'.git/description';
    }

    /**
     * Read the repos metadata from the .git/description file
     */
    private function readMetadata()
    {
        $this->setProp('description', trim($this->fileAccess->read($this->metadataPath())));
    }

    /**
     * Write the repos metadata into the .git/description file
     * @return bool
     */
    public function writeMetadata()
    {
        return $this->fileAccess->write($this->metadataPath(), $this->prop('description')."\n");
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
            $this->git->resetIndex();
            throw new \Contentacle\Exceptions\RepoException('Could not create document "'.$path.'"');
        }
    }

    public function updateDocument($branch, $path, $content, $commitMessage = null, $updatedPath = null)
    {
        if (!$commitMessage) {
            $commitMessage = 'Update '.$path;
        }
        $this->git->setBranch($branch);
        try {
            if ($updatedPath) {
                $this->git->move($path, $updatedPath);
                $path = $updatedPath;
            }
            $this->git->update($path, $content, $commitMessage);
        } catch (\Git\Exception $e) {
            $this->git->resetIndex();
            throw new \Contentacle\Exceptions\RepoException('Could not update document "'.$path.'"');
        }
    }

    public function deleteDocument($branch, $path, $commitMessage = null)
    {
        if (!$commitMessage) {
            $commitMessage = 'Delete '.$path;
        }
        $this->git->setBranch($branch);
        if (!$this->git->remove($path, $commitMessage)) {
            $this->git->resetIndex();
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

    public function undo($sha, $commitMessage)
    {
        return $this->git->revert($sha, $commitMessage);
    }

    public function revert($sha, $commitMessage)
    {
        try {
            return $this->git->undo($sha, $commitMessage);
        } catch (\Git\Exception $e) {
            return false;
        }
    }

    public function parentRepo()
    {
        $remotes = $this->git->command('remote -v');
        preg_match('/origin\t.+\/(.+)\/(.+)\.git \(fetch\)/', $remotes, $match);
        if ($match) {
            return array(
                'username' => $match[1],
                'repoName' => $match[2]
            );
        }
    }

    public function fork($username)
    {
        $repoDir = $this->repoDir.'/'.$this->username.'/'.$this->name.'.git';
        $forkDir = $this->repoDir.'/'.$username.'/'.$this->name.'.git';

        if (file_exists($forkDir)) {
            throw new \Contentacle\Exceptions\RepoException('User "'.$username.'" already has a fork of "'.$this->username.'/'.$this->name.'"');
        }

        try {
            $this->git->command('clone -l -q --bare -b master -- '.escapeshellcmd($repoDir).' '.escapeshellcmd($forkDir));
            $clone = new Repo(array(
                'name' => $this->name,
                'username' => $username,
                'description' => $this->description
            ), $this->gitProvider, $this->repoDir, $this->userRepo, $this->fileAccess, $this->yaml, $this->diffCalculator);
            $clone->writeMetadata();
            return true;
        } catch (\Git\Exception $e) {
            return false;
        }
    }

}