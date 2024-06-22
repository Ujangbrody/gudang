<?php

namespace Dompdf\FrameReflower;

use Dompdf\Adapter\CPDF;
use Dompdf\Css\Style;
use Dompdf\Dompdf;
use Dompdf\Helpers;
use Dompdf\Frame;
use Dompdf\FrameDecorator\Block;
use Dompdf\Frame\Factory;


abstract class AbstractFrameReflower
{

    
    protected $_frame;

    
    protected $_min_max_cache;

    
    function __construct(Frame $frame)
    {
        $this->_frame = $frame;
        $this->_min_max_cache = null;
    }

    function dispose()
    {
    }

    
    function get_dompdf()
    {
        return $this->_frame->get_dompdf();
    }

    
    protected function _collapse_margins()
    {
        $frame = $this->_frame;
        $cb = $frame->get_containing_block();
        $style = $frame->get_style();

        
        if (!$frame->is_in_flow() || $frame->is_inline_block() || $frame->get_root() == $frame || $frame->get_parent() == $frame->get_root()) {
            return;
        }

        $t = $style->length_in_pt($style->margin_top, $cb["h"]);
        $b = $style->length_in_pt($style->margin_bottom, $cb["h"]);

        
        if ($t === "auto") {
            $style->margin_top = "0pt";
            $t = 0;
        }

        if ($b === "auto") {
            $style->margin_bottom = "0pt";
            $b = 0;
        }

        
        $n = $frame->get_next_sibling();
        if ( $n && !$n->is_block() & !$n->is_table() ) {
            while ($n = $n->get_next_sibling()) {
                if ($n->is_block() || $n->is_table()) {
                    break;
                }

                if (!$n->get_first_child()) {
                    $n = null;
                    break;
                }
            }
        }

        if ($n) {
            $n_style = $n->get_style();
            $n_t = (float)$n_style->length_in_pt($n_style->margin_top, $cb["h"]);

            $b = $this->_get_collapsed_margin_length($b, $n_t);
            $style->margin_bottom = $b . "pt";
            $n_style->margin_top = "0pt";
        }

        
        if ($style->border_top_width == 0 && $style->length_in_pt($style->padding_top) == 0) {
            $f = $this->_frame->get_first_child();
            if ( $f && !$f->is_block() && !$f->is_table() ) {
                while ( $f = $f->get_next_sibling() ) {
                    if ( $f->is_block() || $f->is_table() ) {
                        break;
                    }

                    if ( !$f->get_first_child() ) {
                        $f = null;
                        break;
                    }
                }
            }

            
            if ($f) {
                $f_style = $f->get_style();
                $f_t = (float)$f_style->length_in_pt($f_style->margin_top, $cb["h"]);

                $t = $this->_get_collapsed_margin_length($t, $f_t);
                $style->margin_top = $t."pt";
                $f_style->margin_top = "0pt";
            }
        }

        
        if ($style->border_bottom_width == 0 && $style->length_in_pt($style->padding_bottom) == 0) {
            $l = $this->_frame->get_last_child();
            if ( $l && !$l->is_block() && !$l->is_table() ) {
                while ( $l = $l->get_prev_sibling() ) {
                    if ( $l->is_block() || $l->is_table() ) {
                        break;
                    }

                    if ( !$l->get_last_child() ) {
                        $l = null;
                        break;
                    }
                }
            }

            
            if ($l) {
                $l_style = $l->get_style();
                $l_b = (float)$l_style->length_in_pt($l_style->margin_bottom, $cb["h"]);

                $b = $this->_get_collapsed_margin_length($b, $l_b);
                $style->margin_bottom = $b."pt";
                $l_style->margin_bottom = "0pt";
            }
        }
    }

    
    private function _get_collapsed_margin_length($length1, $length2)
    {
        if ($length1 < 0 && $length2 < 0) {
            return min($length1, $length2); 
        }
        
        if ($length1 < 0 || $length2 < 0) {
            return $length1 + $length2; 
        }
        
        return max($length1, $length2);
    }

    
    abstract function reflow(Block $block = null);

    
    function get_min_max_width()
    {
        if (!is_null($this->_min_max_cache)) {
            return $this->_min_max_cache;
        }

        $style = $this->_frame->get_style();

        
        $dims = [$style->padding_left,
            $style->padding_right,
            $style->border_left_width,
            $style->border_right_width,
            $style->margin_left,
            $style->margin_right];

        $cb_w = $this->_frame->get_containing_block("w");
        $delta = (float)$style->length_in_pt($dims, $cb_w);

        
        if (!$this->_frame->get_first_child()) {
            return $this->_min_max_cache = [
                $delta, $delta,
                "min" => $delta,
                "max" => $delta,
            ];
        }

        $low = [];
        $high = [];

        for ($iter = $this->_frame->get_children()->getIterator(); $iter->valid(); $iter->next()) {
            $inline_min = 0;
            $inline_max = 0;

            
            while ($iter->valid() && in_array($iter->current()->get_style()->display, Style::$INLINE_TYPES)) {
                $child = $iter->current();

                $minmax = $child->get_min_max_width();

                if (in_array($iter->current()->get_style()->white_space, ["pre", "nowrap"])) {
                    $inline_min += $minmax["min"];
                } else {
                    $low[] = $minmax["min"];
                }

                $inline_max += $minmax["max"];
                $iter->next();
            }

            if ($inline_max > 0) {
                $high[] = $inline_max;
            }
            if ($inline_min > 0) {
                $low[] = $inline_min;
            }

            if ($iter->valid()) {
                list($low[], $high[]) = $iter->current()->get_min_max_width();
                continue;
            }
        }
        $min = count($low) ? max($low) : 0;
        $max = count($high) ? max($high) : 0;

        
        
        $width = $style->width;
        if ($width !== "auto" && !Helpers::is_percent($width)) {
            $width = (float)$style->length_in_pt($width, $cb_w);
            if ($min < $width) {
                $min = $width;
            }
            if ($max < $width) {
                $max = $width;
            }
        }

        $min += $delta;
        $max += $delta;
        return $this->_min_max_cache = [$min, $max, "min" => $min, "max" => $max];
    }

    
    protected function _parse_string($string, $single_trim = false)
    {
        if ($single_trim) {
            $string = preg_replace('/^[\"\']/', "", $string);
            $string = preg_replace('/[\"\']$/', "", $string);
        } else {
            $string = trim($string, "'\"");
        }

        $string = str_replace(["\\\n", '\\"', "\\'"],
            ["", '"', "'"], $string);

        
        $string = preg_replace_callback("/\\\\([0-9a-fA-F]{0,6})/",
            function ($matches) { return \Dompdf\Helpers::unichr(hexdec($matches[1])); },
            $string);
        return $string;
    }

    
    protected function _parse_quotes()
    {
        
        $re = '/(\'[^\']*\')|(\"[^\"]*\")/';

        $quotes = $this->_frame->get_style()->quotes;

        
        if (!preg_match_all($re, "$quotes", $matches, PREG_SET_ORDER)) {
            return null;
        }

        $quotes_array = [];
        foreach ($matches as $_quote) {
            $quotes_array[] = $this->_parse_string($_quote[0], true);
        }

        if (empty($quotes_array)) {
            $quotes_array = ['"', '"'];
        }

        return array_chunk($quotes_array, 2);
    }

    
    protected function _parse_content()
    {
        
        $re = "/\n" .
            "\s(counters?\\([^)]*\\))|\n" .
            "\A(counters?\\([^)]*\\))|\n" .
            "\s([\"']) ( (?:[^\"']|\\\\[\"'])+ )(?<!\\\\)\\3|\n" .
            "\A([\"']) ( (?:[^\"']|\\\\[\"'])+ )(?<!\\\\)\\5|\n" .
            "\s([^\s\"']+)|\n" .
            "\A([^\s\"']+)\n" .
            "/xi";

        $content = $this->_frame->get_style()->content;

        $quotes = $this->_parse_quotes();

        
        if (!preg_match_all($re, $content, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $text = "";

        foreach ($matches as $match) {
            if (isset($match[2]) && $match[2] !== "") {
                $match[1] = $match[2];
            }

            if (isset($match[6]) && $match[6] !== "") {
                $match[4] = $match[6];
            }

            if (isset($match[8]) && $match[8] !== "") {
                $match[7] = $match[8];
            }

            if (isset($match[1]) && $match[1] !== "") {
                
                $match[1] = mb_strtolower(trim($match[1]));

                
                

                $i = mb_strpos($match[1], ")");
                if ($i === false) {
                    continue;
                }

                preg_match('/(counters?)(^\()*?\(\s*([^\s,]+)\s*(,\s*["\']?([^"\'\)]*)["\']?\s*(,\s*([^\s)]+)\s*)?)?\)/i', $match[1], $args);
                $counter_id = $args[3];
                if (strtolower($args[1]) == 'counter') {
                    
                    if (isset($args[5])) {
                        $type = trim($args[5]);
                    } else {
                        $type = null;
                    }
                    $p = $this->_frame->lookup_counter_frame($counter_id);

                    $text .= $p->counter_value($counter_id, $type);

                } else if (strtolower($args[1]) == 'counters') {
                    
                    if (isset($args[5])) {
                        $string = $this->_parse_string($args[5]);
                    } else {
                        $string = "";
                    }

                    if (isset($args[7])) {
                        $type = trim($args[7]);
                    } else {
                        $type = null;
                    }

                    $p = $this->_frame->lookup_counter_frame($counter_id);
                    $tmp = [];
                    while ($p) {
                        
                        if (array_key_exists($counter_id, $p->_counters)) {
                            array_unshift($tmp, $p->counter_value($counter_id, $type));
                        }
                        $p = $p->lookup_counter_frame($counter_id);
                    }
                    $text .= implode($string, $tmp);
                } else {
                    
                    continue;
                }

            } else if (isset($match[4]) && $match[4] !== "") {
                
                $text .= $this->_parse_string($match[4]);
            } else if (isset($match[7]) && $match[7] !== "") {
                

                if ($match[7] === "open-quote") {
                    
                    $text .= $quotes[0][0];
                } else if ($match[7] === "close-quote") {
                    
                    $text .= $quotes[0][1];
                } else if ($match[7] === "no-open-quote") {
                    
                } else if ($match[7] === "no-close-quote") {
                    
                } else if (mb_strpos($match[7], "attr(") === 0) {
                    $i = mb_strpos($match[7], ")");
                    if ($i === false) {
                        continue;
                    }

                    $attr = mb_substr($match[7], 5, $i - 5);
                    if ($attr == "") {
                        continue;
                    }

                    $text .= $this->_frame->get_parent()->get_node()->getAttribute($attr);
                } else {
                    continue;
                }
            }
        }

        return $text;
    }

    
    protected function _set_content()
    {
        $frame = $this->_frame;
        $style = $frame->get_style();

        
        if ($style->counter_reset && ($reset = $style->counter_reset) !== "none") {
            $vars = preg_split('/\s+/', trim($reset), 2);
            $frame->reset_counter($vars[0], (isset($frame->_counters['__' . $vars[0]]) ? $frame->_counters['__' . $vars[0]] : (isset($vars[1]) ? $vars[1] : 0)));
        }

        if ($style->counter_increment && ($increment = $style->counter_increment) !== "none") {
            $frame->increment_counters($increment);
        }

        if ($style->content && $frame->get_node()->nodeName === "dompdf_generated") {
            $content = $this->_parse_content();
            
            
            
            if ($frame->get_dompdf()->getOptions()->getIsFontSubsettingEnabled() && $frame->get_dompdf()->get_canvas() instanceof CPDF) {
                $frame->get_dompdf()->get_canvas()->register_string_subset($style->font_family, $content);
            }

            $node = $frame->get_node()->ownerDocument->createTextNode($content);

            $new_style = $style->get_stylesheet()->create_style();
            $new_style->inherit($style);

            $new_frame = new Frame($node);
            $new_frame->set_style($new_style);

            Factory::decorate_frame($new_frame, $frame->get_dompdf(), $frame->get_root());
            $frame->append_child($new_frame);
        }
    }

    
    public function calculate_auto_width()
    {
        return $this->_frame->get_margin_width();
    }
}
