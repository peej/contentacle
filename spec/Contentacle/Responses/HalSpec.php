<?php

namespace spec\Contentacle\Responses;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HalSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Responses\Hal');
    }

    function it_should_contain_data()
    {
        $this->beConstructedWith(200, array('one' => 'foo', 'two' => 'bar'));
        $this->body['one']->shouldBe('foo');
        $this->body['two']->shouldBe('bar');
    }

    function it_should_cast_object_data_to_an_array()
    {
        $this->beConstructedWith(200, (object)array('one' => 'foo', 'two' => 'bar'));
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

    function it_should_contain_forms()
    {
        $this->addForm('rel', 'post', null, 'mimetype', 'title');
        $this->body['_links']['rel']['method']->shouldBe('post');
        $this->body['_links']['rel']['content-type'][0]->shouldBe('mimetype+yaml');
        $this->body['_links']['rel']['content-type'][1]->shouldBe('mimetype+json');
        $this->body['_links']['rel']['title']->shouldBe('title');
    }

    function it_should_contain_forms_with_the_self_href()
    {
        $this->addLink('self', '/url', false, 'title');
        $this->addForm('rel', 'post', null, 'mimetype', 'title');
        $this->body['_links']['rel']['method']->shouldBe('post');
        $this->body['_links']['rel']['href']->shouldBe('/url');
        $this->body['_links']['rel']['content-type'][0]->shouldBe('mimetype+yaml');
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
}
