<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DocumentSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo, \Contentacle\Services\Yaml $yaml)
    {
        $repo->documents('master', null)->willReturn(array('new-york'));
        $repo->documents('master', 'new-york')->willReturn(array('new-york/the-hotel'));
        $repo->documents('master', 'new-york/the-hotel')->willReturn(array('new-york/the-hotel/totem.txt'));
        $repo->documents(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);
        $repo->document('master', 'new-york/the-hotel/totem.txt')->willReturn(array(
            'path' => 'new-york/the-hotel/totem.txt',
            'filename' => 'totem.txt',
            'content' => 'An elegant solution for keeping track of reality.',
            'username' => 'cobb',
            'sha' => '123456',
            'commit' => '111111'
        ));
        $repo->document(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);
        $repo->updateDocument('master', 'kick.txt', Argument::any(), Argument::any())->willThrow(new \Contentacle\Exceptions\RepoException);
        $repo->updateDocument(Argument::cetera())->will(function ($args) {
            $this->document($args[0], $args[1])->willReturn(array(
                'path' => $args[1],
                'filename' => basename($args[1]),
                'content' => $args[2],
                'username' => 'cobb',
                'sha' => '654321',
                'commit' => '111111'
            ));
        });
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
        $repo->deleteDocument('master', 'new-york/the-hotel/totem.txt', Argument::any())->willReturn();
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);

        $request->getAccept()->willReturn(array());
        $request->getParams()->willReturn(array());

        $this->beConstructedWith($app, $request);
        $this->setRepoRepository($repoRepo);
        $this->setHalResponse(function($code = null, $body = null, $headers = array()) use ($yaml) {
            return new \Contentacle\Responses\Hal($yaml, $code, $body, $headers);
        });
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Document');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['_links']['cont:doc']['href']->shouldBe('/rels/document');
    }

    function it_should_show_document_listing($repo)
    {
        $repo->documents('master', null)->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['_embedded']['cont:document'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york');
        $response->body['_embedded']['cont:document'][0]['filename']->shouldBe('new-york');
    }

    function it_should_show_document_listing_within_a_subdirectory($repo)
    {
        $repo->documents('master', 'new-york/the-hotel')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel');
        $response->body['_embedded']['cont:document'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
        $response->body['_embedded']['cont:document'][0]['filename']->shouldBe('totem.txt');
        $response->body['_embedded']['cont:document'][0]['username']->shouldBe('cobb');
        $response->body['_embedded']['cont:document'][0]['_links']['cont:user']['href']->shouldBe('/users/cobb');
    }

    function it_should_show_a_single_document($repo)
    {
        $repo->document('master', 'new-york/the-hotel/totem.txt')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->body['filename']->shouldBe('totem.txt');
        $response->body['content']->shouldBe('An elegant solution for keeping track of reality.');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
        $response->body['_links']['cont:doc']['href']->shouldBe('/rels/document');
        $response->body['_links']['cont:history']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/history/new-york/the-hotel/totem.txt');
        $response->body['_links']['cont:raw']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/raw/new-york/the-hotel/totem.txt');
        $response->body['_links']['cont:commit']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits/111111');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }

    function it_should_error_for_unknown_path()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'master', 'paris');
    }

    function it_should_create_a_document($request)
    {
        $request->getData()->willReturn(array(
            'content' => 'It\'s that feeling of falling you get that jolts you awake. It snaps you out of a dream.',
            'message' => 'Create a new document about the kick.'
        ));

        $response = $this->createDocument('cobb', 'extraction', 'master', 'kick.txt');

        $response->getCode()->shouldBe(201);
        $response->body['filename']->shouldBe('kick.txt');
        $response->body['content']->shouldBe('It\'s that feeling of falling you get that jolts you awake. It snaps you out of a dream.');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/kick.txt');
    }

    function it_should_update_a_document($request)
    {
        $request->getData()->willReturn(array(
            'content' => 'I can\'t let you touch it, that would defeat the purpose.',
            'message' => 'Update the document about the totem in the New York hotel'
        ));

        $response = $this->createDocument('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');

        $response->getCode()->shouldBe(200);
        $response->body['filename']->shouldBe('totem.txt');
        $response->body['content']->shouldBe('I can\'t let you touch it, that would defeat the purpose.');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
    }

    function it_should_delete_a_document($request)
    {
        $request->getData()->willReturn(array(
            'message' => 'Remove totem document.'
        ));
        $response = $this->deleteDocument('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->getCode()->shouldBe(204);
    }
}
