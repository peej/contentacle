<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('name')->willReturn('Extraction');
        $repo->prop('description')->willReturn('Extraction is the art of infiltrating the mind of any person to steal their secrets.');
        $repo->prop('username')->willReturn('cobb');
        $repo->branches()->willReturn(array(
            'master', 'branch'
        ));
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch('eames')->willReturn(false);
        $repo->document('master', 'new-york/the-hotel/totem.txt')->willReturn(array(
            'path' => 'new-york/the-hotel/totem.txt',
            'filename' => 'totem.txt',
            'content' => 'An elegant solution for keeping track of reality.',
            'username' => 'cobb',
            'sha' => '123456',
            'commit' => '111111'
        ));
        $repo->document(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);
        $repo->createDocument(Argument::cetera())->will(function ($args) {
            $this->document($args[0], $args[1])->willReturn(array(
                'path' => $args[1],
                'filename' => basename($args[1]),
                'content' => $args[2],
                'username' => 'cobb',
                'sha' => '654321',
                'commit' => '111111'
            ));
        });
        $repo->parentRepo()->willReturn(null);

        $repoRepo->getRepo('eames', 'extraction')->willThrow(new \Contentacle\Exceptions\RepoException('Repo "eames/extraction" does not exist'));
        $repoRepo->getRepo('cobb', 'inception')->willThrow(new \Contentacle\Exceptions\RepoException('Repo "cobb/inception" does not exist'));
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);

        $request->getUri()->willReturn('/users/cobb/repos/extraction/branches/master/edit/new-york/the-hotel/totem.txt');
        $request->getAccept()->willReturn(array());
        $request->getParams()->willReturn(array());

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
        $this->shouldHaveType('Contentacle\Resources\Create');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/new/new-york/the-hotel');
    }

    function it_should_error_for_unknown_user()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('eames', 'extraction', 'master');
    }

    function it_should_error_for_unknown_repo()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception', 'master');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }

    function it_should_redirect_for_an_existing_document_path($request)
    {
        $request->getUri()->willReturn('/users/cobb/repos/extraction/branches/master/new/new-york/the-hotel/totem.txt');

        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');

        $response->code->shouldBe(302);
        $response->Location->shouldBe('/users/cobb/repos/extraction/branches/master/edit/new-york/the-hotel/totem.txt');
    }

    function it_should_create_a_document($request)
    {
        $request->getData()->willReturn(array(
            'filename' => 'kick.txt',
            'content' => 'Arthur is forced to improvise a new kick using an elevator.',
            'message' => 'Update the document about the kick in the New York hotel'
        ));

        $response = $this->commit('cobb', 'extraction', 'master', 'new-york/the-hotel');

        $response->code->shouldBe(302);
        $response->Location->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/kick.txt');
    }
}
