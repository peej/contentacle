<?php

namespace spec\Contentacle\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TemplateSpec extends ObjectBehavior
{
    function let(\Michelf\Markdown $markdown)
    {
        $this->beConstructedWith('.', sys_get_temp_dir(), $markdown);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Services\Template');
    }

    function it_should_have_a_relation_link_helper()
    {
        $this->parse('{{{rel key}}}', array('key' => 'cont:kick'))->shouldBe('<a href="/rels/kick">cont:kick</a>');
        $this->parse('{{{rel key}}}', array('key' => 'self'))->shouldBe('self');
    }

    function it_should_have_an_uppercase_helper()
    {
        $this->parse('{{uppercase key}}', array('key' => 'mY miXedcAse sTrinG!'))->shouldBe('MY MIXEDCASE STRING!');
    }

    function it_should_have_a_capitalise_helper()
    {
        $this->parse('{{capitalise key}}', array('key' => 'my lowercase string!'))->shouldBe('My Lowercase String!');
    }

    function it_should_have_a_count_helper()
    {
        $this->parse('{{count key}}', array('key' => array()))->shouldBe('0');
        $this->parse('{{count key}}', array('key' => range(1, 1)))->shouldBe('1');
        $this->parse('{{count key "totem"}}', array('key' => array()))->shouldBe('0 totems');
        $this->parse('{{count key "totem"}}', array('key' => range(1, 1)))->shouldBe('1 totem');
        $this->parse('{{count key "totem"}}', array('key' => range(1, 2)))->shouldBe('2 totems');
    }

    function it_should_have_a_default_value_helper()
    {
        $this->parse('{{default key "saito"}}', array('key' => 'eames'))->shouldBe('eames');
        $this->parse('{{default key "saito"}}', array('key' => null))->shouldBe('saito');
        $this->parse('{{default key "saito"}}', array('key' => false))->shouldBe('saito');
        $this->parse('{{default key "saito"}}', array('key' => ''))->shouldBe('saito');
    }

    function it_should_have_a_truncate_value_helper()
    {
        $this->parse('{{truncate key}}', array('key' => 'inceptioninceptioninceptioninception'))->shouldBe('inceptioninceptioninceptioninc...');
        $this->parse('{{truncate key 6}}', array('key' => 'inception'))->shouldBe('inception');
        $this->parse('{{truncate key 5}}', array('key' => 'inception'))->shouldBe('incep...');
    }

    function it_should_have_a_markdown_helper(\Michelf\Markdown $markdown)
    {
        $markdown->transform('#Labyrinth')->willReturn('<h1>Labyrinth</h1>');
        $this->parse('{{{markdown key}}}', array('key' => '#Labyrinth'))->shouldBe('<h1>Labyrinth</h1>');
    }

    function it_should_have_a_date_helper()
    {
        $this->parse('{{{date key}}}', array('key' => '1234567890'))->shouldBe('Feb 13, \'09');
        $this->parse('{{{date key "r"}}}', array('key' => '1234567890'))->shouldBe('Fri, 13 Feb 2009 23:31:30 +0000');
    }

    function it_should_have_a_isodate_helper()
    {
        $this->parse('{{{isodate key}}}', array('key' => '1234567890'))->shouldBe('13-02-2009 23:31:30');
    }

    function it_should_have_a_since_helper()
    {
        $template = '{{since key}}';
        $this->parse($template, array('key' => time() - 1))->shouldBe('1 second ago');
        $this->parse($template, array('key' => time() - 2))->shouldBe('2 seconds ago');
        $this->parse($template, array('key' => time() - 60))->shouldBe('1 minute ago');
        $this->parse($template, array('key' => time() - 120))->shouldBe('2 minutes ago');
        $this->parse($template, array('key' => time() - 3600))->shouldBe('1 hour ago');
        $this->parse($template, array('key' => time() - 7200))->shouldBe('2 hours ago');
        $this->parse($template, array('key' => time() - 86400))->shouldBe('1 day ago');
        $this->parse($template, array('key' => time() - 172800))->shouldBe('2 days ago');
        $this->parse($template, array('key' => time() - 2592000))->shouldBe('1 month ago');
        $this->parse($template, array('key' => time() - 5184000))->shouldBe('2 months ago');
        $this->parse($template, array('key' => time() - 31536000))->shouldBe('1 year ago');
        $this->parse($template, array('key' => time() - 63072000))->shouldBe('2 years ago');
        $this->parse($template, array('key' => time() - 315360000))->shouldBe('10 years ago');
    }

    function it_should_have_a_filesize_helper()
    {
        $template = '{{size key}}';
        $this->parse($template, array('key' => '1'))->shouldBe('0.001');
        $this->parse($template, array('key' => '1234'))->shouldBe('0.004');
        $this->parse($template, array('key' => str_repeat('Cobb', 100)))->shouldBe('0.391');
        $this->parse($template, array('key' => str_repeat('Cobb', 1000)))->shouldBe('3.906');
    }

    function it_should_have_a_wordcount_helper()
    {
        $template = '{{wordcount key}}';
        $this->parse($template, array('key' => ''))->shouldBe('0');
        $this->parse($template, array('key' => 'Cobb'))->shouldBe('1');
        $this->parse($template, array('key' => 'Cobb and Mal'))->shouldBe('3');
    }

    function it_should_have_an_equals_block_helper()
    {
        $template = '{{#equal key1 key2}}Ariadne{{/equal}}';
        $this->parse($template, array('key1' => true, 'key2' => true))->shouldBe('Ariadne');
        $this->parse($template, array('key1' => true, 'key2' => false))->shouldBe('');
        $this->parse($template, array('key1' => 'Cobb', 'key2' => 'Cobb'))->shouldBe('Ariadne');
        $this->parse($template, array('key1' => 'Cobb', 'key2' => 'Eames'))->shouldBe('');
        $this->parse($template, array('key1' => 1, 'key2' => 1))->shouldBe('Ariadne');
        $this->parse($template, array('key1' => 1, 'key2' => 2))->shouldBe('');
    }

    function it_should_have_a_contains_block_helper()
    {
        $template = '{{#contains key1 key2}}Inception{{/contains}}';
        $team = array(
            'Ariadne' => 'Architect',
            'Cobb' => 'Extractor',
            'Yusuf' => 'Chemist'
        );
        $this->parse($template, array('key1' => $team, 'key2' => 'Cobb'))->shouldBe('Inception');
        $this->parse($template, array('key1' => $team, 'key2' => 'Fischer'))->shouldBe('');
    }
}
