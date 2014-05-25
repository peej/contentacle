<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BranchSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->hasBranch('master')->willReturn(true);
        $repo->prop('url')->willReturn('/users/cobb/repos/extraction');
        $repo->prop('name')->willReturn('extraction');
        
        $repo->hasBranch('branch')->willReturn(true);
        
        $repo->hasBranch(Argument::cetera())->willThrow(new \Tonic\NotFoundException);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Branch');
    }

    function it_should_show_master_branch_details()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['branch']->shouldBe('master');
        $response->body['documents']->shouldBe('/users/cobb/repos/extraction/branches/master/documents');
        $response->body['commits']->shouldBe('/users/cobb/repos/extraction/branches/master/commits');
    }

    function it_should_show_branch_details()
    {
        $response = $this->get('cobb', 'extraction', 'branch');
        $response->body['branch']->shouldBe('branch');
        $response->body['documents']->shouldBe('/users/cobb/repos/extraction/branches/branch/documents');
        $response->body['commits']->shouldBe('/users/cobb/repos/extraction/branches/branch/commits');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }
}
