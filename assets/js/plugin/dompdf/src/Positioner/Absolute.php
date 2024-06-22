<?php


namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;


class Absolute extends AbstractPositioner
{

    
    function position(AbstractFrameDecorator $frame)
    {
        $style = $frame->get_style();

        $p = $frame->find_positionned_parent();

        list($x, $y, $w, $h) = $frame->get_containing_block();

        $top = $style->length_in_pt($style->top, $h);
        $right = $style->length_in_pt($style->right, $w);
        $bottom = $style->length_in_pt($style->bottom, $h);
        $left = $style->length_in_pt($style->left, $w);

        if ($p && !($left === "auto" && $right === "auto")) {
            
            list($x, $y, $w, $h) = $p->get_padding_box();
        }

        list($width, $height) = [$frame->get_margin_width(), $frame->get_margin_height()];

        $orig_style = $frame->get_original_style();
        $orig_width = $orig_style->width;
        $orig_height = $orig_style->height;

        

        if ($left === "auto") {
            if ($right === "auto") {
                
                $x = $x + $frame->find_block_parent()->get_current_line_box()->w;
            } else {
                if ($orig_width === "auto") {
                    
                    $x += $w - $width - $right;
                } else {
                    
                    $x += $w - $width - $right;
                }
            }
        } else {
            if ($right === "auto") {
                
                $x += (float)$left;
            } else {
                if ($orig_width === "auto") {
                    
                    $x += (float)$left;
                } else {
                    
                    $x += (float)$left;
                }
            }
        }

        
        if ($top === "auto") {
            if ($bottom === "auto") {
                
                $y = $frame->find_block_parent()->get_current_line_box()->y;
            } else {
                if ($orig_height === "auto") {
                    
                    $y += (float)$h - $height - (float)$bottom;
                } else {
                    
                    $y += (float)$h - $height - (float)$bottom;
                }
            }
        } else {
            if ($bottom === "auto") {
                
                $y += (float)$top;
            } else {
                if ($orig_height === "auto") {
                    
                    $y += (float)$top;
                } else {
                    
                    $y += (float)$top;
                }
            }
        }

        $frame->set_position($x, $y);
    }
}
