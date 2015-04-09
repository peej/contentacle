<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo, \Contentacle\Services\Yaml $yaml)
    {
        $repo->prop('name')->willReturn('Extraction');
        $repo->prop('description')->willReturn('Extraction is the art of infiltrating the mind of any person to steal their secrets.');
        $repo->prop('username')->willReturn('cobb');
        $repo->branches()->willReturn(array(
            'master', 'branch'
        ));
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
        $repo->commits('master', null, 1)->willReturn(array(array(
            'sha' => '111111'
        )));
        $repo->commit('master', 111111)->willReturn(array(
            'username' => 'cobb'
        ));

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
            'repoRepository' => $repoRepo,
            'yaml' => $yaml
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Edit');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/edit/new-york/the-hotel/totem.txt');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->data['_links']['cont:doc']['href']->shouldBe('/rels/edit');
    }

    function it_should_show_a_single_document($repo)
    {
        $repo->document('master', 'new-york/the-hotel/totem.txt')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->data['filename']->shouldBe('totem.txt');
        $response->data['content']->shouldBe('An elegant solution for keeping track of reality.');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/edit/new-york/the-hotel/totem.txt');
        $response->data['_links']['cont:doc']['href']->shouldBe('/rels/edit');
        $response->data['_links']['cont:history']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/history/new-york/the-hotel/totem.txt');
        $response->data['_links']['cont:raw']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/raw/new-york/the-hotel/totem.txt');
        $response->data['_links']['cont:commit']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits/111111');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }

    function it_should_error_for_unknown_path($request)
    {
        $request->getUri()->willReturn('/users/cobb/repos/extraction/branches/master/edit/paris');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'master', 'paris');
    }

    function it_should_update_a_document($request)
    {
        $request->getData()->willReturn(array(
            'content' => 'I can\'t let you touch it, that would defeat the purpose.',
            'message' => 'Update the document about the totem in the New York hotel'
        ));

        $response = $this->commit('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');

        $response->code->shouldBe(302);
        $response->Location->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
    }

    function it_should_rename_a_document($request)
    {
        $request->getData()->willReturn(array(
            'filename' => 'new-york/the-hotel/wedding-ring.txt',
            'message' => 'Cobb\'s totem is his wedding ring, not the spinning top, which is Mal\'s'
        ));

        $response = $this->commit('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');

        $response->code->shouldBe(302);
        $response->Location->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/wedding-ring.txt');
    }

    function it_should_update_document_metadata($request, $repo, $yaml)
    {
        $request->getData()->willReturn(array(
            'metadata' => array(
                'name' => array(
                    'forger',
                    'chemist'
                ),
                'value' => array(
                    'Eames',
                    'Yusuf'
                )
            ),
            'content' => 'The team.',
            'message' => 'Add the team'
        ));

        $yaml->encode(array(
            'forger' => 'Eames',
            'chemist' => 'Yusuf'
        ))->willReturn("---\nforger: Eames\nchemist: Yusuf\n");

        $content = "---\nforger: Eames\nchemist: Yusuf\n---\nThe team.";

        $repo->updateDocument('master', 'team.txt', $content, 'Add the team', 'team.txt')->shouldBeCalled();

        $response = $this->commit('cobb', 'extraction', 'master', 'team.txt');

        $response->code->shouldBe(302);
        $response->Location->shouldBe('/users/cobb/repos/extraction/branches/master/documents/team.txt');
    }
}