<?php

namespace Dompdf\FrameReflower;

use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\FrameDecorator\Table as TableFrameDecorator;


class Table extends AbstractFrameReflower
{
    
    protected $_frame;

    
    protected $_state;

    
    function __construct(TableFrameDecorator $frame)
    {
        $this->_state = null;
        parent::__construct($frame);
    }

    
    function reset()
    {
        $this->_state = null;
        $this->_min_max_cache = null;
    }

    protected function _assign_widths()
    {
        $style = $this->_frame->get_style();

        
        
        $min_width = $this->_state["min_width"];
        $max_width = $this->_state["max_width"];
        $percent_used = $this->_state["percent_used"];
        $absolute_used = $this->_state["absolute_used"];
        $auto_min = $this->_state["auto_min"];

        $absolute =& $this->_state["absolute"];
        $percent =& $this->_state["percent"];
        $auto =& $this->_state["auto"];

        
        $cb = $this->_frame->get_containing_block();
        $columns =& $this->_frame->get_cellmap()->get_columns();

        $width = $style->width;

        
        $left = $style->margin_left;
        $right = $style->margin_right;

        $centered = ($left === "auto" && $right === "auto");

        $left = (float)($left === "auto" ? 0 : $style->length_in_pt($left, $cb["w"]));
        $right = (float)($right === "auto" ? 0 : $style->length_in_pt($right, $cb["w"]));

        $delta = $left + $right;

        if (!$centered) {
            $delta += (float)$style->length_in_pt([
                    $style->padding_left,
                    $style->border_left_width,
                    $style->border_right_width,
                    $style->padding_right],
                $cb["w"]);
        }

        $min_table_width = (float)$style->length_in_pt($style->min_width, $cb["w"] - $delta);

        
        $min_width -= $delta;
        $max_width -= $delta;

        if ($width !== "auto") {
            $preferred_width = (float)$style->length_in_pt($width, $cb["w"]) - $delta;

            if ($preferred_width < $min_table_width) {
                $preferred_width = $min_table_width;
            }

            if ($preferred_width > $min_width) {
                $width = $preferred_width;
            } else {
                $width = $min_width;
            }

        } else {
            if ($max_width + $delta < $cb["w"]) {
                $width = $max_width;
            } else if ($cb["w"] - $delta > $min_width) {
                $width = $cb["w"] - $delta;
            } else {
                $width = $min_width;
            }

            if ($width < $min_table_width) {
                $width = $min_table_width;
            }

        }

        
        $style->width = $width;

        $cellmap = $this->_frame->get_cellmap();

        if ($cellmap->is_columns_locked()) {
            return;
        }

        
        if ($width == $max_width) {
            foreach (array_keys($columns) as $i) {
                $cellmap->set_column_width($i, $columns[$i]["max-width"]);
            }

            return;
        }

        
        if ($width > $min_width) {
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            

            $increment = 0;

            
            if ($absolute_used == 0 && $percent_used == 0) {
                $increment = $width - $min_width;

                foreach (array_keys($columns) as $i) {
                    $cellmap->set_column_width($i, $columns[$i]["min-width"] + $increment * ($columns[$i]["max-width"] / $max_width));
                }
                return;
            }

            
            if ($absolute_used > 0 && $percent_used == 0) {
                if (count($auto) > 0) {
                    $increment = ($width - $auto_min - $absolute_used) / count($auto);
                }

                
                foreach (array_keys($columns) as $i) {
                    if ($columns[$i]["absolute"] > 0 && count($auto)) {
                        $cellmap->set_column_width($i, $columns[$i]["min-width"]);
                    } else if (count($auto)) {
                        $cellmap->set_column_width($i, $columns[$i]["min-width"] + $increment);
                    } else {
                        
                        $increment = ($width - $absolute_used) * $columns[$i]["absolute"] / $absolute_used;

                        $cellmap->set_column_width($i, $columns[$i]["min-width"] + $increment);
                    }

                }
                return;
            }

            
            if ($absolute_used == 0 && $percent_used > 0) {
                $scale = null;
                $remaining = null;

                
                
                if ($percent_used > 100 || count($auto) == 0) {
                    $scale = 100 / $percent_used;
                } else {
                    $scale = 1;
                }

                
                $used_width = $auto_min;

                foreach ($percent as $i) {
                    $columns[$i]["percent"] *= $scale;

                    $slack = $width - $used_width;

                    $w = min($columns[$i]["percent"] * $width / 100, $slack);

                    if ($w < $columns[$i]["min-width"]) {
                        $w = $columns[$i]["min-width"];
                    }

                    $cellmap->set_column_width($i, $w);
                    $used_width += $w;

                }

                
                
                if (count($auto) > 0) {
                    $increment = ($width - $used_width) / count($auto);

                    foreach ($auto as $i) {
                        $cellmap->set_column_width($i, $columns[$i]["min-width"] + $increment);
                    }

                }
                return;
            }

            

            
            if ($absolute_used > 0 && $percent_used > 0) {
                $used_width = $auto_min;

                foreach ($absolute as $i) {
                    $cellmap->set_column_width($i, $columns[$i]["min-width"]);
                    $used_width += $columns[$i]["min-width"];
                }

                
                
                if ($percent_used > 100 || count($auto) == 0) {
                    $scale = 100 / $percent_used;
                } else {
                    $scale = 1;
                }

                $remaining_width = $width - $used_width;

                foreach ($percent as $i) {
                    $slack = $remaining_width - $used_width;

                    $columns[$i]["percent"] *= $scale;
                    $w = min($columns[$i]["percent"] * $remaining_width / 100, $slack);

                    if ($w < $columns[$i]["min-width"]) {
                        $w = $columns[$i]["min-width"];
                    }

                    $columns[$i]["used-width"] = $w;
                    $used_width += $w;
                }

                if (count($auto) > 0) {
                    $increment = ($width - $used_width) / count($auto);

                    foreach ($auto as $i) {
                        $cellmap->set_column_width($i, $columns[$i]["min-width"] + $increment);
                    }
                }

                return;
            }
        } else { 
            
            foreach (array_keys($columns) as $i) {
                $cellmap->set_column_width($i, $columns[$i]["min-width"]);
            }
        }
    }

    
    protected function _calculate_height()
    {
        $style = $this->_frame->get_style();
        $height = $style->height;

        $cellmap = $this->_frame->get_cellmap();
        $cellmap->assign_frame_heights();
        $rows = $cellmap->get_rows();

        
        $content_height = 0;
        foreach ($rows as $r) {
            $content_height += $r["height"];
        }

        $cb = $this->_frame->get_containing_block();

        if (!($style->overflow === "visible" ||
            ($style->overflow === "hidden" && $height === "auto"))
        ) {
            

            $min_height = $style->min_height;
            $max_height = $style->max_height;

            if (isset($cb["h"])) {
                $min_height = $style->length_in_pt($min_height, $cb["h"]);
                $max_height = $style->length_in_pt($max_height, $cb["h"]);

            } else if (isset($cb["w"])) {
                if (mb_strpos($min_height, "%") !== false) {
                    $min_height = 0;
                } else {
                    $min_height = $style->length_in_pt($min_height, $cb["w"]);
                }
                if (mb_strpos($max_height, "%") !== false) {
                    $max_height = "none";
                } else {
                    $max_height = $style->length_in_pt($max_height, $cb["w"]);
                }
            }

            if ($max_height !== "none" && $max_height !== "auto" && (float)$min_height > (float)$max_height) {
                
                list($max_height, $min_height) = [$min_height, $max_height];
            }

            if ($max_height !== "none" && $max_height !== "auto" && $height > (float)$max_height) {
                $height = $max_height;
            }

            if ($height < (float)$min_height) {
                $height = $min_height;
            }
        } else {
            
            if ($height !== "auto") {
                $height = $style->length_in_pt($height, $cb["h"]);

                if ($height <= $content_height) {
                    $height = $content_height;
                } else {
                    $cellmap->set_frame_heights($height, $content_height);
                }
            } else {
                $height = $content_height;
            }
        }

        return $height;
    }

    
    function reflow(BlockFrameDecorator $block = null)
    {
        
        $frame = $this->_frame;

        
        $page = $frame->get_root();
        $page->check_forced_page_break($frame);

        
        if ($page->is_full()) {
            return;
        }

        
        
        
        
        $page->table_reflow_start();

        
        $this->_collapse_margins();

        $frame->position();

        
        

        if (is_null($this->_state)) {
            $this->get_min_max_width();
        }

        $cb = $frame->get_containing_block();
        $style = $frame->get_style();

        
        
        
        if ($style->border_collapse === "separate") {
            list($h, $v) = $style->border_spacing;

            $v = (float)$style->length_in_pt($v) / 2;
            $h = (float)$style->length_in_pt($h) / 2;

            $style->padding_left = (float)$style->length_in_pt($style->padding_left, $cb["w"]) + $h;
            $style->padding_right = (float)$style->length_in_pt($style->padding_right, $cb["w"]) + $h;
            $style->padding_top = (float)$style->length_in_pt($style->padding_top, $cb["h"]) + $v;
            $style->padding_bottom = (float)$style->length_in_pt($style->padding_bottom, $cb["h"]) + $v;
        }

        $this->_assign_widths();

        
        $width = $style->width;
        $left = $style->margin_left;
        $right = $style->margin_right;

        $diff = (float)$cb["w"] - (float)$width;

        if ($left === "auto" && $right === "auto") {
            if ($diff < 0) {
                $left = 0;
                $right = $diff;
            } else {
                $left = $right = $diff / 2;
            }

            $style->margin_left = sprintf("%Fpt", $left);
            $style->margin_right = sprintf("%Fpt", $right);;
        } else {
            if ($left === "auto") {
                $left = (float)$style->length_in_pt($cb["w"], $cb["w"]) - (float)$style->length_in_pt($right, $cb["w"]) - (float)$style->length_in_pt($width, $cb["w"]);
            }
            if ($right === "auto") {
                $left = (float)$style->length_in_pt($left, $cb["w"]);
            }
        }

        list($x, $y) = $frame->get_position();

        
        $content_x = $x + (float)$left + (float)$style->length_in_pt([$style->padding_left,
                $style->border_left_width], $cb["w"]);
        $content_y = $y + (float)$style->length_in_pt([$style->margin_top,
                $style->border_top_width,
                $style->padding_top], $cb["h"]);

        if (isset($cb["h"])) {
            $h = $cb["h"];
        } else {
            $h = null;
        }

        $cellmap = $frame->get_cellmap();
        $col =& $cellmap->get_column(0);
        $col["x"] = $content_x;

        $row =& $cellmap->get_row(0);
        $row["y"] = $content_y;

        $cellmap->assign_x_positions();

        
        foreach ($frame->get_children() as $child) {
            
            if (!$page->in_nested_table() && $page->is_full()) {
                break;
            }

            $child->set_containing_block($content_x, $content_y, $width, $h);
            $child->reflow();

            if (!$page->in_nested_table()) {
                
                $page->check_page_break($child);
            }

        }

        
        $style->height = $this->_calculate_height();

        if ($style->border_collapse === "collapse") {
            
            $style->border_style = "none";
        }

        $page->table_reflow_end();

        
        

        if ($block && $style->float === "none" && $frame->is_in_flow()) {
            $block->add_frame_to_line($frame);
            $block->add_line();
        }
    }

    
    function get_min_max_width()
    {
        if (!is_null($this->_min_max_cache)) {
            return $this->_min_max_cache;
        }

        $style = $this->_frame->get_style();

        $this->_frame->normalise();

        
        
        $this->_frame->get_cellmap()->add_frame($this->_frame);

        
        
        $this->_state = [];
        $this->_state["min_width"] = 0;
        $this->_state["max_width"] = 0;

        $this->_state["percent_used"] = 0;
        $this->_state["absolute_used"] = 0;
        $this->_state["auto_min"] = 0;

        $this->_state["absolute"] = [];
        $this->_state["percent"] = [];
        $this->_state["auto"] = [];

        $columns =& $this->_frame->get_cellmap()->get_columns();
        foreach (array_keys($columns) as $i) {
            $this->_state["min_width"] += $columns[$i]["min-width"];
            $this->_state["max_width"] += $columns[$i]["max-width"];

            if ($columns[$i]["absolute"] > 0) {
                $this->_state["absolute"][] = $i;
                $this->_state["absolute_used"] += $columns[$i]["absolute"];
            } else if ($columns[$i]["percent"] > 0) {
                $this->_state["percent"][] = $i;
                $this->_state["percent_used"] += $columns[$i]["percent"];
            } else {
                $this->_state["auto"][] = $i;
                $this->_state["auto_min"] += $columns[$i]["min-width"];
            }
        }

        
        $dims = [$style->border_left_width,
            $style->border_right_width,
            $style->padding_left,
            $style->padding_right,
            $style->margin_left,
            $style->margin_right];

        if ($style->border_collapse !== "collapse") {
            list($dims[]) = $style->border_spacing;
        }

        $delta = (float)$style->length_in_pt($dims, $this->_frame->get_containing_block("w"));

        $this->_state["min_width"] += $delta;
        $this->_state["max_width"] += $delta;

        return $this->_min_max_cache = [
            $this->_state["min_width"],
            $this->_state["max_width"],
            "min" => $this->_state["min_width"],
            "max" => $this->_state["max_width"],
        ];
    }
}
