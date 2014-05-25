<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReposSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo1, \Contentacle\Models\Repo $repo2)
    {
        $repo1->prop('url')->willReturn('/users/cobb/repos/extraction');
        $repo1->prop('username')->willReturn('cobb');
        $repo1->prop('name')->willReturn('extraction');
        $repo1->prop('title')->willReturn('Extraction 101');
        $repo1->prop('description')->willReturn('Extraction instructions for Ariadne');

        $repo2->prop('url')->willReturn('/users/cobb/repos/inception');
        $repo2->prop('username')->willReturn('cobb');
        $repo2->prop('name')->willReturn('inception');
        $repo2->prop('title')->willReturn('Inception');
        $repo2->prop('description')->willReturn('Notes on the concept of inception for Eames');

        $repoRepo->getRepos('cobb')->willReturn(array(
            'extraction' => $repo1,
            'inception' => $repo2
        ));
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Repos');
    }

    function it_should_get_a_list_of_a_users_repos()
    {
        $response = $this->get('cobb');
        $response->body->shouldHaveCount(2);
        $response->body['extraction']->prop('url')->shouldBe('/users/cobb/repos/extraction');
        $response->body['extraction']->prop('name')->shouldBe('extraction');
        $response->body['extraction']->prop('title')->shouldBe('Extraction 101');
        $response->body['inception']->prop('url')->shouldBe('/users/cobb/repos/inception');
    }
}
