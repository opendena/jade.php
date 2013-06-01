<?php

namespace Jade\test\unit;

use \atoum;

require __DIR__.'/../src/Jade/Jade.php';
require __DIR__.'/../src/Jade/Dumper.php';
require __DIR__.'/../src/Jade/Lexer.php';
require __DIR__.'/../src/Jade/Parser.php';
require __DIR__.'/../src/Jade/Node.php';

class Jade extends atoum
{
    protected $jade;

    protected function parse($value)
    {
        if (!$this->jade) {
            $parser = new \Jade\Parser(new \Jade\Lexer());
            $dumper = new \Jade\Dumper();

            $this->jade = new \Jade\Jade($parser, $dumper);
        }

        return $this->jade->render($value);
    }

    public function testDoctypes()
    {
        $this->string('<?xml version="1.0" encoding="utf-8" ?>')
            ->isEqualTo($this->parse('!!! xml'));

        $this->string('<!DOCTYPE html>')
            ->isEqualTo($this->parse('!!! 5'))
            ->isEqualTo($this->parse('doctype html'))
            ->isEqualTo($this->parse('!!! html'));

        $this->string('<html></html>')
            ->isEqualTo($this->parse('html'));
    }

    public function testLineEndings()
    {
        $tags = array('p', 'div', 'img');
        $html = implode("\n", array('<p></p>', '<div></div>', '<img />'));

        $this->string($html)
            ->isEqualTo($this->parse(implode("\r\n", $tags)))
            ->isEqualTo($this->parse(implode("\r", $tags)))
            ->isEqualTo($this->parse(implode("\n", $tags)));
    }

    public function testSingleQuotes()
    {
        $this->string("<p>'foo'</p>")
            ->isEqualTo($this->parse("p 'foo'"));
        $this->string("<p>\n  'foo'\n</p>")
            ->isEqualTo($this->parse("p\n  | 'foo'"));
    }

    public function testBlockExpansion()
    {
        // @todo https://github.com/visionmedia/jade/blob/master/test/jade.test.js#L102
        // $this->string(
        //    '<li><a>foo</a></li><li><a>bar</a></li><li><a>baz</a></li>'
        //)
        //    ->isEqualTo($this->parse("li: a foo\nli: a bar\nli: a baz"));
    }

    public function testTags()
    {
        $str = implode("\n", array('p', 'div', 'img'));
        $html = implode("\n", array('<p></p>', '<div></div>', '<img />'));

        $this->string($html)
            ->isEqualTo($this->parse($str));
        $this->string('<fb:foo-bar></fb:foo-bar>')
            ->isEqualTo($this->parse('fb:foo-bar'));
        $this->string('<div class="something"></div>')
            ->isEqualTo($this->parse('div.something'));
        $this->string('<div id="something"></div>')
            ->isEqualTo($this->parse('div#something'));
        $this->string('<div id="item" class="something"></div>')
            ->isEqualTo($this->parse('div#item.something'));
        $this->string('<div id="foo" class="bar"></div>')
            ->isEqualTo($this->parse('#foo.bar'))
            //    ->isEqualTo($this->parse('.bar#foo'))
            ->isEqualTo($this->parse('div#foo(class="bar")'))
            ->isEqualTo($this->parse('div(class="bar")#foo'));
        //    ->isEqualTo($this->parse('div(id="bar").foo'))
        $this->string('<div class="foo bar baz"></div>')
            ->isEqualTo($this->parse('div.foo.bar.baz'));
        //    ->isEqualTo($this->parse('div(class="foo").bar.baz'))
        //    ->isEqualTo($this->parse('div.foo(class="bar").baz'))
        //    ->isEqualTo($this->parse('div.foor.bar(class="baz"'))
        $this->string('<div class="a-b2"></div>')
            ->isEqualTo($this->parse('div.a-b2'));

        $this->string('<div class="a_b2"></div>')
            ->isEqualTo($this->parse('div.a_b2'));
        $this->string('<fb:user></fb:user>')
            ->isEqualTo($this->parse('fb:user'));
        $this->string('<fb:user:role></fb:user:role>')
            ->isEqualTo($this->parse('fb:user:role'));
        // $this->string('<colgroup><col class="test"/></colgroup>')
        //     ->isEqualTo($this->parse("colgroup\n  col.test"));
    }

