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
        @mkdir($this->repoDir);
        @mkdir($this->repoDir.'/cobb');

        $yaml = new \Contentacle\Services\Yaml;
        $git = new \Git\Repo(
            $this->repoDir.'/cobb/extraction'
        );

        $this->beConstructedWith(
            $this->repoDir,
            function ($data) use ($git, $yaml) {
                return new \Contentacle\Models\Repo($data, function () use ($git) {
                    return $git;
                }, $yaml);
            }
        );

        $git->add('contentacle.yaml', $yaml->encode(array(
            'title' => 'Extraction 101',
            'description' => 'Extraction information for Ariadne'
        )), 'Initial commit');

    }

    function letgo()
    {
        exec('rm -rf '.$this->repoDir);
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
        $repo->name->shouldBe('extraction');
        $repo->title->shouldBe('Extraction 101');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('Extraction information for Ariadne');
    }

    function it_should_retrieve_a_given_repo_for_a_given_user()
    {
        $repo = $this->getRepo('cobb', 'extraction');
        $repo->shouldHaveType('Contentacle\Models\Repo');
        $repo->name->shouldBe('extraction');
        $repo->title->shouldBe('Extraction 101');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('Extraction information for Ariadne');
    }
}
