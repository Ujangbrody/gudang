<?php

namespace Dompdf\FrameDecorator;

use DOMElement;
use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\Exception;


class Inline extends AbstractFrameDecorator
{

    
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
    }

    
    function split(Frame $frame = null, $force_pagebreak = false)
    {
        if (is_null($frame)) {
            $this->get_parent()->split($this, $force_pagebreak);
            return;
        }

        if ($frame->get_parent() !== $this) {
            throw new Exception("Unable to split: frame is not a child of this one.");
        }

        $node = $this->_frame->get_node();

        if ($node instanceof DOMElement && $node->hasAttribute("id")) {
            $node->setAttribute("data-dompdf-original-id", $node->getAttribute("id"));
            $node->removeAttribute("id");
        }

        $split = $this->copy($node->cloneNode());
        
        if ($split->get_node()->nodeName == "dompdf_generated") {
            $split->get_style()->content = "normal";
        }
        $this->get_parent()->insert_child_after($split, $this);

        
        $style = $this->_frame->get_style();
        $style->margin_right = 0;
        $style->padding_right = 0;
        $style->border_right_width = 0;

        
        
        $style = $split->get_style();
        $style->margin_left = 0;
        $style->padding_left = 0;
        $style->border_left_width = 0;

        
        
        
        if (($url = $style->background_image) && $url !== "none"
            && ($repeat = $style->background_repeat) && $repeat !== "repeat" && $repeat !== "repeat-y"
        ) {
            $style->background_image = "none";
        }

        
        $iter = $frame;
        while ($iter) {
            $frame = $iter;
            $iter = $iter->get_next_sibling();
            $frame->reset();
            $split->append_child($frame);
        }

        $page_breaks = ["always", "left", "right"];
        $frame_style = $frame->get_style();
        if ($force_pagebreak ||
            in_array($frame_style->page_break_before, $page_breaks) ||
            in_array($frame_style->page_break_after, $page_breaks)
        ) {
            $this->get_parent()->split($split, true);
        }
    }

}
