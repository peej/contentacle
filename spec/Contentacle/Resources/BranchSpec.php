<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BranchSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('name')->willReturn('extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->prop('title')->willReturn('Extraction 101');
        $repo->prop('description')->willReturn('Extraction instructions for Ariadne');
        
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch('branch')->willReturn(true);
        $repo->hasBranch(Argument::cetera())->willThrow(new \Tonic\NotFoundException);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);

        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Branch');
    }

    function it_should_link_to_itself()
    {
        $this->get('cobb', 'extraction', 'master')->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master');
    }

    function it_should_link_to_edit_method() {
        $body = $this->get('cobb', 'extraction', 'master')->body;
        $body['_links']['cont:edit-branch']['method']->shouldBe('patch');
        $body['_links']['cont:edit-branch']['content-type']->shouldContain('application/json-patch+yaml');
        $body['_links']['cont:edit-branch']['content-type']->shouldContain('application/json-patch+json');
    }

    function it_should_link_to_delete_method() {
        $body = $this->get('cobb', 'extraction', 'master')->body;
        $body['_links']['cont:delete-branch']['method']->shouldBe('delete');
    }

    function it_should_show_master_branch_details()
    {
        $body = $this->get('cobb', 'extraction', 'master')->body;
        $body['name']->shouldBe('master');
        $body['repo']->shouldBe('extraction');
        $body['username']->shouldBe('cobb');
        $body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master');
        $body['_links']['cont:documents']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents');
        $body['_links']['cont:commits']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits');
    }

    function it_should_show_branch_details()
    {
        $body = $this->get('cobb', 'extraction', 'branch')->body;
        $body['name']->shouldBe('branch');
        $body['repo']->shouldBe('extraction');
        $body['username']->shouldBe('cobb');
        $body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch');
        $body['_links']['cont:documents']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch/documents');
        $body['_links']['cont:commits']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch/commits');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }
}
