<?php

namespace spec\Contentacle\Models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepoSpec extends ObjectBehavior
{
    function let(\Git\Repo $repo, \Contentacle\Services\Yaml $yaml, \Git\Tree $rootTree, \Git\Tree $subTree, \Git\Blob $totem)
    {
        $repo->file('contentacle.yaml')->willReturn('Contentacle YAML');
        $yaml->decode(Argument::any())->willReturn(array(
            'title' => 'Extraction 101',
            'description' => 'Extraction instructions for Ariadne'
        ));
        $repo->getBranches()->willReturn(array('master', 'arthur'));
        $repo->setBranch('master')->willReturn();
        
        $rootTree->entries()->willReturn(array(
            'totem.txt' => (object)array('filename' => 'totem.txt')
        ));
        $repo->tree('')->willReturn($rootTree);

        $subTree->entries()->willReturn(array(
            'mr-charles.txt' => (object)array('filename' => 'mr-charles.txt')
        ));
        $repo->tree('new-york/the-hotel')->willReturn($subTree);

        $totem->getContent()->willReturn('A Totem is an object that is used to test if oneself is in one\'s own reality and not in another person\'s dream.');
        $repo->tree('totem.txt')->willReturn(null);
        $repo->file('totem.txt')->willReturn($totem);

        $data = array(
            'username' => 'cobb',
            'name' => 'extraction'
        );
        $gitProvider = function ($username, $repoName) use ($repo) {
            return $repo->getWrappedObject();
        };
        $this->beConstructedWith($data, $gitProvider, $yaml);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Models\Repo');
    }

    function it_should_load_branch_data_from_the_repo()
    {
        $this->loadBranches();
        $this->branches->shouldBe(array(
            'master' => array(
                'name' => 'master',
                'url' => '/users/cobb/repos/extraction/branches/master'
            ),
            'arthur' => array(
                'name' => 'arthur',
                'url' => '/users/cobb/repos/extraction/branches/arthur'
            )
        ));
    }

    function it_should_be_able_to_check_if_a_branch_exists()
    {
        $this->hasBranch('arthur')->shouldBe(true);
        $this->hasBranch('eames')->shouldBe(false);
    }

    function it_should_load_document_metadata()
    {
        $this->loadDocuments();
        $this->documents->shouldBe(array(
            'totem.txt' => array(
                'url' => '/users/cobb/repos/extraction/branches/master/documents/totem.txt',
                'filename' => 'totem.txt'
            )
        ));
        $this->document->shouldBe(null);
    }

    function it_should_load_document_metadata_from_within_a_subdirectory()
    {
        $this->loadDocuments('master', 'new-york/the-hotel');
        $this->documents->shouldBe(array(
            'mr-charles.txt' => array(
                'url' => '/users/cobb/repos/extraction/branches/master/documents/mr-charles.txt',
                'filename' => 'mr-charles.txt'
            )
        ));
        $this->document->shouldBe(null);
    }

    function it_should_load_a_documents_metadata()
    {
        $this->loadDocuments('master', 'totem.txt');
        $this->documents->shouldBe(null);
        $this->document['url']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/totem.txt');
        $this->document['content']->shouldBe('A Totem is an object that is used to test if oneself is in one\'s own reality and not in another person\'s dream.');
        $this->document['raw']->shouldBe('/users/cobb/repos/extraction/branches/master/raw/totem.txt');
        $this->document['history']->shouldBe('/users/cobb/repos/extraction/branches/master/history/totem.txt');
    }
}
