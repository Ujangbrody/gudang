<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\Helpers;
use Dompdf\Image\Cache;


class ListBulletImage extends AbstractFrameDecorator
{

    
    protected $_img;

    
    protected $_width;

    
    protected $_height;

    
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        $style = $frame->get_style();
        $url = $style->list_style_image;
        $frame->get_node()->setAttribute("src", $url);
        $this->_img = new Image($frame, $dompdf);
        parent::__construct($this->_img, $dompdf);

        if (Cache::is_broken($this->_img->get_image_url())) {
            $width = 0;
            $height = 0;
        } else {
            list($width, $height) = Helpers::dompdf_getimagesize($this->_img->get_image_url(), $dompdf->getHttpContext());
        }

        
        
        
        $dpi = $this->_dompdf->getOptions()->getDpi();
        $this->_width = ((float)rtrim($width, "px") * 72) / $dpi;
        $this->_height = ((float)rtrim($height, "px") * 72) / $dpi;

        
        
        
        
        
        
        
        
        
        
        
    }

    
    function get_width()
    {
        
        
        
        
        return $this->_frame->get_style()->font_size * ListBullet::BULLET_SIZE +
        2 * ListBullet::BULLET_PADDING;
    }

    
    function get_height()
    {
        
        if ($this->_height == 0) {
            $style = $this->_frame->get_style();

            if ($style->list_style_type === "none") {
                return 0;
            }
    
            return $style->font_size * ListBullet::BULLET_SIZE + 2 * ListBullet::BULLET_PADDING;
        } else {
            return $this->_height;
        }
    }

    
    function get_margin_width()
    {
        
        
        
        
        
        

        
        
        if ($this->_frame->get_style()->list_style_position === "outside" || $this->_width == 0) {
            return 0;
        }
        
        
        
        
        
        return $this->_width + 2 * ListBullet::BULLET_PADDING;
    }

    
    function get_margin_height()
    {
        
        
        return $this->_height + 2 * ListBullet::BULLET_PADDING;
    }

    
    function get_image_url()
    {
        return $this->_img->get_image_url();
    }

}
