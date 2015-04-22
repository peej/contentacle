<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BranchSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Contentacle\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('name')->willReturn('extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->prop('description')->willReturn('Extraction instructions for Ariadne');
        $repo->branches()->willReturn(array(
            'master', 'branch'
        ));
        $repo->commits(Argument::cetera())->willReturn(array(
            array()
        ));
        
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch('branch')->willReturn(true);
        $repo->hasBranch(Argument::cetera())->willThrow(new \Tonic\NotFoundException);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);

        $this->beConstructedWith(array(
            'app' => $app,
            'request' => $request,
            'response' => function($code = null, $templateName = null) {
                return new \Contentacle\Response($code, '', null, null);
            },
            'repoRepository' => $repoRepo
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Branch');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_links']['cont:doc']['href']->shouldBe('/rels/branch');
    }

    function it_should_link_to_documents()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_links']['cont:documents']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents');
    }

    function it_should_link_to_commits()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_links']['cont:commits']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits');
    }

    function it_should_show_master_branch_details()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['name']->shouldBe('master');
        $response->data['repo']->shouldBe('extraction');
        $response->data['username']->shouldBe('cobb');
    }

    function it_should_show_branch_details()
    {
        $response = $this->get('cobb', 'extraction', 'branch');
        $response->data['name']->shouldBe('branch');
        $response->data['repo']->shouldBe('extraction');
        $response->data['username']->shouldBe('cobb');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch');
        $response->data['_links']['cont:documents']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch/documents');
        $response->data['_links']['cont:commits']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch/commits');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }
}
