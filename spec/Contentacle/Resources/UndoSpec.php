<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UndoSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->commit('master', '123456')->willReturn(array(
            'sha' => '123456',
            'authorname' => 'cobb',
            'message' => 'An elegant solution for keeping track of reality.',
            'files' => array('new-york/the-hotel/totem.txt')
        ));
        $repo->commit(Argument::cetera())->willThrow(new \Git\Exception);
        $repo->undo('123456', Argument::any())->willReturn('999999');

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
        $this->shouldHaveType('Contentacle\Resources\Undo');
    }

    function it_should_undo_a_commit()
    {
        $response = $this->post('cobb', 'extraction', 'master', '123456');
        $response->code->shouldBe(201);
        $response->location->shouldBe('/users/cobb/repos/extraction/branches/master/commits/999999');
    }

    function it_should_fail_to_undo_a_conflicting_commit($repo)
    {
        $repo->undo('123456', Argument::any())->willReturn(false);
        $response = $this->post('cobb', 'extraction', 'master', '123456');
        $response->code->shouldBe(409);
    }
}