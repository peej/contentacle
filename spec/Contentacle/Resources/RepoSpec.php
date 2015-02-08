<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepoSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('name')->willReturn('extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->prop('title')->willReturn('Extraction 101');
        $repo->prop('description')->willReturn('Extraction instructions for Ariadne');
        $repo->props()->willReturn(array(
            'name' => 'extraction',
            'username' => 'cobb',
            'title' => 'Extraction 101',
            'description' => 'Extraction instructions for Ariadne'
        ));
        $repo->branches()->willReturn(array('master', 'branch'));
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch('branch')->willReturn(true);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);

        $this->beConstructedWith($app, $request);
        $this->setRepoRepository($repoRepo);
        $this->setResponse(function($code = null, $templateName = null) {
            return new \Contentacle\Response($code);
        });
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Repo');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction');
        $response->data['_links']['cont:doc']['href']->shouldBe('/rels/repo');
    }

    function it_should_link_to_branches()
    {
        $response = $this->get('cobb', 'extraction');
        $response->data['_links']['cont:branches']['href']->shouldBe('/users/cobb/repos/extraction/branches');
    }

    function it_should_get_a_list_of_a_users_repos()
    {
        $response = $this->get('cobb', 'extraction');
        
        $response->data['_embedded']['cont:branch']->shouldHaveCount(2);
        $response->data['_embedded']['cont:branch'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master');
        $response->data['_embedded']['cont:branch'][0]['name']->shouldBe('master');
        $response->data['_embedded']['cont:branch'][1]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/branch');
        $response->data['_embedded']['cont:branch'][1]['name']->shouldBe('branch');
        
    }

    function it_should_error_for_unknown_repo()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception');
    }
}
