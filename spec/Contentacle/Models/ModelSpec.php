<?php

namespace spec\Contentacle\Models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ModelSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(array(
            'test' => '/^[a-z]+$/'
        ), array(
            'test' => 'qwerty'
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Models\Model');
    }

    function it_should_get_properties()
    {
        $this->test->shouldBe('qwerty');
        $this->prop('test')->shouldBe('qwerty');
    }

    function it_should_set_properties()
    {
        $this->setProp('test', 'uiop');
        $this->prop('test')->shouldBe('uiop');
    }

    function it_should_fail_to_set_invalid_properties()
    {
        $this->shouldThrow('Contentacle\Exceptions\ValidationException')->duringSetProp('test', 'uiop1234');
        $this->prop('test')->shouldBe('qwerty');
    }

    function it_should_set_multiple_properties()
    {
        $this->setProps(array(
            'test' => 'uiop'
        ));
        $this->prop('test')->shouldBe('uiop');
    }

    function it_should_allow_to_be_patched()
    {
        $this->patch(array(
            array(
                'op' => 'replace',
                'path' => 'test',
                'value' => 'uiop'
            )
        ));
        $this->prop('test')->shouldBe('uiop');
    }

}
