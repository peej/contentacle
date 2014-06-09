<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepoSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
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
        $body = $this->get('cobb', 'extraction')->body;
        $body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction');
        $body['name']->shouldBe('extraction');
        $body['title']->shouldBe('Extraction 101');
        $body['description']->shouldBe('Extraction instructions for Ariadne');
        $body['username']->shouldBe('cobb');
        
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
