<?php


namespace Svg\Tag;

use Sabberworm\CSS;

class StyleTag extends AbstractTag
{
    protected $text = "";

    public function end()
    {
        $parser = new CSS\Parser($this->text);
        $this->document->appendStyleSheet($parser->parse());
    }

    public function appendText($text)
    {
        $this->text .= $text;
    }
} 