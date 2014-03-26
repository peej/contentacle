<?php

namespace spec\Contentacle\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepoRepositorySpec extends ObjectBehavior
{
    private $repoDir;

    function __construct()
    {
        $this->repoDir = sys_get_temp_dir().'/contentacle';
    }
    
    function let()
    {
        $yaml = new \Contentacle\Services\Yaml;

        $this->beConstructedWith(
            $this->repoDir,
            function ($data) {
                return new \Contentacle\Models\Repo($data, function ($username, $repoName) {
                    return new \Git\Repo($this->repoDir.'/'.$username.'/'.$repoName);
                });
            },
            $yaml
        );
        @mkdir($this->repoDir);
        @mkdir($this->repoDir.'/cobb');
        @mkdir($this->repoDir.'/cobb/extraction');

        file_put_contents($this->repoDir.'/cobb/extraction/contentacle.yaml', $yaml->encode(array(
            'title' => 'Extraction 101',
            'description' => 'Extraction information for Ariadne'
        )));
    }

    function letgo()
    {
        unlink($this->repoDir.'/cobb/extraction/contentacle.yaml');
        rmdir($this->repoDir.'/cobb/extraction');
        rmdir($this->repoDir.'/cobb');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Services\RepoRepository');
    }

    function it_should_retrieve_repos_for_a_given_user()
    {
        $this->getRepos('cobb')->shouldHaveCount(1);
        $repo = $this->getRepos('cobb')['extraction'];
        $repo->shouldHaveType('Contentacle\Models\Repo');
        $repo->url->shouldBe('/users/cobb/repos/extraction');
        $repo->name->shouldBe('extraction');
        $repo->title->shouldBe('Extraction 101');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('Extraction information for Ariadne');
    }

    function it_should_retrieve_a_given_repo_for_a_given_user()
    {
        $repo = $this->getRepo('cobb', 'extraction');
        $repo->shouldHaveType('Contentacle\Models\Repo');
        $repo->url->shouldBe('/users/cobb/repos/extraction');
        $repo->name->shouldBe('extraction');
        $repo->title->shouldBe('Extraction 101');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('Extraction information for Ariadne');
    }
}
