<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BranchesSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
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
        $this->shouldHaveType('Contentacle\Resources\Branches');
    }

    function it_should_list_branches()
    {
        $response = $this->get('cobb', 'extraction');
        $response->body[0]['name']->shouldBe('master');
        $response->body[1]['name']->shouldBe('branch');
    }
}
