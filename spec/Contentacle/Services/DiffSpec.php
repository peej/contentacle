<?php

namespace spec\Contentacle\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DiffSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new \cogpowered\FineDiff\Granularity\Word);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Services\Diff');
    }

    function it_should_show_an_added_line()
    {
        $calc = $this->calculate(array(
            '1,1 Line one',
            '2,+ Added line',
            '3,2 Line two'
        ));

        $calc[0]['from']->shouldBe('1');
        $calc[0]['to']->shouldBe('1');
        $calc[0]['text']->shouldBe('Line one');
        
        $calc[1]['from']->shouldBe('2');
        $calc[1]['to']->shouldBe('+');
        $calc[1]['text']->shouldBe('Added line');
        
        $calc[2]['from']->shouldBe('3');
        $calc[2]['to']->shouldBe('2');
        $calc[2]['text']->shouldBe('Line two');
    }

    function it_should_show_a_removed_line()
    {
        $calc = $this->calculate(array(
            '1,1 Line one',
            '-,2 Removed line',
            '2,3 Line two'
        ));

        $calc[0]['from']->shouldBe('1');
        $calc[0]['to']->shouldBe('1');
        $calc[0]['text']->shouldBe('Line one');
        
        $calc[1]['from']->shouldBe('-');
        $calc[1]['to']->shouldBe('2');
        $calc[1]['text']->shouldBe('Removed line');
        
        $calc[2]['from']->shouldBe('2');
        $calc[2]['to']->shouldBe('3');
        $calc[2]['text']->shouldBe('Line two');
    }

    function it_should_show_a_changed_line()
    {
        $calc = $this->calculate(array(
            '1,1 Line one',
            '-,2 Line two',
            '2,+ Changed line',
            '3,3 Line three'
        ));

        $calc[0]['from']->shouldBe('1');
        $calc[0]['to']->shouldBe('1');
        $calc[0]['text']->shouldBe('Line one');
        
        $calc[1]['from']->shouldBe('-');
        $calc[1]['to']->shouldBe('2');
        $calc[1]['text']->shouldBe('Line two');

        $calc[2]['from']->shouldBe('2');
        $calc[2]['to']->shouldBe('+');
        $calc[2]['text']->shouldBe('Changed line');
        
        $calc[3]['from']->shouldBe('3');
        $calc[3]['to']->shouldBe('3');
        $calc[3]['text']->shouldBe('Line three');
    }

    function it_should_show_inline_changes_of_a_small_line_change()
    {
        $calc = $this->calculate(array(
            '1,1 Line one',
            '-,2 Line nummer two',
            '2,+ Line number two',
            '3,3 Line three'
        ));

        $calc[0]['from']->shouldBe('1');
        $calc[0]['to']->shouldBe('1');
        $calc[0]['text']->shouldBe('Line one');
        
        $calc[1]['from']->shouldBe('-');
        $calc[1]['to']->shouldBe('2');
        $calc[1]['text']->shouldBe('Line <del>nummer </del>two');

        $calc[2]['from']->shouldBe('2');
        $calc[2]['to']->shouldBe('+');
        $calc[2]['text']->shouldBe('Line <ins>number </ins>two');
        
        $calc[3]['from']->shouldBe('3');
        $calc[3]['to']->shouldBe('3');
        $calc[3]['text']->shouldBe('Line three');
    }

}