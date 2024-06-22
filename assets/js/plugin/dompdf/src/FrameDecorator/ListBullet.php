<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;


class ListBullet extends AbstractFrameDecorator
{

    const BULLET_PADDING = 1; 
    
    const BULLET_THICKNESS = 0.04; 
    const BULLET_DESCENT = 0.3; 
    const BULLET_SIZE = 0.35; 

    static $BULLET_TYPES = ["disc", "circle", "square"];

    
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
    }

    
    function get_margin_width()
    {
        $style = $this->_frame->get_style();

        if ($style->list_style_type === "none") {
            return 0;
        }

        return $style->font_size * self::BULLET_SIZE + 2 * self::BULLET_PADDING;
    }

    
    function get_margin_height()
    {
        $style = $this->_frame->get_style();

        if ($style->list_style_type === "none") {
            return 0;
        }

        return $style->font_size * self::BULLET_SIZE + 2 * self::BULLET_PADDING;
    }

    
    function get_width()
    {
        return $this->get_margin_width();
    }

    
    function get_height()
    {
        return $this->get_margin_height();
    }

    
}
