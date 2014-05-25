<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepoSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('url')->willReturn('/users/cobb/repos/extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->prop('name')->willReturn('extraction');
        $repo->prop('title')->willReturn('Extraction 101');
        $repo->prop('description')->willReturn('Extraction instructions for Ariadne');
        $repo->loadBranches()->will(function () use ($repo) {
            $repo->prop('branches')->willReturn(array(array('name' => 'master'), array('name' => 'branch')));
        });
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Repo');
    }

    function it_should_get_a_list_of_a_users_repos()
    {
        $response = $this->get('cobb', 'extraction');
        $response->body->prop('url')->shouldBe('/users/cobb/repos/extraction');
        $response->body->prop('name')->shouldBe('extraction');
        $response->body->prop('title')->shouldBe('Extraction 101');
        $response->body->prop('description')->shouldBe('Extraction instructions for Ariadne');
        $response->body->prop('username')->shouldBe('cobb');
        
        $response->body->prop('branches')->shouldHaveCount(2);
        $response->body->prop('branches')[0]['name']->shouldBe('master');
        $response->body->prop('branches')[1]['name']->shouldBe('branch');
    }
}
