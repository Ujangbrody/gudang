<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Css\Style;
use Dompdf\Dompdf;
use Dompdf\Helpers;
use Dompdf\Frame;
use Dompdf\Renderer;


class Page extends AbstractFrameDecorator
{

    
    protected $_bottom_page_margin;

    
    protected $_page_full;

    
    protected $_in_table;

    
    protected $_renderer;

    
    protected $_floating_frames = [];

    

    
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
        $this->_page_full = false;
        $this->_in_table = 0;
        $this->_bottom_page_margin = null;
    }

    
    function set_renderer($renderer)
    {
        $this->_renderer = $renderer;
    }

    
    function get_renderer()
    {
        return $this->_renderer;
    }

    
    function set_containing_block($x = null, $y = null, $w = null, $h = null)
    {
        parent::set_containing_block($x, $y, $w, $h);
        
        if (isset($h)) {
            $this->_bottom_page_margin = $h;
        } 
    }

    
    function is_full()
    {
        return $this->_page_full;
    }

    
    function next_page()
    {
        $this->_floating_frames = [];
        $this->_renderer->new_page();
        $this->_page_full = false;
    }

    
    function table_reflow_start()
    {
        $this->_in_table++;
    }

    
    function table_reflow_end()
    {
        $this->_in_table--;
    }

    
    function in_nested_table()
    {
        return $this->_in_table > 1;
    }

    
    function check_forced_page_break(Frame $frame)
    {
        
        if ($this->_page_full) {
            return null;
        }

        $block_types = ["block", "list-item", "table", "inline"];
        $page_breaks = ["always", "left", "right"];

        $style = $frame->get_style();

        if (!in_array($style->display, $block_types)) {
            return false;
        }

        
        $prev = $frame->get_prev_sibling();

        while ($prev && !in_array($prev->get_style()->display, $block_types)) {
            $prev = $prev->get_prev_sibling();
        }

        if (in_array($style->page_break_before, $page_breaks)) {
            
            $frame->split(null, true);
            
            
            $frame->get_style()->page_break_before = "auto";
            $this->_page_full = true;
            $frame->_already_pushed = true;

            return true;
        }

        if ($prev && in_array($prev->get_style()->page_break_after, $page_breaks)) {
            
            $frame->split(null, true);
            $prev->get_style()->page_break_after = "auto";
            $this->_page_full = true;
            $frame->_already_pushed = true;

            return true;
        }

        if ($prev && $prev->get_last_child() && $frame->get_node()->nodeName != "body") {
            $prev_last_child = $prev->get_last_child();
            if (in_array($prev_last_child->get_style()->page_break_after, $page_breaks)) {
                $frame->split(null, true);
                $prev_last_child->get_style()->page_break_after = "auto";
                $this->_page_full = true;
                $frame->_already_pushed = true;

                return true;
            }
        }

        return false;
    }

    
    protected function _page_break_allowed(Frame $frame)
    {
        $block_types = ["block", "list-item", "table", "-dompdf-image"];
        Helpers::dompdf_debug("page-break", "_page_break_allowed(" . $frame->get_node()->nodeName . ")");
        $display = $frame->get_style()->display;

        
        if (in_array($display, $block_types)) {

            
            if ($this->_in_table > ($display === "table" ? 1 : 0)) {
                Helpers::dompdf_debug("page-break", "In table: " . $this->_in_table);

                return false;
            }

            

            if ($frame->get_style()->page_break_before === "avoid") {
                Helpers::dompdf_debug("page-break", "before: avoid");

                return false;
            }

            
            $prev = $frame->get_prev_sibling();
            while ($prev && !in_array($prev->get_style()->display, $block_types)) {
                $prev = $prev->get_prev_sibling();
            }

            
            if ($prev && $prev->get_style()->page_break_after === "avoid") {
                Helpers::dompdf_debug("page-break", "after: avoid");

                return false;
            }

            
            
            $parent = $frame->get_parent();
            if ($prev && $parent && $parent->get_style()->page_break_inside === "avoid") {
                Helpers::dompdf_debug("page-break", "parent inside: avoid");

                return false;
            }

            
            
            
            if ($parent->get_node()->nodeName === "body" && !$prev) {
                
                Helpers::dompdf_debug("page-break", "Body's first child.");

                return false;
            }

            
            
            
            if (!$prev && $parent) {
                Helpers::dompdf_debug("page-break", "First block level frame, checking gap");

                return $frame->get_style()->length_in_pt($frame->get_style()->margin_top) != 0
                    || $parent->get_style()->length_in_pt($parent->get_style()->padding_top) != 0;
            }

            Helpers::dompdf_debug("page-break", "block: break allowed");

            return true;

        } 
        else {
            if (in_array($display, Style::$INLINE_TYPES)) {

                
                if ($this->_in_table) {
                    Helpers::dompdf_debug("page-break", "In table: " . $this->_in_table);

                    return false;
                }

                
                $block_parent = $frame->find_block_parent();
                if (count($block_parent->get_line_boxes()) < $frame->get_style()->orphans) {
                    Helpers::dompdf_debug("page-break", "orphans");

                    return false;
                }

                
                

                
                $p = $block_parent;
                while ($p) {
                    if ($p->get_style()->page_break_inside === "avoid") {
                        Helpers::dompdf_debug("page-break", "parent->inside: avoid");

                        return false;
                    }
                    $p = $p->find_block_parent();
                }

                
                
                
                $prev = $frame->get_prev_sibling();
                while ($prev && ($prev->is_text_node() && trim($prev->get_node()->nodeValue) == "")) {
                    $prev = $prev->get_prev_sibling();
                }

                if ($block_parent->get_node()->nodeName === "body" && !$prev) {
                    
                    Helpers::dompdf_debug("page-break", "Body's first child.");

                    return false;
                }

                
                if ($frame->is_text_node() && $frame->get_node()->nodeValue == "") {
                    return false;
                }

                Helpers::dompdf_debug("page-break", "inline: break allowed");

                return true;

            
            } else {
                if ($display === "table-row") {
                    
                    
                    $table = Table::find_parent_table($frame);

                    $p = $table;
                    while ($p) {
                        if ($p->get_style()->page_break_inside === "avoid") {
                            Helpers::dompdf_debug("page-break", "parent->inside: avoid");

                            return false;
                        }
                        $p = $p->find_block_parent();
                    }

                    
                    if ($table && $table->get_first_child() === $frame || $table->get_first_child()->get_first_child() === $frame) {
                        Helpers::dompdf_debug("page-break", "table: first-row");

                        return false;
                    }

                    
                    if ($this->_in_table > 1) {
                        Helpers::dompdf_debug("page-break", "table: nested table");

                        return false;
                    }

                    Helpers::dompdf_debug("page-break", "table-row/row-groups: break allowed");

                    return true;
                } else {
                    if (in_array($display, Table::$ROW_GROUPS)) {

                        
                        return false;

                    } else {
                        Helpers::dompdf_debug("page-break", "? " . $frame->get_style()->display . "");

                        return false;
                    }
                }
            }
        }
    }

    
    function check_page_break(Frame $frame)
    {
        if ($this->_page_full || $frame->_already_pushed) {
            return false;
        }

        $p = $frame;
        do {
            $display = $p->get_style()->display;
            if ($display == "table-row") {
                if ($p->_already_pushed) { return false; }
            }
        } while ($p = $p->get_parent());

        
        $p = $frame;
        do {
            if ($p->is_absolute()) {
                return false;
            }
        } while ($p = $p->get_parent());

        $margin_height = $frame->get_margin_height();

        
        $max_y = (float)$frame->get_position("y") + $margin_height;

        
        
        $p = $frame->get_parent();
        while ($p) {
            $max_y += (float) $p->get_style()->computed_bottom_spacing();
            $p = $p->get_parent();
        }

        
        if ($max_y <= $this->_bottom_page_margin) {
            
            return false;
        }

        Helpers::dompdf_debug("page-break", "check_page_break");
        Helpers::dompdf_debug("page-break", "in_table: " . $this->_in_table);

        
        $iter = $frame;
        $flg = false;
        $pushed_flg = false;

        $in_table = $this->_in_table;

        Helpers::dompdf_debug("page-break", "Starting search");
        while ($iter) {
            
            if ($iter === $this) {
                Helpers::dompdf_debug("page-break", "reached root.");
                
                break;
            }

            if ($iter->_already_pushed) {
                $pushed_flg = true;
            } elseif ($this->_page_break_allowed($iter)) {
                Helpers::dompdf_debug("page-break", "break allowed, splitting.");
                $iter->split(null, true);
                $this->_page_full = true;
                $this->_in_table = $in_table;
                $iter->_already_pushed = true;
                $frame->_already_pushed = true;

                return true;
            }

            if (!$flg && $next = $iter->get_last_child()) {
                Helpers::dompdf_debug("page-break", "following last child.");

                if ($next->is_table()) {
                    $this->_in_table++;
                }

                $iter = $next;
                $pushed_flg = false;
                continue;
            }

            if ($pushed_flg) {
                
                break;
            }

            if ($next = $iter->get_prev_sibling()) {
                Helpers::dompdf_debug("page-break", "following prev sibling.");

                if ($next->is_table() && !$iter->is_table()) {
                    $this->_in_table++;
                } else if (!$next->is_table() && $iter->is_table()) {
                    $this->_in_table--;
                }

                $iter = $next;
                $flg = false;
                continue;
            }

            if ($next = $iter->get_parent()) {
                Helpers::dompdf_debug("page-break", "following parent.");

                if ($iter->is_table()) {
                    $this->_in_table--;
                }

                $iter = $next;
                $flg = true;
                continue;
            }

            break;
        }

        $this->_in_table = $in_table;

        
        Helpers::dompdf_debug("page-break", "no valid break found, just splitting.");

        
        if ($this->_in_table) {
            $iter = $frame;
            while ($iter && $iter->get_style()->display !== "table-row" && $iter->get_style()->display !== 'table-row-group' && $iter->_already_pushed === false) {
                $iter = $iter->get_parent();
            }

            if ($iter) {
                $iter->split(null, true);
                $iter->_already_pushed = true;
            } else {
                return false;
            }
        } else {
            $frame->split(null, true);
        }

        $this->_page_full = true;
        $frame->_already_pushed = true;

        return true;
    }

    

    
    function split(Frame $frame = null, $force_pagebreak = false)
    {
        
    }

    
    function add_floating_frame(Frame $frame)
    {
        array_unshift($this->_floating_frames, $frame);
    }

    
    function get_floating_frames()
    {
        return $this->_floating_frames;
    }

    
    public function remove_floating_frame($key)
    {
        unset($this->_floating_frames[$key]);
    }

    
    public function get_lowest_float_offset(Frame $child)
    {
        $style = $child->get_style();
        $side = $style->clear;
        $float = $style->float;

        $y = 0;

        if ($float === "none") {
            foreach ($this->_floating_frames as $key => $frame) {
                if ($side === "both" || $frame->get_style()->float === $side) {
                    $y = max($y, $frame->get_position("y") + $frame->get_margin_height());
                }
                $this->remove_floating_frame($key);
            }
        }

        if ($y > 0) {
            $y++; 
        }

        return $y;
    }
}
