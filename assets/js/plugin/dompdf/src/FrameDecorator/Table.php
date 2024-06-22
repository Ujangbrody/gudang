<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Cellmap;
use DOMNode;
use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\Frame\Factory;


class Table extends AbstractFrameDecorator
{
    public static $VALID_CHILDREN = [
        "table-row-group",
        "table-row",
        "table-header-group",
        "table-footer-group",
        "table-column",
        "table-column-group",
        "table-caption",
        "table-cell"
    ];

    public static $ROW_GROUPS = [
        'table-row-group',
        'table-header-group',
        'table-footer-group'
    ];

    
    protected $_cellmap;

    
    protected $_min_width;

    
    protected $_max_width;

    
    protected $_headers;

    
    protected $_footers;

    
    public function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
        $this->_cellmap = new Cellmap($this);

        if ($frame->get_style()->table_layout === "fixed") {
            $this->_cellmap->set_layout_fixed(true);
        }

        $this->_min_width = null;
        $this->_max_width = null;
        $this->_headers = [];
        $this->_footers = [];
    }

    public function reset()
    {
        parent::reset();
        $this->_cellmap->reset();
        $this->_min_width = null;
        $this->_max_width = null;
        $this->_headers = [];
        $this->_footers = [];
        $this->_reflower->reset();
    }

    

    
    public function split(Frame $child = null, $force_pagebreak = false)
    {
        if (is_null($child)) {
            parent::split();

            return;
        }

        
        
        if (count($this->_headers) && !in_array($child, $this->_headers, true) &&
            !in_array($child->get_prev_sibling(), $this->_headers, true)
        ) {
            $first_header = null;

            
            foreach ($this->_headers as $header) {

                $new_header = $header->deep_copy();

                if (is_null($first_header)) {
                    $first_header = $new_header;
                }

                $this->insert_child_before($new_header, $child);
            }

            parent::split($first_header);

        } elseif (in_array($child->get_style()->display, self::$ROW_GROUPS)) {

            
            parent::split($child);

        } else {

            $iter = $child;

            while ($iter) {
                $this->_cellmap->remove_row($iter);
                $iter = $iter->get_next_sibling();
            }

            parent::split($child);
        }
    }

    
    public function copy(DOMNode $node)
    {
        $deco = parent::copy($node);

        
        $deco->_cellmap->set_columns($this->_cellmap->get_columns());
        $deco->_cellmap->lock_columns();

        return $deco;
    }

    
    public static function find_parent_table(Frame $frame)
    {
        while ($frame = $frame->get_parent()) {
            if ($frame->is_table()) {
                break;
            }
        }

        return $frame;
    }

    
    public function get_cellmap()
    {
        return $this->_cellmap;
    }

    
    public function get_min_width()
    {
        return $this->_min_width;
    }

    
    public function get_max_width()
    {
        return $this->_max_width;
    }

    
    public function set_min_width($width)
    {
        $this->_min_width = $width;
    }

    
    public function set_max_width($width)
    {
        $this->_max_width = $width;
    }

    
    public function normalise()
    {
        
        $erroneous_frames = [];
        $anon_row = false;
        $iter = $this->get_first_child();
        while ($iter) {
            $child = $iter;
            $iter = $iter->get_next_sibling();

            $display = $child->get_style()->display;

            if ($anon_row) {

                if ($display === "table-row") {
                    
                    $this->insert_child_before($table_row, $child);

                    $table_row->normalise();
                    $child->normalise();
                    $this->_cellmap->add_row();
                    $anon_row = false;
                    continue;
                }

                
                $table_row->append_child($child);
                continue;

            } else {

                if ($display === "table-row") {
                    $child->normalise();
                    continue;
                }

                if ($display === "table-cell") {
                    $css = $this->get_style()->get_stylesheet();

                    
                    $tbody = $this->get_node()->ownerDocument->createElement("tbody");

                    $frame = new Frame($tbody);

                    $style = $css->create_style();
                    $style->inherit($this->get_style());

                    
                    
                    
                    if ($tbody_style = $css->lookup("tbody")) {
                        $style->merge($tbody_style);
                    }
                    $style->display = 'table-row-group';

                    
                    
                    $frame->set_style($style);
                    $table_row_group = Factory::decorate_frame($frame, $this->_dompdf, $this->_root);

                    
                    $tr = $this->get_node()->ownerDocument->createElement("tr");

                    $frame = new Frame($tr);

                    $style = $css->create_style();
                    $style->inherit($this->get_style());

                    
                    
                    
                    if ($tr_style = $css->lookup("tr")) {
                        $style->merge($tr_style);
                    }
                    $style->display = 'table-row';

                    
                    
                    $frame->set_style(clone $style);
                    $table_row = Factory::decorate_frame($frame, $this->_dompdf, $this->_root);

                    
                    $table_row->append_child($child, true);

                    
                    $table_row_group->append_child($table_row, true);

                    $anon_row = true;
                    continue;
                }

                if (!in_array($display, self::$VALID_CHILDREN)) {
                    $erroneous_frames[] = $child;
                    continue;
                }

                
                foreach ($child->get_children() as $grandchild) {
                    if ($grandchild->get_style()->display === "table-row") {
                        $grandchild->normalise();
                    }
                }

                
                if ($display === "table-header-group") {
                    $this->_headers[] = $child;
                } elseif ($display === "table-footer-group") {
                    $this->_footers[] = $child;
                }
            }
        }

        if ($anon_row && $table_row_group instanceof AbstractFrameDecorator) {
            
            $this->_frame->append_child($table_row_group->_frame);
            $table_row->normalise();
        }

        foreach ($erroneous_frames as $frame) {
            $this->move_after($frame);
        }
    }

    

    
    public function move_after(Frame $frame)
    {
        $this->get_parent()->insert_child_after($frame, $this);
    }
}