<?php

namespace Dompdf\FrameReflower;

use Dompdf\Frame;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\FrameDecorator\TableCell as TableCellFrameDecorator;
use Dompdf\FrameDecorator\Text as TextFrameDecorator;
use Dompdf\Exception;
use Dompdf\Css\Style;


class Block extends AbstractFrameReflower
{
    
    const MIN_JUSTIFY_WIDTH = 0.80;

    
    protected $_frame;

    function __construct(BlockFrameDecorator $frame)
    {
        parent::__construct($frame);
    }

    
    protected function _calculate_width($width)
    {
        $frame = $this->_frame;
        $style = $frame->get_style();
        $w = $frame->get_containing_block("w");

        if ($style->position === "fixed") {
            $w = $frame->get_parent()->get_containing_block("w");
        }

        $rm = $style->length_in_pt($style->margin_right, $w);
        $lm = $style->length_in_pt($style->margin_left, $w);

        $left = $style->length_in_pt($style->left, $w);
        $right = $style->length_in_pt($style->right, $w);

        
        $dims = [$style->border_left_width,
            $style->border_right_width,
            $style->padding_left,
            $style->padding_right,
            $width !== "auto" ? $width : 0,
            $rm !== "auto" ? $rm : 0,
            $lm !== "auto" ? $lm : 0];

        
        if ($frame->is_absolute()) {
            $absolute = true;
            $dims[] = $left !== "auto" ? $left : 0;
            $dims[] = $right !== "auto" ? $right : 0;
        } else {
            $absolute = false;
        }

        $sum = (float)$style->length_in_pt($dims, $w);

        
        $diff = $w - $sum;

        if ($diff > 0) {
            if ($absolute) {
                
                

                if ($width === "auto" && $left === "auto" && $right === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }

                    
                    
                    
                    $left = 0;
                    $right = 0;
                    $width = $diff;
                } else if ($width === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }
                    if ($left === "auto") {
                        $left = 0;
                    }
                    if ($right === "auto") {
                        $right = 0;
                    }

                    $width = $diff;
                } else if ($left === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }
                    if ($right === "auto") {
                        $right = 0;
                    }

                    $left = $diff;
                } else if ($right === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }

