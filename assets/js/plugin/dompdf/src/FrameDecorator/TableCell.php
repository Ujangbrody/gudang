<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;


class TableCell extends BlockFrameDecorator
{

    protected $_resolved_borders;
    protected $_content_height;

    

    
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
        $this->_resolved_borders = [];
        $this->_content_height = 0;
    }

    

    function reset()
    {
        parent::reset();
        $this->_resolved_borders = [];
        $this->_content_height = 0;
        $this->_frame->reset();
    }

    
    function get_content_height()
    {
        return $this->_content_height;
    }

    
    function set_content_height($height)
    {
        $this->_content_height = $height;
    }

    
    function set_cell_height($height)
    {
        $style = $this->get_style();
        $v_space = (float)$style->length_in_pt(
            [
                $style->margin_top,
                $style->padding_top,
                $style->border_top_width,
                $style->border_bottom_width,
                $style->padding_bottom,
                $style->margin_bottom
            ],
            (float)$style->length_in_pt($style->height)
        );

        $new_height = $height - $v_space;
        $style->height = $new_height;

        if ($new_height > $this->_content_height) {
            $y_offset = 0;

            
            switch ($style->vertical_align) {
                default:
                case "baseline":
                    

                case "top":
                    
                    return;

                case "middle":
                    $y_offset = ($new_height - $this->_content_height) / 2;
                    break;

                case "bottom":
                    $y_offset = $new_height - $this->_content_height;
                    break;
            }

            if ($y_offset) {
                
                foreach ($this->get_line_boxes() as $line) {
                    foreach ($line->get_frames() as $frame) {
                        $frame->move(0, $y_offset);
                    }
                }
            }
        }
    }

    
    function set_resolved_border($side, $border_spec)
    {
        $this->_resolved_borders[$side] = $border_spec;
    }

    
    function get_resolved_border($side)
    {
        return $this->_resolved_borders[$side];
    }

    
    function get_resolved_borders()
    {
        return $this->_resolved_borders;
    }
}
