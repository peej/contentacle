<?php

namespace spec\Contentacle\Responses;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HalSpec extends ObjectBehavior
{
    function let(\Contentacle\Services\Yaml $yaml)
    {
        $this->beConstructedWith($yaml);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Responses\Hal');
    }

    function it_should_contain_data(\Contentacle\Services\Yaml $yaml)
    {
        $this->beConstructedWith($yaml, 200, array('one' => 'foo', 'two' => 'bar'));
        $this->body['one']->shouldBe('foo');
        $this->body['two']->shouldBe('bar');
    }

    function it_should_cast_object_data_to_an_array(\Contentacle\Services\Yaml $yaml)
    {
        $this->beConstructedWith($yaml, 200, (object)array('one' => 'foo', 'two' => 'bar'));
        $this->body['one']->shouldBe('foo');
        $this->body['two']->shouldBe('bar');
    }

    function it_should_contain_links()
    {
        $this->addLink('rel', 'href', true, 'title');
        $this->body['_links']['rel']['href']->shouldBe('href');
        $this->body['_links']['rel']['templated']->shouldBe(true);
        $this->body['_links']['rel']['title']->shouldBe('title');
    }

    function it_should_contain_embedded_resources()
    {
        $this->embed('rel', 'document');
        $this->body['_embedded']['rel'][0]->shouldBe('document');
    }
    
    function it_should_have_a_default_output_format_of_hal_yaml()
    {
        $this->contentType->shouldBe('application/hal+yaml');
    }

    function it_should_have_the_contentacle_curie_defined()
    {
        $this->addLink('self', '/url', false, 'title');
        $this->body['_links']['curies'][0]['name']->shouldBe('cont');
        $this->body['_links']['curies'][0]['href']->shouldBe('http://contentacle.io/rels/{rel}');
    }

    function it_should_encode_output_into_yaml(\Contentacle\Services\Yaml $yaml)
    {
        $this->beConstructedWith($yaml, 200, array('one' => 'foo', 'two' => 'bar'));
        $this->output();
    }
}
