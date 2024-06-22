<?php

namespace Dompdf\FrameReflower;

use Dompdf\Frame;
use Dompdf\Helpers;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\FrameDecorator\Image as ImageFrameDecorator;


class Image extends AbstractFrameReflower
{

    
    function __construct(ImageFrameDecorator $frame)
    {
        parent::__construct($frame);
    }

    
    function reflow(BlockFrameDecorator $block = null)
    {
        $this->_frame->position();

        
        
        

        
        
        

        
        $this->get_min_max_width();

        if ($block) {
            $block->add_frame_to_line($this->_frame);
        }
    }

    
    function get_min_max_width()
    {
        $frame = $this->_frame;

        if ($this->get_dompdf()->getOptions()->getDebugPng()) {
            
            list($img_width, $img_height) = Helpers::dompdf_getimagesize($frame->get_image_url(), $this->get_dompdf()->getHttpContext());
            print "get_min_max_width() " .
                $frame->get_style()->width . ' ' .
                $frame->get_style()->height . ';' .
                $frame->get_parent()->get_style()->width . " " .
                $frame->get_parent()->get_style()->height . ";" .
                $frame->get_parent()->get_parent()->get_style()->width . ' ' .
                $frame->get_parent()->get_parent()->get_style()->height . ';' .
                $img_width . ' ' .
                $img_height . '|';
        }

        $style = $frame->get_style();

        $width_forced = true;
        $height_forced = true;

        
        
        
        
        

        $width = $this->get_size($frame, 'width');
        $height = $this->get_size($frame, 'height');

        if ($width === 'auto' || $height === 'auto') {
            
            list($img_width, $img_height) = Helpers::dompdf_getimagesize($frame->get_image_url(), $this->get_dompdf()->getHttpContext());

            
            
            
            if ($width === 'auto' && $height === 'auto') {
                $dpi = $frame->get_dompdf()->getOptions()->getDpi();
                $width = (float)($img_width * 72) / $dpi;
                $height = (float)($img_height * 72) / $dpi;
                $width_forced = false;
                $height_forced = false;
            } elseif ($height === 'auto') {
                $height_forced = false;
                $height = ($width / $img_width) * $img_height; 
            } else {
                $width_forced = false;
                $width = ($height / $img_height) * $img_width; 
            }
        }

        
        if ($style->min_width !== "none" ||
            $style->max_width !== "none" ||
            $style->min_height !== "none" ||
            $style->max_height !== "none"
        ) {

            list( , , $w, $h) = $frame->get_containing_block();

            $min_width = $style->length_in_pt($style->min_width, $w);
            $max_width = $style->length_in_pt($style->max_width, $w);
            $min_height = $style->length_in_pt($style->min_height, $h);
            $max_height = $style->length_in_pt($style->max_height, $h);

            if ($max_width !== "none" && $max_width !== "auto" && $width > (float)$max_width) {
                if (!$height_forced) {
                    $height *= (float)$max_width / $width;
                }

                $width = (float)$max_width;
            }

            if ($min_width !== "none" && $min_width !== "auto" && $width < (float)$min_width) {
                if (!$height_forced) {
                    $height *= (float)$min_width / $width;
                }

                $width = (float)$min_width;
            }

            if ($max_height !== "none" && $max_height !== "auto" && $height > (float)$max_height) {
                if (!$width_forced) {
                    $width *= (float)$max_height / $height;
                }

                $height = (float)$max_height;
            }

            if ($min_height !== "none" && $min_height !== "auto" && $height < (float)$min_height) {
                if (!$width_forced) {
                    $width *= (float)$min_height / $height;
                }

                $height = (float)$min_height;
            }
        }

        if ($this->get_dompdf()->getOptions()->getDebugPng()) {
            print $width . ' ' . $height . ';';
        }

        $style->width = $width . "pt";
        $style->height = $height . "pt";

        $style->min_width = "none";
        $style->max_width = "none";
        $style->min_height = "none";
        $style->max_height = "none";

        return [$width, $width, "min" => $width, "max" => $width];
    }

    private function get_size(Frame $f, string $type)
    {
        $ref_stack = [];
        $result_size = 0.0;
        do {
            $f_style = $f->get_style();
            $current_size = $f_style->$type;
            if (Helpers::is_percent($current_size)) {
                $ref_stack[] = str_replace('%px', '%', $current_size);
            } else {
                
                if ($current_size !== 'auto' || count($ref_stack) === 0) {
                    $result_size = $f_style->length_in_pt($current_size);
                    break;
                }
            }
        } while (($f = $f->get_parent()));

        
        if (count($ref_stack) > 0) {
            while (($ref = array_pop($ref_stack))) {
                $result_size = $f_style->length_in_pt($ref, $result_size);
            }
        }

        return $result_size;
    }
}
