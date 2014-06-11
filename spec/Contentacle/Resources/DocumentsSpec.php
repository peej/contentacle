<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DocumentsSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->documents('master', null)->willReturn(array('new-york'));
        $repo->documents('master', 'new-york')->willReturn(array('new-york/the-hotel'));
        $repo->documents('master', 'new-york/the-hotel')->willReturn(array('new-york/the-hotel/totem.txt'));
        $repo->documents(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);
        $repo->document('master', 'new-york/the-hotel/totem.txt')->willReturn(array(
            'path' => 'new-york/the-hotel/totem.txt',
            'filename' => 'totem.txt'
        ));
        $repo->document(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Documents');
    }

    function it_should_link_to_itself()
    {
        $this->get('cobb', 'extraction', 'master')->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents');
    }

    function it_should_link_to_add_method() {
        $body = $this->get('cobb', 'extraction', 'master')->body;
        $body['_links']['cont:add-document']['method']->shouldBe('post');
        $body['_links']['cont:add-document']['content-type']->shouldContain('application/hal+yaml');
        $body['_links']['cont:add-document']['content-type']->shouldContain('application/hal+json');
    }

    function it_should_link_to_update_method() {
        $body = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt')->body;
        $body['_links']['cont:update-document']['method']->shouldBe('patch');
        $body['_links']['cont:update-document']['content-type']->shouldContain('application/json-patch+yaml');
        $body['_links']['cont:update-document']['content-type']->shouldContain('application/json-patch+json');
    }

    function it_should_link_to_edit_method() {
        $body = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt')->body;
        $body['_links']['cont:edit-document']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/raw/new-york/the-hotel/totem.txt');
        $body['_links']['cont:edit-document']['method']->shouldBe('put');
        $body['_links']['cont:edit-document']['content-type']->shouldContain('*/*');
    }

    function it_should_link_to_delete_method() {
        $body = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt')->body;
        $body['_links']['cont:delete-document']['method']->shouldBe('delete');
    }

    function it_should_show_document_listing($repo)
    {
        $repo->documents('master', null)->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body['filename']->shouldBe('');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents');
        $response->body['_embedded']['documents'][0]['filename']->shouldBe('new-york');
    }

    function it_should_show_document_listing_within_a_subdirectory($repo)
    {
        $repo->documents('master', 'new-york/the-hotel')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel');
        $response->body['filename']->shouldBe('the-hotel');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel');
        $response->body['_embedded']['documents'][0]['filename']->shouldBe('totem.txt');
    }

    function it_should_show_a_single_document($repo)
    {
        $repo->document('master', 'new-york/the-hotel/totem.txt')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->body['filename']->shouldBe('totem.txt');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
        $response->body['_links']['cont:history']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/history/new-york/the-hotel/totem.txt');
        $response->body['_links']['cont:raw']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/raw/new-york/the-hotel/totem.txt');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }

    function it_should_error_for_unknown_path()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'master', 'paris');
    }
}
