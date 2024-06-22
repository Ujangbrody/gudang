<?php

namespace Dompdf\FrameReflower;

use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\FrameDecorator\Text as TextFrameDecorator;
use Dompdf\FontMetrics;
use Dompdf\Helpers;


class Text extends AbstractFrameReflower
{
    
    const SOFT_HYPHEN = "\xC2\xAD";

    
    protected $_block_parent; 

    
    protected $_frame;

    
    
    public static $_whitespace_pattern = '/([^\S\xA0]+)/u';
    
    
    public static $_wordbreak_pattern = '/([^\S\xA0]+|\-+|\xAD+)/u';

    
    private $fontMetrics;

    
    public function __construct(TextFrameDecorator $frame, FontMetrics $fontMetrics)
    {
        parent::__construct($frame);
        $this->setFontMetrics($fontMetrics);
    }

    
    protected function _collapse_white_space($text)
    {
        return preg_replace(self::$_whitespace_pattern, " ", $text);
    }

    
    protected function _line_break($text)
    {
        $style = $this->_frame->get_style();
        $size = $style->font_size;
        $font = $style->font_family;
        $current_line = $this->_block_parent->get_current_line_box();

        
        $line_width = $this->_frame->get_containing_block("w");
        $current_line_width = $current_line->left + $current_line->w + $current_line->right;

        $available_width = $line_width - $current_line_width;

        
        $word_spacing = (float)$style->length_in_pt($style->word_spacing);
        $char_spacing = (float)$style->length_in_pt($style->letter_spacing);

        
        $visible_text = preg_replace('/\xAD/u', '', $text);
        $text_width = $this->getFontMetrics()->getTextWidth($visible_text, $font, $size, $word_spacing, $char_spacing);
        $mbp_width =
            (float)$style->length_in_pt([$style->margin_left,
                $style->border_left_width,
                $style->padding_left,
                $style->padding_right,
                $style->border_right_width,
                $style->margin_right], $line_width);

        $frame_width = $text_width + $mbp_width;












        if ($frame_width <= $available_width) {
            return false;
        }

        
        $words = preg_split(self::$_wordbreak_pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $wc = count($words);

        
        $width = 0;
        $str = "";
        reset($words);

        $shy_width = $this->getFontMetrics()->getTextWidth(self::SOFT_HYPHEN, $font, $size);

        
        for ($i = 0; $i < $wc; $i += 2) {
            $word = $words[$i] . (isset($words[$i + 1]) ? $words[$i + 1] : "");
            $word_width = $this->getFontMetrics()->getTextWidth($word, $font, $size, $word_spacing, $char_spacing);
            if ($width + $word_width + $mbp_width > $available_width) {
                
                
                if (isset($words[$i - 1]) && self::SOFT_HYPHEN === $words[$i - 1]) {
                    $width += $shy_width;
                }
                break;
            }

            
            
            
            if (isset($words[$i + 1]) && self::SOFT_HYPHEN === $words[$i + 1]) {
                $width += $word_width - $shy_width;
            } else {
                $width += $word_width;
            }
            $str .= $word;
        }

        $break_word = ($style->word_wrap === "break-word");

        
        if ($current_line_width == 0 && $width == 0) {
            $s = "";
            $last_width = 0;

            if ($break_word) {
                for ($j = 0; $j < strlen($word); $j++) {
                    $s .= $word[$j];
                    $_width = $this->getFontMetrics()->getTextWidth($s, $font, $size, $word_spacing, $char_spacing);
                    if ($_width > $available_width) {
                        break;
                    }

                    $last_width = $_width;
                }
            }

            if ($break_word && $last_width > 0) {
                
                $str .= substr($s, 0, -1);
            } else {
                
                $str .= $word;
            }
        }

        $offset = mb_strlen($str);

        
        
        
        

        return $offset;
    }

    

    
    protected function _newline_break($text)
    {
        if (($i = mb_strpos($text, "\n")) === false) {
            return false;
        }

        return $i + 1;
    }

    protected function _layout_line(): bool
    {
        $frame = $this->_frame;
        $style = $frame->get_style();
        $text = $frame->get_text();
        $size = $style->font_size;
        $font = $style->font_family;

        
        $style->height = $this->getFontMetrics()->getFontHeight($font, $size);

        $split = false;
        $add_line = false;

        
        
        switch (strtolower($style->text_transform)) {
            default:
                break;
            case "capitalize":
                $text = Helpers::mb_ucwords($text);
                break;
            case "uppercase":
                $text = mb_convert_case($text, MB_CASE_UPPER);
                break;
            case "lowercase":
                $text = mb_convert_case($text, MB_CASE_LOWER);
                break;
        }

        
        
        switch ($style->white_space) {
            default:
            case "normal":
                $frame->set_text($text = $this->_collapse_white_space($text));
                if ($text === "") {
                    break;
                }

                $split = $this->_line_break($text);
                break;

            case "pre":
                $split = $this->_newline_break($text);
                $add_line = $split !== false;
                break;

            case "nowrap":
                $frame->set_text($text = $this->_collapse_white_space($text));
                break;
            
            case "pre-line":
                
                $frame->set_text($text = preg_replace("/[ \t]+/u", " ", $text));

                if ($text === "") {
                    break;
                }
            case "pre-wrap":
                $split = $this->_newline_break($text);

                if (($tmp = $this->_line_break($text)) !== false) {
                    if ($split === false || $tmp < $split) {
                        $split = $tmp;
                    } else {
                        $add_line = true;
                    }
                } else if ($split !== false) {
                    $add_line = true;
                }

                break;
        }

        
        if ($text === "") {
            $split = 0;
        }

        if ($split !== false) {
            
            if ($split == 0 && !$frame->is_pre() && empty(trim($text))) {
                $frame->set_text("");
            } else if ($split === 0) {
                
                if (($sibling = $frame->get_prev_sibling()) !== null) {
                    if ($sibling instanceof \Dompdf\FrameDecorator\Text && !$sibling->is_pre()) {
                        $st = $sibling->get_text();
                        if (preg_match(self::$_whitespace_pattern, mb_substr($st, -1))) {
                            $sibling->set_text(mb_substr($st, 0, -1));
                            $sibling->recalculate_width();
                            $this->_block_parent->get_current_line_box()->recalculate_width();
                        }
                    }
                }

                
                

                $this->_block_parent->maximize_line_height($style->height, $frame);
                $this->_block_parent->add_line();
                $frame->position();

                
                $add_line = $this->_layout_line();
            } else if ($split < mb_strlen($frame->get_text())) {
                
                $frame->split_text($split);

                $t = $frame->get_text();

                
                $shyPosition = mb_strpos($t, self::SOFT_HYPHEN);
                if (false !== $shyPosition && $shyPosition < mb_strlen($t) - 1) {
                    $t = str_replace(self::SOFT_HYPHEN, '', mb_substr($t, 0, -1)) . mb_substr($t, -1);
                    $frame->set_text($t);
                }

                
                
                
                
                
            }

            
            if (!$frame->is_pre() && $add_line) {
                $t = $frame->get_text();
                if (preg_match(self::$_whitespace_pattern, mb_substr($t, -1))) {
                    $frame->set_text(mb_substr($t, 0, -1));
                }
            }
        } else {
            
            
            
            $t = $frame->get_text();
            $parent = $frame->get_parent();
            $is_inline_frame = ($parent instanceof \Dompdf\FrameDecorator\Inline);

            if ((!$is_inline_frame && !$frame->get_next_sibling()) 
            ) { 
                $t = rtrim($t);
            }

            if ((!$is_inline_frame && !$frame->get_prev_sibling()) 
            ) { 
                $t = ltrim($t);
            }

            
            $t = str_replace(self::SOFT_HYPHEN, '', $t);

            $frame->set_text($t);
        }

        
        $frame->recalculate_width();

        return $add_line;
    }

    
    function reflow(BlockFrameDecorator $block = null)
    {
        $frame = $this->_frame;
        $page = $frame->get_root();
        $page->check_forced_page_break($this->_frame);

        if ($page->is_full()) {
            return;
        }

        $this->_block_parent = 
        $frame->find_block_parent();

        
        






        $frame->position();

        $add_line = $this->_layout_line();

        if ($block) {
            $block->add_frame_to_line($frame);

            if ($add_line === true) {
                $block->add_line();
            }
        }
    }

    

    
    
    function get_min_max_width()
    {
        
        $frame = $this->_frame;
        $style = $frame->get_style();
        $this->_block_parent = $frame->find_block_parent();
        $line_width = $frame->get_containing_block("w");

        $str = $text = $frame->get_text();
        $size = $style->font_size;
        $font = $style->font_family;

        $word_spacing = (float)$style->length_in_pt($style->word_spacing);
        $char_spacing = (float)$style->length_in_pt($style->letter_spacing);

        
        switch ($style->white_space) {
            default:
            
            case "normal":
                $str = preg_replace(self::$_whitespace_pattern, " ", $str);
            case "pre-wrap":
            case "pre-line":

                

                
                $words = array_flip(preg_split(self::$_wordbreak_pattern, $str, -1, PREG_SPLIT_DELIM_CAPTURE));
                $root = $this;
                array_walk($words, function(&$chunked_text_width, $chunked_text) use ($font, $size, $word_spacing, $char_spacing, $root) {
                    $chunked_text_width = $root->getFontMetrics()->getTextWidth($chunked_text, $font, $size, $word_spacing, $char_spacing);
                });

                arsort($words);
                $min = reset($words);
                break;

            case "pre":
                $lines = array_flip(preg_split("/\R/u", $str));
                $root = $this;
                array_walk($lines, function(&$chunked_text_width, $chunked_text) use ($font, $size, $word_spacing, $char_spacing, $root) {
                    $chunked_text_width = $root->getFontMetrics()->getTextWidth($chunked_text, $font, $size, $word_spacing, $char_spacing);
                });

                arsort($lines);
                $min = reset($lines);
                break;

            case "nowrap":
                $min = $this->getFontMetrics()->getTextWidth($this->_collapse_white_space($str), $font, $size, $word_spacing, $char_spacing);
                break;
        }

        
        switch ($style->white_space) {
            default:
            case "normal":
            case "nowrap":
                $str = preg_replace(self::$_whitespace_pattern, " ", $text);
                break;

            case "pre-line":
                $str = preg_replace("/[ \t]+/u", " ", $text);
                break;

            case "pre-wrap":
                
                $lines = array_flip(preg_split("/\R/u", $text));
                $root = $this;
                array_walk($lines, function(&$chunked_text_width, $chunked_text) use ($font, $size, $word_spacing, $char_spacing, $root) {
                    $chunked_text_width = $root->getFontMetrics()->getTextWidth($chunked_text, $font, $size, $word_spacing, $char_spacing);
                });
                arsort($lines);
                reset($lines);
                $str = key($lines);
                break;
        }
        $max = $this->getFontMetrics()->getTextWidth($str, $font, $size, $word_spacing, $char_spacing);

        $delta = (float)$style->length_in_pt([$style->margin_left,
            $style->border_left_width,
            $style->padding_left,
            $style->padding_right,
            $style->border_right_width,
            $style->margin_right], $line_width);
        $min += $delta;
        $min_word = $min;
        $max += $delta;

        if ($style->word_wrap === 'break-word') {
            
            
            $char = mb_substr($str, 0, 1);
            $min_char = $this->getFontMetrics()->getTextWidth($char, $font, $size, $word_spacing, $char_spacing);
            $min = $delta + $min_char;
        }

        return $this->_min_max_cache = [$min, $max, $min_word, "min" => $min, "max" => $max, 'min_word' => $min_word];
    }

    
    public function setFontMetrics(FontMetrics $fontMetrics)
    {
        $this->fontMetrics = $fontMetrics;
        return $this;
    }

    
    public function getFontMetrics()
    {
        return $this->fontMetrics;
    }

    
    public function calculate_auto_width()
    {
        return $this->_frame->recalculate_width();
    }
}
