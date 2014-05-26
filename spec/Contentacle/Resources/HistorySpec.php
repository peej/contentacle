<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HistorySpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->history('master', 'totem.txt')->willReturn(array());
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\History');
    }

    function it_should_show_history_listing($repo)
    {
        $repo->history('master', 'totem.txt')->willReturn('history')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'totem.txt');
        $response->body->shouldBe('history');
    }

}
