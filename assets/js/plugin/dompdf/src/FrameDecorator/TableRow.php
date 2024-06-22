<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\FrameDecorator\Table as TableFrameDecorator;


class TableRow extends AbstractFrameDecorator
{
    
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
    }

    

    
    function normalise()
    {
        
        $p = TableFrameDecorator::find_parent_table($this);

        $erroneous_frames = [];
        foreach ($this->get_children() as $child) {
            $display = $child->get_style()->display;

            if ($display !== "table-cell") {
                $erroneous_frames[] = $child;
            }
        }

        
        foreach ($erroneous_frames as $frame) {
            $p->move_after($frame);
        }
    }

    function split(Frame $child = null, $force_pagebreak = false)
    {
        $this->_already_pushed = true;
        
        if (is_null($child)) {
            parent::split();
            return;
        }

        parent::split($child, $force_pagebreak);
    }
}
