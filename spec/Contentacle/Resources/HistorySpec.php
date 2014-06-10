<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HistorySpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->history('master', 'new-york/the-hotel/totem.txt')->willReturn(array(
            array('sha' => '123456')
        ));
        $repo->commit('master', '123456')->willReturn(array(
            'sha' => '123456'
        ));
        
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
        $repo->history('master', 'new-york/the-hotel/totem.txt')->shouldBeCalled();
        $repo->commit('master', '123456')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->body['filename']->shouldBe('totem.txt');
        $response->body['_embedded']['commits'][0]['sha']->shouldBe('123456');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/history/new-york/the-hotel/totem.txt');
        $response->body['_links']['document']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
        $response->body['_links']['raw']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/raw/new-york/the-hotel/totem.txt');
    }

}
