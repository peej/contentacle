<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HistorySpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('name')->willReturn('Extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->history('master', 'new-york/the-hotel/totem.txt')->willReturn(array(
            array('sha' => '123456')
        ));
        $repo->commit('master', '123456')->willReturn(array(
            'sha' => '123456',
            'username' => 'cobb'
        ));
        $repo->branches()->willReturn(array(
            'master', 'branch'
        ));

        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);

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
        $this->shouldHaveType('Contentacle\Resources\History');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/history/new-york/the-hotel/totem.txt');
    }

    function it_should_link_to_the_document()
    {
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->data['_links']['cont:document']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
    }

    function it_should_show_history_listing($repo)
    {
        $repo->history('master', 'new-york/the-hotel/totem.txt')->shouldBeCalled();
        $repo->commit('master', '123456')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->data['_embedded']['cont:commit'][0]['sha']->shouldBe('123456');
    }

}
