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

        $git->add('contentacle.yaml', $yaml->encode(array(
            'title' => 'Extraction 101',
            'description' => 'Extraction information for Ariadne'
        )), 'Initial commit');

        $repoDir = $this->repoDir;

        $this->beConstructedWith(
            $this->repoDir,
            function ($data) use ($yaml, $repoDir) {
                return new \Contentacle\Models\Repo($data, function () use ($data, $repoDir) {
                    return new \Git\Repo(
                        $repoDir.'/'.$data['username'].'/'.$data['name']
                    );
                }, $yaml);
            }
        );

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

    function it_should_create_a_new_repo(\Contentacle\Models\User $user)
    {
        $user->prop('username')->willReturn('cobb');
        $user->prop('name')->willReturn('Dominick Cobb');
        $user->prop('email')->willReturn('dominick@cobb.com');

        $repo = $this->createRepo($user, array(
            'name' => 'limbo',
            'title' => 'Limbo'
        ));
        
        $repo->shouldHaveType('Contentacle\Models\Repo');
        $repo->name->shouldBe('limbo');
        $repo->title->shouldBe('Limbo');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe(null);
        $repo->branches()[0]->shouldBe('master');

        $document = $repo->document('master', 'contentacle.yaml');
        $document['filename']->shouldBe('contentacle.yaml');

        $repo = $this->getRepo('cobb', 'limbo');
        $repo->name->shouldBe('limbo');
        $repo->title->shouldBe('Limbo');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe(null);
    }
}
