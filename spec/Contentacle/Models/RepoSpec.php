<?php

namespace spec\Contentacle\Models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepoSpec extends ObjectBehavior
{
    function let(\Git\Repo $repo, \Contentacle\Services\Yaml $yaml, \Git\Tree $rootTree, \Git\Tree $subTree, \Git\Blob $totem, \Git\Commit $commit)
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
        $repo->file('totem.txt')->willReturn($totem);
        $repo->tree('totem.txt')->willReturn(null);

        $totem->getHistory()->willReturn(array(
            (object)array(
                'sha' => '123456',
                'message' => 'Added information about forever spinning totems',
                'date' => '1392493822',
                'user' => 'cobb',
                'email' => 'cobb@localhost'
            )
        ));

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
        $documents = $this->documents();
        $documents->shouldBe(array(
            'totem.txt' => array(
                'url' => '/users/cobb/repos/extraction/branches/master/documents/totem.txt',
                'filename' => 'totem.txt'
            )
        ));
    }

    function it_should_load_document_metadata_from_within_a_subdirectory()
    {
        $documents = $this->documents('master', 'new-york/the-hotel');
        $documents->shouldBe(array(
            'mr-charles.txt' => array(
                'url' => '/users/cobb/repos/extraction/branches/master/documents/mr-charles.txt',
                'filename' => 'mr-charles.txt'
            )
        ));
    }

    function it_should_load_a_documents_metadata()
    {
        $document = $this->document('master', 'totem.txt');
        $document['url']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/totem.txt');
        $document['content']->shouldBe('A Totem is an object that is used to test if oneself is in one\'s own reality and not in another person\'s dream.');
        $document['raw']->shouldBe('/users/cobb/repos/extraction/branches/master/raw/totem.txt');
        $document['history']->shouldBe('/users/cobb/repos/extraction/branches/master/history/totem.txt');
    }

    function it_should_load_a_documents_history()
    {
        $history = $this->history('master', 'totem.txt');
        $history[0]['sha']->shouldBe('123456');
        $history[0]['message']->shouldBe('Added information about forever spinning totems');
        $history[0]['date']->shouldBe('1392493822');
        $history[0]['username']->shouldBe('cobb');
        $history[0]['email']->shouldBe('cobb@localhost');
        $history[0]['url']->shouldBe('/users/cobb/repos/extraction/branches/master/commits/123456');
    }
}
