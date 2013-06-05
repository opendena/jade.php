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

        //$this->string('<!DOCTYPE foo bar baz>')
        //    ->isEqualTo($this->parse('doctype foo bar baz'));

        $this->string('<html></html>')
            ->isEqualTo($this->parse('html'));

        //$this->string('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN>')
        //    ->isEqualTo($this->parse('doctype html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN'));
    }

    public function testLineEndings()
    {
        $tags = array('p', 'div', 'img');
        $html = implode("", array('<p></p>', '<div></div>', '<img/>'));

        $this->string($html)
            ->isEqualTo($this->parse(implode("\r\n", $tags)))
            ->isEqualTo($this->parse(implode("\r", $tags)))
            ->isEqualTo($this->parse(implode("\n", $tags)));
    }

    public function testSingleQuotes()
    {
        $this->string("<p>'foo'</p>")
            ->isEqualTo($this->parse("p 'foo'"))
            ->isEqualTo($this->parse("p\n  | 'foo'"));
    }

    public function testBlockExpansion()
    {
        // @todo https://github.com/visionmedia/jade/blob/master/test/jade.test.js#L102
        // $this->string(
        //    '<li><a>foo</a></li><li><a>bar</a></li><li><a>baz</a></li>'
        //)
        //    ->isEqualTo($this->parse("li: a foo\nli: a bar\nli: a baz"));
        //$this->string("<li class=\"first\"><a>foo</a></li><li><a>bar</a></li><li><a>baz</a></li>")
        //    ->isEqualTo($this->parse("li.first: a foo\nli: a bar\nli: a baz"));
        //$this->string('<div class="foo"><div class="bar">baz</div></div>')
        //    ->isEqualTo($this->parse(".foo: .bar baz"));
    }

    public function testTags()
    {
        $str = implode("\n", array('p', 'div', 'img'));
        $html = implode("", array('<p></p>', '<div></div>', '<img/>'));

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

    public function testTagTextBlock()
    {
        /*
        $this->string('<p>foo \nbar \nbaz</p>')
            ->isEqualTo($this->parse('p\n  | foo \n  | bar \n  | baz'));
        $this->string('<label>Password:<input/></label>')
            ->isEqualTo($this->parse('label\n  | Password:\n  input'));
        $this->string('<label>Password:<input/></label>')
            ->isEqualTo($this->parse('label Password:\n  input'));
        */
    }

    public function testTagTextInterpolation()
    {
        /*
        $this->string('yo, jade is cool')
            ->isEqualTo($this->parse('| yo, #{name} is cool\n'));
        $this->string('<p>yo, jade is cool</p>')
            ->isEqualTo($this->parse('p yo, #{name} is cool'));
        $this->string('yo, jade is cool')
            ->isEqualTo($this->parse('| yo, #{name || "jade"} is cool'));
        $this->string('yo, \'jade\' is cool')
            ->isEqualTo($this->parse('| yo, #{name || "\'jade\'"} is cool'));
        $this->string('foo &lt;script&gt; bar')
            ->isEqualTo($this->parse('| foo #{code} bar'));
        $this->string('foo <script> bar')
            ->isEqualTo($this->parse('| foo !{code} bar'));
        */
    }

    public function testFlexibleIdentation()
    {
        /*
        $this->string('<html><body><h1>Wahoo</h1><p>test</p></body></html>')
            ->isEqualTo($this->parse('html\n  body\n   h1 Wahoo\n   p test'));
        */
    }

    public function testInterpolationValues()
    {
        $this->string('<p>Users: <?php echo Jade\Dumper::_text(15); ?></p>')
            ->isEqualTo($this->parse('p Users: #{15}'));
        //$this->string('<p>Users: </p>')
        //    ->isEqualTo($this->parse('p Users: #{null}'));
        //$this->string('<p>Users: </p>')
        //    ->isEqualTo($this->parse('p Users: #{undefined}'));
        //$this->string('<p>Users: none</p>')
        //    ->isEqualTo($this->parse('p Users: #{undefined || "none"}'));
        $this->string('<p>Users: <?php echo Jade\Dumper::_text(0); ?></p>')
            ->isEqualTo($this->parse('p Users: #{0}'));
        $this->string('<p>Users: <?php echo Jade\Dumper::_text(false); ?></p>')
            ->isEqualTo($this->parse('p Users: #{false}'));
    }

    public function testHtml5Mode()
    {
        //$this->string('<!DOCTYPE html><input type="checkbox" checked>')
        //    ->isEqualTo($this->parse('!!! 5\ninput(type="checkbox", checked)'));
        //$this->string('<!DOCTYPE html><input type="checkbox" checked>')
        //    ->isEqualTo($this->parse('!!! 5\ninput(type="checkbox", checked=true)'));
        //$this->string('<!DOCTYPE html><input type="checkbox">')
        //    ->isEqualTo($this->parse('!!! 5\ninput(type="checkbox", checked= false)'));
    }

    public function testMultiLineAttributes()
    {
        //$this->string('<a foo="bar" bar="baz" checked="checked">foo</a>')
        //    ->isEqualTo($this->parse('a(foo="bar"\n  bar="baz"\n  checked) foo'));
        //$this->string('<a foo="bar" bar="baz" checked="checked">foo</a>')
        //    ->isEqualTo($this->parse('a(foo="bar"\nbar="baz"\nchecked) foo'));
        //$this->string('<a foo="bar" bar="baz" checked="checked">foo</a>')
        //    ->isEqualTo($this->parse('a(foo="bar"\n,bar="baz"\n,checked) foo'));
        //$this->string('<a foo="bar" bar="baz" checked="checked">foo</a>')
        //    ->isEqualTo($this->parse('a(foo="bar",\nbar="baz",\nchecked) foo'));
    }

    public function testAttributes()
    {
        //$this->string('<img src="&lt;script&gt;"/>')
        //    ->isEqualTo($this->parse('img(src="<script>")'), 'Test attr escaping');

        $this->string('<a data-attr="bar"></a>')
            ->isEqualTo($this->parse('a(data-attr="bar")'));
        $this->string('<a data-attr="bar" data-attr-2="baz"></a>')
            ->isEqualTo($this->parse('a(data-attr="bar", data-attr-2="baz")'));

        $this->string('<a title="foo,bar"></a>')
            ->isEqualTo($this->parse('a(title= "foo,bar")'));
        $this->string('<a title="foo,bar" href="#"></a>')
            ->isEqualTo($this->parse('a(title= "foo,bar", href="#")'));

        $this->string('<p class="foo"></p>')
            ->isEqualTo($this->parse("p(class='foo')"), 'Test single quoted attrs');
        //$this->string('<input type="checkbox" checked="checked"/>')
        //    ->isEqualTo($this->parse('input( type="checkbox", checked )'));
        //$this->string('<input type="checkbox" checked="checked"/>')
        //    ->isEqualTo($this->parse('input( type="checkbox", checked = true )'));
        //$this->string('<input type="checkbox"/>')
        //    ->isEqualTo($this->parse('input(type="checkbox", checked= false)'));
        //$this->string('<input type="checkbox"/>')
        //    ->isEqualTo($this->parse('input(type="checkbox", checked= null)'));
        //$this->string('<input type="checkbox"/>')
        //    ->isEqualTo($this->parse('input(type="checkbox", checked= undefined)'));

        $this->string('<img src="/foo.png"/>')
            ->isEqualTo($this->parse('img(src="/foo.png")'));
        $this->string('<img src="/foo.png"/>')
            ->isEqualTo($this->parse('img(src  =  "/foo.png")'), 'Test attr = whitespace');
        $this->string('<img src="/foo.png"/>')
            ->isEqualTo($this->parse('img(src="/foo.png")'), 'Test attr :');
        $this->string('<img src="/foo.png"/>')
            ->isEqualTo($this->parse('img(src  =  "/foo.png")'), 'Test attr : whitespace');

        $this->string('<img src="/foo.png" alt="just some foo"/>')
            ->isEqualTo($this->parse('img(src="/foo.png", alt="just some foo")'));
        $this->string('<img src="/foo.png" alt="just some foo"/>')
            ->isEqualTo($this->parse('img(src = "/foo.png", alt = "just some foo")'));

        $this->string('<p class="foo,bar,baz"></p>')
            ->isEqualTo($this->parse('p(class="foo,bar,baz")'));
        $this->string('<a href="http://google.com" title="Some : weird = title"></a>')
            ->isEqualTo($this->parse('a(href= "http://google.com", title= "Some : weird = title")'));
        $this->string('<label for="name"></label>')
            ->isEqualTo($this->parse('label(for="name")'));
        $this->string('<meta name="viewport" content="width=device-width"/>')
            ->isEqualTo($this->parse("meta(name= 'viewport', content='width=device-width')"), 'Test attrs that contain attr separators');
        $this->string('<div style="color= white"></div>')
            ->isEqualTo($this->parse("div(style='color= white')"));
        $this->string('<div style="color: white"></div>')
            ->isEqualTo($this->parse("div(style='color: white')"));
        //$this->string('<p class="foo"></p>')
        //    ->isEqualTo($this->parse("p('class'='foo')"));
        //$this->string('<p class="foo"></p>')
        //    ->isEqualTo($this->parse("p(\"class\"= 'foo')"), 'Test keys with double quotes');

        $this->string('<p data-lang="en"></p>')
            ->isEqualTo($this->parse('p(data-lang = "en")'));
        //$this->string('<p data-dynamic="true"></p>')
        //    ->isEqualTo($this->parse('p("data-dynamic"= "true")'));
        //$this->string('<p data-dynamic="true" class="name"></p>')
        //    ->isEqualTo($this->parse('p("class"= "name", "data-dynamic"= "true")'));
        //$this->string('<p data-dynamic="true"></p>')
        //    ->isEqualTo($this->parse('p(\'data-dynamic\'= "true")'));
        //$this->string('<p data-dynamic="true" class="name"></p>')
        //    ->isEqualTo($this->parse('p(\'class\'= "name", \'data-dynamic\'= "true")'));
        //$this->string('<p data-dynamic="true" yay="yay" class="name"></p>')
        //    ->isEqualTo($this->parse('p(\'class\'= "name", \'data-dynamic\'= "true", yay)'));

        $this->string('<input checked="checked" type="checkbox"/>')
            ->isEqualTo($this->parse('input(checked, type="checkbox")'));

        $this->string('<a data-foo="{ foo: \'bar\', bar= \'baz\' }"></a>')
            ->isEqualTo($this->parse('a(data-foo  = "{ foo: \'bar\', bar= \'baz\' }")'));

        $this->string('<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>')
            ->isEqualTo($this->parse('meta(http-equiv="X-UA-Compatible", content="IE=edge,chrome=1")'));

        $this->string('<div style="background: url(/images/test.png)">Foo</div>')
            ->isEqualTo($this->parse("div(style= 'background: url(/images/test.png)') Foo"));
        $this->string('<div style="background = url(/images/test.png)">Foo</div>')
            ->isEqualTo($this->parse("div(style= 'background = url(/images/test.png)') Foo"));
        //$this->string('<div style="foo">Foo</div>')
        //    ->isEqualTo($this->parse("div(style= ['foo', 'bar'][0]) Foo"));
        //$this->string('<div style="bar">Foo</div>')
        //    ->isEqualTo($this->parse("div(style= { foo: 'bar', baz: 'raz' }['foo']) Foo"));
        //$this->string('<a href="def">Foo</a>')
        //    ->isEqualTo($this->parse("a(href='abcdefg'.substr(3,3)) Foo"));
        //$this->string('<a href="def">Foo</a>')
        //    ->isEqualTo($this->parse("a(href={test: 'abcdefg'}.test.substr(3,3)) Foo"));
        //$this->string('<a href="def">Foo</a>')
        //    ->isEqualTo($this->parse("a(href={test: 'abcdefg'}.test.substr(3,[0,3][1])) Foo"));

        $this->string('<rss xmlns:atom="atom"></rss>')
            ->isEqualTo($this->parse("rss(xmlns:atom=\"atom\")"));
        //$this->string('<rss xmlns:atom="atom"></rss>')
        //    ->isEqualTo($this->parse("rss('xmlns:atom'=\"atom\")"));
        //$this->string('<rss xmlns:atom="atom"></rss>')
        //    ->isEqualTo($this->parse("rss(\"xmlns:atom\"='atom')"));
        //$this->string('<rss xmlns:atom="atom" foo="bar"></rss>')
        //    ->isEqualTo($this->parse("rss('xmlns:atom'=\"atom\", 'foo'= 'bar')"));
        //$this->string('<a data-obj="{ foo: \'bar\' }"></a>')
        //    ->isEqualTo($this->parse("a(data-obj= \"{ foo: 'bar' }\")"));

        //$this->string('<meta content="what\'s up? \'weee\'"/>')
        //    ->isEqualTo($this->parse('meta(content="what\'s up? \'weee\'")'));
    }


    public function testPhpVar()
    {
        $str = 'foo bar baz';
        $this->string('<?php echo Jade\Dumper::_text($var); ?>')
            ->isEqualTo($this->parse('#{$var}'));
    }
}
