<?php
namespace Dompdf\Frame;

use Iterator;
use Dompdf\Frame;


class FrameTreeIterator implements Iterator
{
    
    protected $_root;

    
    protected $_stack = [];

    
    protected $_num;

    
    public function __construct(Frame $root)
    {
        $this->_stack[] = $this->_root = $root;
        $this->_num = 0;
    }

    
    public function rewind()
    {
        $this->_stack = [$this->_root];
        $this->_num = 0;
    }

    
    public function valid()
    {
        return count($this->_stack) > 0;
    }

    
    public function key()
    {
        return $this->_num;
    }

    
    public function current()
    {
        return end($this->_stack);
    }

    
    public function next()
    {
        $b = end($this->_stack);

        
        unset($this->_stack[key($this->_stack)]);
        $this->_num++;

        
        if ($c = $b->get_last_child()) {
            $this->_stack[] = $c;
            while ($c = $c->get_prev_sibling()) {
                $this->_stack[] = $c;
            }
        }

        return $b;
    }
}

