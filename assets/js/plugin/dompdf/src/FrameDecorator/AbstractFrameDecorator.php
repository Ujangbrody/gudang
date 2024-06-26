<?php

namespace Dompdf\FrameDecorator;

use DOMElement;
use DOMNode;
use DOMText;
use Dompdf\Helpers;
use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\Frame\FrameTreeList;
use Dompdf\Frame\Factory;
use Dompdf\FrameReflower\AbstractFrameReflower;
use Dompdf\Css\Style;
use Dompdf\Positioner\AbstractPositioner;
use Dompdf\Exception;




abstract class AbstractFrameDecorator extends Frame
{
    const DEFAULT_COUNTER = "-dompdf-default-counter";

    public $_counters = []; 

    
    protected $_root;

    
    protected $_frame;

    
    protected $_positioner;

    
    protected $_reflower;

    
    protected $_dompdf;

    
    private $_block_parent;

    
    private $_positionned_parent;

    
    private $_cached_parent;

    
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        $this->_frame = $frame;
        $this->_root = null;
        $this->_dompdf = $dompdf;
        $frame->set_decorator($this);
    }

    
    function dispose($recursive = false)
    {
        if ($recursive) {
            while ($child = $this->get_first_child()) {
                $child->dispose(true);
            }
        }

        $this->_root = null;
        unset($this->_root);

        $this->_frame->dispose(true);
        $this->_frame = null;
        unset($this->_frame);

        $this->_positioner = null;
        unset($this->_positioner);

        $this->_reflower = null;
        unset($this->_reflower);
    }

    
    function copy(DOMNode $node)
    {
        $frame = new Frame($node);
        $frame->set_style(clone $this->_frame->get_original_style());

        return Factory::decorate_frame($frame, $this->_dompdf, $this->_root);
    }

    
    function deep_copy()
    {
        $node = $this->_frame->get_node();

        if ($node instanceof DOMElement && $node->hasAttribute("id")) {
            $node->setAttribute("data-dompdf-original-id", $node->getAttribute("id"));
            $node->removeAttribute("id");
        }

        $frame = new Frame($node->cloneNode());
        $frame->set_style(clone $this->_frame->get_original_style());

        $deco = Factory::decorate_frame($frame, $this->_dompdf, $this->_root);

        foreach ($this->get_children() as $child) {
            $deco->append_child($child->deep_copy());
        }

        return $deco;
    }

    
    function reset()
    {
        $this->_frame->reset();

        $this->_counters = [];

        $this->_cached_parent = null; 

        
        foreach ($this->get_children() as $child) {
            $child->reset();
        }
    }

    

    
    function get_id()
    {
        return $this->_frame->get_id();
    }

    
    function get_frame()
    {
        return $this->_frame;
    }

    
    function get_node()
    {
        return $this->_frame->get_node();
    }

    
    function get_style()
    {
        return $this->_frame->get_style();
    }

    
    function get_original_style()
    {
        return $this->_frame->get_original_style();
    }

    
    function get_containing_block($i = null)
    {
        return $this->_frame->get_containing_block($i);
    }

    
    function get_position($i = null)
    {
        return $this->_frame->get_position($i);
    }

    
    function get_dompdf()
    {
        return $this->_dompdf;
    }

    
    function get_margin_height()
    {
        return $this->_frame->get_margin_height();
    }

    
    function get_margin_width()
    {
        return $this->_frame->get_margin_width();
    }

    
    function get_content_box()
    {
        return $this->_frame->get_content_box();
    }

    
    function get_padding_box()
    {
        return $this->_frame->get_padding_box();
    }

    
    function get_border_box()
    {
        return $this->_frame->get_border_box();
    }

    
    function set_id($id)
    {
        $this->_frame->set_id($id);
    }

    
    function set_style(Style $style)
    {
        $this->_frame->set_style($style);
    }

    
    function set_containing_block($x = null, $y = null, $w = null, $h = null)
    {
        $this->_frame->set_containing_block($x, $y, $w, $h);
    }

    
    function set_position($x = null, $y = null)
    {
        $this->_frame->set_position($x, $y);
    }

    
    function is_auto_height()
    {
        return $this->_frame->is_auto_height();
    }

    
    function is_auto_width()
    {
        return $this->_frame->is_auto_width();
    }

    
    function __toString()
    {
        return $this->_frame->__toString();
    }

    
    function prepend_child(Frame $child, $update_node = true)
    {
        while ($child instanceof AbstractFrameDecorator) {
            $child = $child->_frame;
        }

        $this->_frame->prepend_child($child, $update_node);
    }

    
    function append_child(Frame $child, $update_node = true)
    {
        while ($child instanceof AbstractFrameDecorator) {
            $child = $child->_frame;
        }

        $this->_frame->append_child($child, $update_node);
    }

    
    function insert_child_before(Frame $new_child, Frame $ref, $update_node = true)
    {
        while ($new_child instanceof AbstractFrameDecorator) {
            $new_child = $new_child->_frame;
        }

        if ($ref instanceof AbstractFrameDecorator) {
            $ref = $ref->_frame;
        }

        $this->_frame->insert_child_before($new_child, $ref, $update_node);
    }

    
    function insert_child_after(Frame $new_child, Frame $ref, $update_node = true)
    {
        $insert_frame = $new_child;
        while ($insert_frame instanceof AbstractFrameDecorator) {
            $insert_frame = $insert_frame->_frame;
        }

        $reference_frame = $ref;
        while ($reference_frame instanceof AbstractFrameDecorator) {
            $reference_frame = $reference_frame->_frame;
        }

        $this->_frame->insert_child_after($insert_frame, $reference_frame, $update_node);
    }

    
    function remove_child(Frame $child, $update_node = true)
    {
        while ($child instanceof AbstractFrameDecorator) {
            $child = $child->_frame;
        }

        return $this->_frame->remove_child($child, $update_node);
    }

    
    function get_parent($use_cache = true)
    {
        if ($use_cache && $this->_cached_parent) {
            return $this->_cached_parent;
        }
        $p = $this->_frame->get_parent();
        if ($p && $deco = $p->get_decorator()) {
            while ($tmp = $deco->get_decorator()) {
                $deco = $tmp;
            }

            return $this->_cached_parent = $deco;
        } else {
            return $this->_cached_parent = $p;
        }
    }

    
    function get_first_child()
    {
        $c = $this->_frame->get_first_child();
        if ($c && $deco = $c->get_decorator()) {
            while ($tmp = $deco->get_decorator()) {
                $deco = $tmp;
            }

            return $deco;
        } else {
            if ($c) {
                return $c;
            }
        }

        return null;
    }

    
    function get_last_child()
    {
        $c = $this->_frame->get_last_child();
        if ($c && $deco = $c->get_decorator()) {
            while ($tmp = $deco->get_decorator()) {
                $deco = $tmp;
            }

            return $deco;
        } else {
            if ($c) {
                return $c;
            }
        }

        return null;
    }

    
    function get_prev_sibling()
    {
        $s = $this->_frame->get_prev_sibling();
        if ($s && $deco = $s->get_decorator()) {
            while ($tmp = $deco->get_decorator()) {
                $deco = $tmp;
            }

            return $deco;
        } else {
            if ($s) {
                return $s;
            }
        }

        return null;
    }

    
    function get_next_sibling()
    {
        $s = $this->_frame->get_next_sibling();
        if ($s && $deco = $s->get_decorator()) {
            while ($tmp = $deco->get_decorator()) {
                $deco = $tmp;
            }

            return $deco;
        } else {
            if ($s) {
                return $s;
            }
        }

        return null;
    }

    
    function get_subtree()
    {
        return new FrameTreeList($this);
    }

    function set_positioner(AbstractPositioner $posn)
    {
        $this->_positioner = $posn;
        if ($this->_frame instanceof AbstractFrameDecorator) {
            $this->_frame->set_positioner($posn);
        }
    }

    function set_reflower(AbstractFrameReflower $reflower)
    {
        $this->_reflower = $reflower;
        if ($this->_frame instanceof AbstractFrameDecorator) {
            $this->_frame->set_reflower($reflower);
        }
    }

    
    function get_reflower()
    {
        return $this->_reflower;
    }

    
    function set_root(Frame $root)
    {
        $this->_root = $root;

        if ($this->_frame instanceof AbstractFrameDecorator) {
            $this->_frame->set_root($root);
        }
    }

    
    function get_root()
    {
        return $this->_root;
    }

    
    function find_block_parent()
    {
        
        $p = $this->get_parent();

        while ($p) {
            if ($p->is_block()) {
                break;
            }

            $p = $p->get_parent();
        }

        return $this->_block_parent = $p;
    }

    
    function find_positionned_parent()
    {
        
        $p = $this->get_parent();
        while ($p) {
            if ($p->is_positionned()) {
                break;
            }

            $p = $p->get_parent();
        }

        if (!$p) {
            $p = $this->_root->get_first_child(); 
        }

        return $this->_positionned_parent = $p;
    }

    
    function split(Frame $child = null, $force_pagebreak = false)
    {
        
        $style = $this->_frame->get_style();
        if (
            $this->_frame->get_node()->nodeName !== "body" &&
            $style->counter_increment &&
            ($decrement = $style->counter_increment) !== "none"
        ) {
            $this->decrement_counters($decrement);
        }

        if (is_null($child)) {
            
            
            
            if (!$this->is_text_node() && $this->get_node()->hasAttribute("dompdf_before_frame_id")) {
                foreach ($this->_frame->get_children() as $child) {
                    if (
                        $this->get_node()->getAttribute("dompdf_before_frame_id") == $child->get_id() &&
                        $child->get_position('x') !== null
                    ) {
                        $style = $child->get_style();
                        if ($style->counter_increment && ($decrement = $style->counter_increment) !== "none") {
                            $this->decrement_counters($decrement);
                        }
                    }
                }
            }
            $this->get_parent()->split($this, $force_pagebreak);

            return;
        }

        if ($child->get_parent() !== $this) {
            throw new Exception("Unable to split: frame is not a child of this one.");
        }

        $node = $this->_frame->get_node();

        if ($node instanceof DOMElement && $node->hasAttribute("id")) {
            $node->setAttribute("data-dompdf-original-id", $node->getAttribute("id"));
            $node->removeAttribute("id");
        }

        $split = $this->copy($node->cloneNode());
        $split->reset();
        $split->get_original_style()->text_indent = 0;
        $split->_splitted = true;
        $split->_already_pushed = true;

        
        if ($node->nodeName !== "body") {
            
            $style = $this->_frame->get_style();
            $style->margin_bottom = 0;
            $style->padding_bottom = 0;
            $style->border_bottom = 0;

            
            $orig_style = $split->get_original_style();
            $orig_style->text_indent = 0;
            $orig_style->margin_top = 0;
            $orig_style->padding_top = 0;
            $orig_style->border_top = 0;
            $orig_style->page_break_before = "auto";
        }

        
        $this->get_parent()->insert_child_after($split, $this);
        if ($this instanceof Block) {
            foreach ($this->get_line_boxes() as $index => $line_box) {
                $line_box->get_float_offsets();
            }
        }

        
        $iter = $child;
        while ($iter) {
            $frame = $iter;
            $iter = $iter->get_next_sibling();
            $frame->reset();
            $frame->_parent = $split;
            $split->append_child($frame);

            
            if ($frame instanceof Block) {
                foreach ($frame->get_line_boxes() as $index => $line_box) {
                    $line_box->get_float_offsets();
                }
            }
        }

        $this->get_parent()->split($split, $force_pagebreak);

        
        if ($style->counter_reset && ($reset = $style->counter_reset) !== "none") {
            $vars = preg_split('/\s+/', trim($reset), 2);
            $split->_counters['__' . $vars[0]] = $this->lookup_counter_frame($vars[0])->_counters[$vars[0]];
        }
    }

    
    function reset_counter($id = self::DEFAULT_COUNTER, $value = 0)
    {
        $this->get_parent()->_counters[$id] = intval($value);
    }

    
    function decrement_counters($counters)
    {
        foreach ($counters as $id => $increment) {
            $this->increment_counter($id, intval($increment) * -1);
        }
    }

    
    function increment_counters($counters)
    {
        foreach ($counters as $id => $increment) {
            $this->increment_counter($id, intval($increment));
        }
    }

    
    function increment_counter($id = self::DEFAULT_COUNTER, $increment = 1)
    {
        $counter_frame = $this->lookup_counter_frame($id);

        if ($counter_frame) {
            if (!isset($counter_frame->_counters[$id])) {
                $counter_frame->_counters[$id] = 0;
            }

            $counter_frame->_counters[$id] += $increment;
        }
    }

    
    function lookup_counter_frame($id = self::DEFAULT_COUNTER)
    {
        $f = $this->get_parent();

        while ($f) {
            if (isset($f->_counters[$id])) {
                return $f;
            }
            $fp = $f->get_parent();

            if (!$fp) {
                return $f;
            }

            $f = $fp;
        }

        return null;
    }

    
    function counter_value($id = self::DEFAULT_COUNTER, $type = "decimal")
    {
        $type = mb_strtolower($type);

        if (!isset($this->_counters[$id])) {
            $this->_counters[$id] = 0;
        }

        $value = $this->_counters[$id];

        switch ($type) {
            default:
            case "decimal":
                return $value;

            case "decimal-leading-zero":
                return str_pad($value, 2, "0", STR_PAD_LEFT);

            case "lower-roman":
                return Helpers::dec2roman($value);

            case "upper-roman":
                return mb_strtoupper(Helpers::dec2roman($value));

            case "lower-latin":
            case "lower-alpha":
                return chr(($value % 26) + ord('a') - 1);

            case "upper-latin":
            case "upper-alpha":
                return chr(($value % 26) + ord('A') - 1);

            case "lower-greek":
                return Helpers::unichr($value + 944);

            case "upper-greek":
                return Helpers::unichr($value + 912);
        }
    }

    
    final function position()
    {
        $this->_positioner->position($this);
    }

    
    final function move($offset_x, $offset_y, $ignore_self = false)
    {
        $this->_positioner->move($this, $offset_x, $offset_y, $ignore_self);
    }

    
    final function reflow(Block $block = null)
    {
        
        
        
        $this->_reflower->reflow($block);
    }

    
    final function get_min_max_width()
    {
        return $this->_reflower->get_min_max_width();
    }

    
    final function calculate_auto_width()
    {
        return $this->_reflower->calculate_auto_width();
    }
}
