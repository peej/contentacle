<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BranchesSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop(Argument::any())->willReturn();
        $repo->branches()->willReturn(array('master', 'branch'));
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch('branch')->willReturn(true);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);

        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Branches');
    }

    function it_should_link_to_itself()
    {
        $this->get('cobb', 'extraction')->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches');
    }

    function it_should_link_to_create_method() {
        $body = $this->get('cobb', 'extraction')->body;
        $body['_links']['cont:create-branch']['method']->shouldBe('post');
        $body['_links']['cont:create-branch']['content-type']->shouldContain('contentacle/branch+yaml');
        $body['_links']['cont:create-branch']['content-type']->shouldContain('contentacle/branch+json');
    }

    function it_should_list_branches()
    {
        $body = $this->get('cobb', 'extraction')->body;
        $body['_embedded']['branches']->shouldHaveCount(2);
        $body['_embedded']['branches'][0]['name']->shouldBe('master');
        $body['_embedded']['branches'][1]['name']->shouldBe('branch');
    }

    function it_should_error_for_unknown_repo()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception');
    }
}
