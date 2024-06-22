<?php
namespace Dompdf\Frame;

use Dompdf\Frame;
use IteratorAggregate;


class FrameList implements IteratorAggregate
{
    
    protected $_frame;

    
    function __construct($frame)
    {
        $this->_frame = $frame;
    }

    
    function getIterator()
    {
        return new FrameListIterator($this->_frame);
    }
}
