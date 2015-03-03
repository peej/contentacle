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
    
    function let(
        \Contentacle\Models\Repo $extraction,
        \Contentacle\Models\Repo $limbo
    )
    {
        @mkdir($this->repoDir);
        @mkdir($this->repoDir.'/cobb');
        @mkdir($this->repoDir.'/cobb/extraction');
        @mkdir($this->repoDir.'/cobb/extraction/.git');

        $repoDir = $this->repoDir;

        $extraction->prop('name')->willReturn('extraction');
        $extraction->prop('username')->willReturn('cobb');
        $extraction->prop('description')->willReturn('Extraction information for Ariadne');

        $this->beConstructedWith(
            $this->repoDir,
            function ($data) use ($extraction, $limbo) {
                if ($data['name'] == 'extraction') {
                    return $extraction;
                }

                @mkdir($this->repoDir.'/cobb/'.$data['name']);
                @mkdir($this->repoDir.'/cobb/'.$data['name'].'/.git');
                foreach ($data as $name => $value) {
                    $limbo->prop($name)->willReturn($value);
                }
                $limbo->prop(Argument::cetera())->willReturn(null);
                return $limbo;
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
        $repo->name->shouldBe('extraction');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('Extraction information for Ariadne');
    }

    function it_should_retrieve_a_given_repo_for_a_given_user()
    {
        $repo = $this->getRepo('cobb', 'extraction');
        $repo->name->shouldBe('extraction');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('Extraction information for Ariadne');
    }

    function it_should_create_a_new_repo(\Contentacle\Models\User $user)
    {
        $user->prop('username')->willReturn('cobb');
        $user->prop('name')->willReturn('Dominick Cobb');
        $user->prop('email')->willReturn('dominick@cobb.com');

        $repo = $this->createRepo($user, array(
            'name' => 'limbo'
        ));
        
        $repo->name->shouldBe('limbo');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('No description');

        $repo = $this->getRepo('cobb', 'limbo');
        $repo->name->shouldBe('limbo');
        $repo->username->shouldBe('cobb');
        $repo->description->shouldBe('No description');
    }
}