    public function testTabConversion()
    {
        $str = implode(
            "\n",
            array(
                'ul',
                "\tli a",
                "\t",
                "\tli b",
                "\t\t",
                "\t\t\t\t\t\t",
                "\tli",
                "\tli",
                "\t\tul",
                "\t\t\tli c",
                "",
                "\t\t\tli d",
                "\tli e"
            )
        );

        $html = implode(
            '',
            array(
                '<ul>',
                '<li>a</li>',
                '<li>b</li>',
                '<li><ul><li>c</li><li>d</li></ul></li>',
                '<li>e</li>',
                '</ul>'
            )
        );

        //$this->string($html)
        //    ->isEqualTo($this->parse($str));
    }

    public function testNewLines()
    {
        $str = implode(
            "\n",
            array(
                'ul',
                '  li a',
                '  ',
                '    ',
                '',
                ' ',
                '  li b',
                '  li',
                '    ',
                '        ',
                ' ',
                '    ul',
                '      ',
                '      li c',
                '      li d',
                '  li e'
            )
        );

        $html = implode(
            '',
            array(
                '<ul>',
                '<li>a</li>',
                '<li>b</li>',
                '<li><ul><li>c</li><li>d</li></ul></li>',
                '<li>e</li>',
                '</ul>'
            )
        );

        //$this->string($html)
        //    ->isEqualTo($this->parse($str));

        $str = implode(
            "\n",
            array(
                'html',
                ' ',
                '  head',
                '    != "test"',
                '  ',
                '  ',
                '  ',
                '  body'
            )
        );

        $html = implode(
            '',
            array(
                '<html>',
                '<head>',
                'test',
                '</head>',
                '<body></body>',
                '</html>'
            )
        );

        //$this->string($html)
        //    ->isEqualTo($this->parse($str));

        //$this->string('<foo></foo>something<bar></bar>')
        //    ->isEqualTo($this->parse('foo\n= "something"\nbar'));
        //$this->string('<foo></foo>something<bar></bar>else')
        //    ->isEqualTo($this->parse('foo\n= "something"\nbar\n= "else"'));

    }

    public function testSupportText()
    {
        //$this->string('foo\nbar\nbaz')
        //    ->isEqualTo($this->parse('| foo\n| bar\n| baz'));
        //$this->string('foo \nbar \nbaz')
        //    ->isEqualTo($this->parse('| foo \n| bar \n| baz'));
        $this->string('(hey)')
            ->isEqualTo($this->parse('| (hey)'));
        $this->string('some random text')
            ->isEqualTo($this->parse('| some random text'));
        $this->string('  foo')
            ->isEqualTo($this->parse('|   foo'));
        $this->string('  foo  ')
            ->isEqualTo($this->parse('|   foo  '));
        //$this->string('  foo  \n bar    ')
        //    ->isEqualTo($this->parse('|   foo  \n|  bar    '));
    }

    public function testPipeLessText()
    {
        //$this->string('<pre><code><foo></foo><bar></bar></code></pre>')
        //    ->isEqualTo($this->parse('pre\n  code\n    foo\n\n    bar'));
        //$this->string('<p>foo\n\nbar</p>')
        //    ->isEqualTo($this->parse('p.\n  foo\n\n  bar'));
        //$this->string('<p>foo\n\n\n\nbar</p>')
        //    ->isEqualTo($this->parse('p.\n  foo\n\n\n\n  bar'));
        //$this->string('<p>foo\n  bar\nfoo</p>')
        //    ->isEqualTo($this->parse('p.\n  foo\n    bar\n  foo'));
        //$this->string('<script>s.parentNode.insertBefore(g,s)</script>')
        //    ->isEqualTo($this->parse('script.\n  s.parentNode.insertBefore(g,s)\n'));
        //$this->string('<script>s.parentNode.insertBefore(g,s)</script>')
        //    ->isEqualTo($this->parse('script.\n  s.parentNode.insertBefore(g,s)'));
    }

    public function testTagText()
    {
        $this->string('<p>some random text</p>')
            ->isEqualTo($this->parse('p some random text'));
        //$this->string('<p>click<a>Google</a>.</p>')
        //    ->isEqualTo($this->parse('p\n  | click\n  a Google\n  | .'));
        $this->string('<p>(parens)</p>')
            ->isEqualTo($this->parse('p (parens)'));
        $this->string('<p foo="bar">(parens)</p>')
            ->isEqualTo($this->parse('p(foo="bar") (parens)'));
        // $this->string('<option value="">-- (optional) foo --</option>')
        //    ->isEqualTo($this->parse('option(value="") -- (optional) foo --'));
    }
}
