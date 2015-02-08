<?php

namespace spec\Contentacle;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResponseSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Response');
    }

    function it_should_contain_data()
    {
        $this->addData(array('one' => 'foo', 'two' => 'bar'));
        $this->addData('three', 'baz');
        $this->data['one']->shouldBe('foo');
        $this->data['two']->shouldBe('bar');
        $this->data['three']->shouldBe('baz');
    }

    function it_should_cast_object_data_to_an_array()
    {
        $this->addData((object)array('one' => 'foo', 'two' => 'bar'));
        $this->data['one']->shouldBe('foo');
        $this->data['two']->shouldBe('bar');
    }

    function it_should_handle_model_objects()
    {
        $model = new \Contentacle\Models\Model(
            array('one' => true, 'two' => true),
            array('one' => 'foo', 'two' => 'bar')
        );
        $this->addData($model);
        $this->data['one']->shouldBe('foo');
        $this->data['two']->shouldBe('bar');
    }

    function it_should_contain_links()
    {
        $this->addLink('rel', 'href', true, 'title');
        $this->data['_links']['rel']['href']->shouldBe('href');
        $this->data['_links']['rel']['templated']->shouldBe(true);
        $this->data['_links']['rel']['title']->shouldBe('title');
    }

    function it_should_contain_embedded_resources()
    {
        $this->embed('rel', 'document');
        $this->data['_embedded']['rel'][0]->shouldBe('document');
    }

    function it_should_contain_error_messages()
    {
        $this->addError('title');
        $this->data['_embedded']['cont:error'][0]['logref']->shouldBe('title');
        $this->data['_embedded']['cont:error'][0]['message']->shouldBe('"title" field failed validation');
    }

    function it_should_have_a_default_output_format_of_hal_yaml()
    {
        $this->contentType->shouldBe('application/hal+yaml');
    }
}