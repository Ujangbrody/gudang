<?php

namespace Dompdf\FrameReflower;

use Dompdf\Frame;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\FrameDecorator\Text as TextFrameDecorator;


class Inline extends AbstractFrameReflower
{

    
    function __construct(Frame $frame)
    {
        parent::__construct($frame);
    }

    
    function reflow(BlockFrameDecorator $block = null)
    {
        $frame = $this->_frame;

        
        $page = $frame->get_root();
        $page->check_forced_page_break($frame);

        if ($page->is_full()) {
            return;
        }

        $style = $frame->get_style();

        
        $this->_set_content();

        $frame->position();

        $cb = $frame->get_containing_block();

        
        if (($f = $frame->get_first_child()) && $f instanceof TextFrameDecorator) {
            $f_style = $f->get_style();
            $f_style->margin_left = $style->margin_left;
            $f_style->padding_left = $style->padding_left;
            $f_style->border_left = $style->border_left;
        }

        if (($l = $frame->get_last_child()) && $l instanceof TextFrameDecorator) {
            $l_style = $l->get_style();
            $l_style->margin_right = $style->margin_right;
            $l_style->padding_right = $style->padding_right;
            $l_style->border_right = $style->border_right;
        }

        if ($block) {
            $block->add_frame_to_line($this->_frame);
        }

        
        
        foreach ($frame->get_children() as $child) {
            $child->set_containing_block($cb);
            $child->reflow($block);
        }
    }

    
    public function calculate_auto_width()
    {
        $width = 0;

        foreach ($this->_frame->get_children() as $child) {
            if ($child->get_original_style()->width == 'auto') {
                $width += $child->calculate_auto_width();
            } else {
                $width += $child->get_margin_width();
            }
        }

        $this->_frame->get_style()->width = $width;

        return $this->_frame->get_margin_width();
    }
}
