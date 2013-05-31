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
            ->isEqualTo($this->parse('!!! 5'));
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

    public function testTags()
    {
        $str = implode("\n", array('p', 'div', 'img'));
        $html = implode("\n", array('<p></p>', '<div></div>', '<img />'));

        $this->string($html)
            ->isEqualTo($this->parse($str));
        $this->string('<div id="item" class="something"></div>')
            ->isEqualTo($this->parse('div#item.something'));
    }
}
