<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommitSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->commit('master', '123456')->willReturn(array(
            'sha' => '123456'
        ));
        $repo->commit(Argument::cetera())->willThrow(new \Git\Exception);

        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);
        
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Commit');
    }

    function it_should_link_to_itself()
    {
        $this->get('cobb', 'extraction', 'master', '123456')->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits/123456');
    }

    function it_should_output_a_commit(\Contentacle\Models\Repo $repo)
    {
        $repo->commit('master', '123456')->shouldBeCalled();
        $body = $this->get('cobb', 'extraction', 'master', '123456')->body;
        $body['sha']->shouldBe('123456');
    }

    function it_should_error_for_invalid_commit(\Contentacle\Models\Repo $repo)
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction', 'master', '123456');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception', 'master', '123456');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'branch', '123456');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'master', '111111');
    }

}
