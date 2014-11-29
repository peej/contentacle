<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BranchSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo, \Contentacle\Services\Yaml $yaml)
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

        $this->beConstructedWith($app, $request);
        $this->setRepoRepository($repoRepo);
        $this->setHalResponse(function($code = null, $body = null, $headers = array()) use ($yaml) {
            return new \Contentacle\Responses\Hal($yaml, $code, $body, $headers);
        });
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Branch');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['_links']['cont:doc']['href']->shouldBe('/rels/branch');
    }

    function it_should_link_to_documents()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['_links']['cont:document']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents');
    }

    function it_should_link_to_commits()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['_links']['cont:commits']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits');
    }

    function it_should_show_master_branch_details()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['name']->shouldBe('master');
        $response->body['repo']->shouldBe('extraction');
        $response->body['username']->shouldBe('cobb');
    }

    function it_should_show_branch_details()
    {
        $response = $this->get('cobb', 'extraction', 'branch');
        $response->body['name']->shouldBe('branch');
        $response->body['repo']->shouldBe('extraction');
        $response->body['username']->shouldBe('cobb');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch');
        $response->body['_links']['cont:document']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch/documents');
        $response->body['_links']['cont:commits']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch/commits');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }
}