                    $right = $diff;
                }

            } else {
                
                if ($width === "auto") {
                    $width = $diff;
                } else if ($lm === "auto" && $rm === "auto") {
                    $lm = $rm = round($diff / 2);
                } else if ($lm === "auto") {
                    $lm = $diff;
                } else if ($rm === "auto") {
                    $rm = $diff;
                }
            }
        } else if ($diff < 0) {
            
            $rm = $diff;
        }

        return [
            "width" => $width,
            "margin_left" => $lm,
            "margin_right" => $rm,
            "left" => $left,
            "right" => $right,
        ];
    }

    
    protected function _calculate_restricted_width()
    {
        $frame = $this->_frame;
        $style = $frame->get_style();
        $cb = $frame->get_containing_block();

        if ($style->position === "fixed") {
            $cb = $frame->get_root()->get_containing_block();
        }

        
        

        if (!isset($cb["w"])) {
            throw new Exception("Box property calculation requires containing block width");
        }

        
        if ($style->width === "100%") {
            $width = "auto";
        } else {
            $width = $style->length_in_pt($style->width, $cb["w"]);
        }

        $calculate_width = $this->_calculate_width($width);
        $margin_left = $calculate_width['margin_left'];
        $margin_right = $calculate_width['margin_right'];
        $width =  $calculate_width['width'];
        $left =  $calculate_width['left'];
        $right =  $calculate_width['right'];

        
        $min_width = $style->length_in_pt($style->min_width, $cb["w"]);
        $max_width = $style->length_in_pt($style->max_width, $cb["w"]);

        if ($max_width !== "none" && $min_width > $max_width) {
            list($max_width, $min_width) = [$min_width, $max_width];
        }

        if ($max_width !== "none" && $width > $max_width) {
            extract($this->_calculate_width($max_width));
        }

        if ($width < $min_width) {
            $calculate_width = $this->_calculate_width($min_width);
            $margin_left = $calculate_width['margin_left'];
            $margin_right = $calculate_width['margin_right'];
            $width =  $calculate_width['width'];
            $left =  $calculate_width['left'];
            $right =  $calculate_width['right'];
        }

        return [$width, $margin_left, $margin_right, $left, $right];
    }

    
    protected function _calculate_content_height()
    {
        $height = 0;
        $lines = $this->_frame->get_line_boxes();
        if (count($lines) > 0) {
            $last_line = end($lines);
            $content_box = $this->_frame->get_content_box();
            $height = $last_line->y + $last_line->h - $content_box["y"];
        }
        return $height;
    }

    
    protected function _calculate_restricted_height()
    {
        $frame = $this->_frame;
        $style = $frame->get_style();
        $content_height = $this->_calculate_content_height();
        $cb = $frame->get_containing_block();

        $height = $style->length_in_pt($style->height, $cb["h"]);

        $top = $style->length_in_pt($style->top, $cb["h"]);
        $bottom = $style->length_in_pt($style->bottom, $cb["h"]);

        $margin_top = $style->length_in_pt($style->margin_top, $cb["h"]);
        $margin_bottom = $style->length_in_pt($style->margin_bottom, $cb["h"]);

        if ($frame->is_absolute()) {

            

            $dims = [$top !== "auto" ? $top : 0,
                $style->margin_top !== "auto" ? $style->margin_top : 0,
                $style->padding_top,
                $style->border_top_width,
                $height !== "auto" ? $height : 0,
                $style->border_bottom_width,
                $style->padding_bottom,
                $style->margin_bottom !== "auto" ? $style->margin_bottom : 0,
                $bottom !== "auto" ? $bottom : 0];

            $sum = (float)$style->length_in_pt($dims, $cb["h"]);

            $diff = $cb["h"] - $sum;

            if ($diff > 0) {
                if ($height === "auto" && $top === "auto" && $bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $diff;
                } else if ($height === "auto" && $top === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $content_height;
                    $top = $diff - $content_height;
                } else if ($height === "auto" && $bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $content_height;
                    $bottom = $diff - $content_height;
                } else if ($top === "auto" && $bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $bottom = $diff;
                } else if ($top === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $top = $diff;
                } else if ($height === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $diff;
                } else if ($bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $bottom = $diff;
                } else {
                    if ($style->overflow === "visible") {
                        
                        if ($margin_top === "auto") {
                            $margin_top = 0;
                        }
                        if ($margin_bottom === "auto") {
                            $margin_bottom = 0;
                        }
                        if ($top === "auto") {
                            $top = 0;
                        }
                        if ($bottom === "auto") {
                            $bottom = 0;
                        }
                        if ($height === "auto") {
                            $height = $content_height;
                        }
                    }

                    
                }
            }

        } else {
            
            if ($height === "auto" && $content_height > 0 ) {
                $height = $content_height;
            }

            
            

            
            if (!($style->overflow === "visible" || ($style->overflow === "hidden" && $height === "auto"))) {
                $min_height = $style->min_height;
                $max_height = $style->max_height;

                if (isset($cb["h"])) {
                    $min_height = $style->length_in_pt($min_height, $cb["h"]);
                    $max_height = $style->length_in_pt($max_height, $cb["h"]);
                } else if (isset($cb["w"])) {
                    if (mb_strpos($min_height, "%") !== false) {
                        $min_height = 0;
                    } else {
                        $min_height = $style->length_in_pt($min_height, $cb["w"]);
                    }

                    if (mb_strpos($max_height, "%") !== false) {
                        $max_height = "none";
                    } else {
                        $max_height = $style->length_in_pt($max_height, $cb["w"]);
                    }
                }

                if ($max_height !== "none" && $max_height !== "auto" && (float)$min_height > (float)$max_height) {
                    
                    list($max_height, $min_height) = [$min_height, $max_height];
                }

                if ($max_height !== "none" && $max_height !== "auto" && $height > (float)$max_height) {
                    $height = $max_height;
                }

                if ($height < (float)$min_height) {
                    $height = $min_height;
                }
            }
        }

        return [$height, $margin_top, $margin_bottom, $top, $bottom];
    }

    
    protected function _text_align()
    {
        $style = $this->_frame->get_style();
        $w = $this->_frame->get_containing_block("w");
        $width = (float)$style->length_in_pt($style->width, $w);

        switch ($style->text_align) {
            default:
            case "left":
                foreach ($this->_frame->get_line_boxes() as $line) {
                    if (!$line->left) {
                        continue;
                    }

                    foreach ($line->get_frames() as $frame) {
                        if ($frame instanceof BlockFrameDecorator) {
                            continue;
                        }
                        $frame->set_position($frame->get_position("x") + $line->left);
                    }
                }
                return;

            case "right":
                foreach ($this->_frame->get_line_boxes() as $line) {
                    
                    $dx = $width - $line->w - $line->right;

                    foreach ($line->get_frames() as $frame) {
                        
                        if ($frame instanceof BlockFrameDecorator) {
                            continue;
                        }

                        $frame->set_position($frame->get_position("x") + $dx);
                    }
                }
                break;

            case "justify":
                
                $lines = $this->_frame->get_line_boxes(); 
                $last_line = array_pop($lines);

                foreach ($lines as $i => $line) {
                    if ($line->br) {
                        unset($lines[$i]);
                    }
                }

                
                $space_width = $this->get_dompdf()->getFontMetrics()->getTextWidth(" ", $style->font_family, $style->font_size);

                foreach ($lines as $line) {
                    if ($line->left) {
                        foreach ($line->get_frames() as $frame) {
                            if (!$frame instanceof TextFrameDecorator) {
                                continue;
                            }

                            $frame->set_position($frame->get_position("x") + $line->left);
                        }
                    }

                    
                    if ($line->wc > 1) {
                        $spacing = ($width - ($line->left + $line->w + $line->right)) / ($line->wc - 1);
                    } else {
                        $spacing = 0;
                    }

                    $dx = 0;
                    foreach ($line->get_frames() as $frame) {
                        if (!$frame instanceof TextFrameDecorator) {
                            continue;
                        }

                        $text = $frame->get_text();
                        $spaces = mb_substr_count($text, " ");

                        $char_spacing = (float)$style->length_in_pt($style->letter_spacing);
                        $_spacing = $spacing + $char_spacing;

                        $frame->set_position($frame->get_position("x") + $dx);
                        $frame->set_text_spacing($_spacing);

                        $dx += $spaces * $_spacing;
                    }

                    
                    $line->w = $width;
                }

                
                if ($last_line->left) {
                    foreach ($last_line->get_frames() as $frame) {
                        if ($frame instanceof BlockFrameDecorator) {
                            continue;
                        }
                        $frame->set_position($frame->get_position("x") + $last_line->left);
                    }
                }
                break;

            case "center":
            case "centre":
                foreach ($this->_frame->get_line_boxes() as $line) {
                    
                    $dx = ($width + $line->left - $line->w - $line->right) / 2;

                    foreach ($line->get_frames() as $frame) {
                        
                        if ($frame instanceof BlockFrameDecorator) {
                            continue;
                        }

                        $frame->set_position($frame->get_position("x") + $dx);
                    }
                }
                break;
        }
    }

    
    function vertical_align()
    {
        $canvas = null;

        foreach ($this->_frame->get_line_boxes() as $line) {

            $height = $line->h;

            foreach ($line->get_frames() as $frame) {
                $style = $frame->get_style();
                $isInlineBlock = (
                    '-dompdf-image' === $style->display
                    || 'inline-block' === $style->display
                    || 'inline-table' === $style->display
                );
                if (!$isInlineBlock && $style->display !== "inline") {
                    continue;
                }

                if (!isset($canvas)) {
                    $canvas = $frame->get_root()->get_dompdf()->get_canvas();
                }

                $baseline = $canvas->get_font_baseline($style->font_family, $style->font_size);
                $y_offset = 0;

                
                if($isInlineBlock) {
                    $lineFrames = $line->get_frames();
                    if (count($lineFrames) == 1) {
                        continue;
                    }
                    $frameBox = $frame->get_frame()->get_border_box();
                    $imageHeightDiff = $height * 0.8 - (float)$frameBox['h'];

                    $align = $frame->get_style()->vertical_align;
                    if (in_array($align, Style::$vertical_align_keywords) === true) {
                        switch ($align) {
                            case "middle":
                                $y_offset = $imageHeightDiff / 2;
                                break;

                            case "sub":
                                $y_offset = 0.3 * $height + $imageHeightDiff;
                                break;

                            case "super":
                                $y_offset = -0.2 * $height + $imageHeightDiff;
                                break;

                            case "text-top": 
                                $y_offset = $height - $style->line_height;
                                break;

                            case "top":
                                break;

                            case "text-bottom": 
                            case "bottom":
                                $y_offset = 0.3 * $height + $imageHeightDiff;
                                break;

                            case "baseline":
                            default:
                                $y_offset = $imageHeightDiff;
                                break;
                        }
                    } else {
                        $y_offset = $baseline - (float)$style->length_in_pt($align, $style->font_size) - (float)$frameBox['h'];
                    }
                } else {
                    $parent = $frame->get_parent();
                    if ($parent instanceof TableCellFrameDecorator) {
                        $align = "baseline";
                    } else {
                        $align = $parent->get_style()->vertical_align;
                    }
                    if (in_array($align, Style::$vertical_align_keywords) === true) {
                        switch ($align) {
                            case "middle":
                                $y_offset = ($height * 0.8 - $baseline) / 2;
                                break;

                            case "sub":
                                $y_offset = $height * 0.8 - $baseline * 0.5;
                                break;

                            case "super":
                                $y_offset = $height * 0.8 - $baseline * 1.4;
                                break;

                            case "text-top":
                            case "top": 
                                break;

                            case "text-bottom":
                            case "bottom":
                                $y_offset = $height * 0.8 - $baseline;
                                break;

                            case "baseline":
                            default:
                                $y_offset = $height * 0.8 - $baseline;
                                break;
                        }
                    } else {
                        $y_offset = $height * 0.8 - $baseline - (float)$style->length_in_pt($align, $style->font_size);
                    }
                }

                if ($y_offset !== 0) {
                    $frame->move(0, $y_offset);
                }
            }
        }
    }

    
    function process_clear(Frame $child)
    {
        $child_style = $child->get_style();
        $root = $this->_frame->get_root();

        
        if ($child_style->clear !== "none") {
            
            if ($child->get_prev_sibling() !== null) {
                $this->_frame->add_line();
            }
            if ($child_style->float !== "none" && $child->get_next_sibling()) {
                $this->_frame->set_current_line_number($this->_frame->get_current_line_number() - 1);
            }

            $lowest_y = $root->get_lowest_float_offset($child);

            
            if ($lowest_y) {
                if ($child->is_in_flow()) {
                    $line_box = $this->_frame->get_current_line_box();
                    $line_box->y = $lowest_y + $child->get_margin_height();
                    $line_box->left = 0;
                    $line_box->right = 0;
                }

                $child->move(0, $lowest_y - $child->get_position("y"));
            }
        }
    }

    
    function process_float(Frame $child, $cb_x, $cb_w)
    {
        $child_style = $child->get_style();
        $root = $this->_frame->get_root();

        
        if ($child_style->float !== "none") {
            $root->add_floating_frame($child);

            
            $next = $child->get_next_sibling();
            if ($next && $next instanceof TextFrameDecorator) {
                $next->set_text(ltrim($next->get_text()));
            }

            $line_box = $this->_frame->get_current_line_box();
            list($old_x, $old_y) = $child->get_position();

            $float_x = $cb_x;
            $float_y = $old_y;
            $float_w = $child->get_margin_width();

            if ($child_style->clear === "none") {
                switch ($child_style->float) {
                    case "left":
                        $float_x += $line_box->left;
                        break;
                    case "right":
                        $float_x += ($cb_w - $line_box->right - $float_w);
                        break;
                }
            } else {
                if ($child_style->float === "right") {
                    $float_x += ($cb_w - $float_w);
                }
            }

            if ($cb_w < $float_x + $float_w - $old_x) {
                
            }

            $line_box->get_float_offsets();

            if ($child->_float_next_line) {
                $float_y += $line_box->h;
            }

            $child->set_position($float_x, $float_y);
            $child->move($float_x - $old_x, $float_y - $old_y, true);
        }
    }

    
    function reflow(BlockFrameDecorator $block = null)
    {

        
        $page = $this->_frame->get_root();
        $page->check_forced_page_break($this->_frame);

        
        if ($page->is_full()) {
            return;
        }

        
        $this->_set_content();

        
        $this->_collapse_margins();

        $style = $this->_frame->get_style();
        $cb = $this->_frame->get_containing_block();

        if ($style->position === "fixed") {
            $cb = $this->_frame->get_root()->get_containing_block();
        }

        
        
        list($w, $left_margin, $right_margin, $left, $right) = $this->_calculate_restricted_width();

        
        $style->width = $w;
        $style->margin_left = $left_margin;
        $style->margin_right = $right_margin;
        $style->left = $left;
        $style->right = $right;

        
        $this->_frame->position();
        list($x, $y) = $this->_frame->get_position();

        
        $indent = (float)$style->length_in_pt($style->text_indent, $cb["w"]);
        $this->_frame->increase_line_width($indent);

        
        $top = (float)$style->length_in_pt([$style->margin_top,
            $style->padding_top,
            $style->border_top_width], $cb["h"]);

        $bottom = (float)$style->length_in_pt([$style->border_bottom_width,
            $style->margin_bottom,
            $style->padding_bottom], $cb["h"]);

        $cb_x = $x + (float)$left_margin + (float)$style->length_in_pt([$style->border_left_width,
                $style->padding_left], $cb["w"]);

        $cb_y = $y + $top;

        $cb_h = ($cb["h"] + $cb["y"]) - $bottom - $cb_y;

        
        $line_box = $this->_frame->get_current_line_box();
        $line_box->y = $cb_y;
        $line_box->get_float_offsets();

        
        foreach ($this->_frame->get_children() as $child) {

            
            if ($page->is_full()) {
                break;
            }

            $child->set_containing_block($cb_x, $cb_y, $w, $cb_h);

            $this->process_clear($child);

            $child->reflow($this->_frame);

            
            if ($page->check_page_break($child)) {
                break;
            }

            $this->process_float($child, $cb_x, $w);
        }

        
        list($height, $margin_top, $margin_bottom, $top, $bottom) = $this->_calculate_restricted_height();
        $style->height = $height;
        $style->margin_top = $margin_top;
        $style->margin_bottom = $margin_bottom;
        $style->top = $top;
        $style->bottom = $bottom;

        $orig_style = $this->_frame->get_original_style();

        $needs_reposition = ($style->position === "absolute" && ($style->right !== "auto" || $style->bottom !== "auto"));

        
        if ($needs_reposition) {
            if ($orig_style->width === "auto" && ($orig_style->left === "auto" || $orig_style->right === "auto")) {
                $width = 0;
                foreach ($this->_frame->get_line_boxes() as $line) {
                    $width = max($line->w, $width);
                }
                $style->width = $width;
            }

            $style->left = $orig_style->left;
            $style->right = $orig_style->right;
        }

        
        if (($style->display === "inline-block" || $style->float !== 'none') && $orig_style->width === 'auto') {
            $width = 0;

            foreach ($this->_frame->get_line_boxes() as $line) {
                $line->recalculate_width();

                $width = max($line->w, $width);
            }

            if ($width === 0) {
                foreach ($this->_frame->get_children() as $child) {
                    $width += $child->calculate_auto_width();
                }
            }

            $style->width = $width;
        }

        $this->_text_align();
        $this->vertical_align();

        
        if ($needs_reposition) {
            list($x, $y) = $this->_frame->get_position();
            $this->_frame->position();
            list($new_x, $new_y) = $this->_frame->get_position();
            $this->_frame->move($new_x - $x, $new_y - $y, true);
        }

        if ($block && $this->_frame->is_in_flow()) {
            $block->add_frame_to_line($this->_frame);

            
            if ($style->display === "block") {
                $block->add_line();
            }
        }
    }

    
    public function calculate_auto_width()
    {
        $width = 0;

        foreach ($this->_frame->get_line_boxes() as $line) {
            $line_width = 0;

            foreach ($line->get_frames() as $frame) {
                if ($frame->get_original_style()->width == 'auto') {
                    $line_width += $frame->calculate_auto_width();
                } else {
                    $line_width += $frame->get_margin_width();
                }
            }

            $width = max($line_width, $width);
        }

        $this->_frame->get_style()->width = $width;

        return $this->_frame->get_margin_width();
    }
}
