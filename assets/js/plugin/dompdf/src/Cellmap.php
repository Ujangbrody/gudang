<?php

namespace Dompdf;

use Dompdf\FrameDecorator\Table as TableFrameDecorator;
use Dompdf\FrameDecorator\TableCell as TableCellFrameDecorator;


class Cellmap
{
    
    protected static $_BORDER_STYLE_SCORE = [
        "inset"  => 1,
        "groove" => 2,
        "outset" => 3,
        "ridge"  => 4,
        "dotted" => 5,
        "dashed" => 6,
        "solid"  => 7,
        "double" => 8,
        "hidden" => 9,
        "none"   => 0,
    ];

    
    protected $_table;

    
    protected $_num_rows;

    
    protected $_num_cols;

    
    protected $_cells;

    
    protected $_columns;

    
    protected $_rows;

    
    protected $_borders;

    
    protected $_frames;

    
    private $__col;

    
    private $__row;

    
    private $_columns_locked = false;

    
    private $_fixed_layout = false;

    
    public function __construct(TableFrameDecorator $table)
    {
        $this->_table = $table;
        $this->reset();
    }

    
    public function reset()
    {
        $this->_num_rows = 0;
        $this->_num_cols = 0;

        $this->_cells = [];
        $this->_frames = [];

        if (!$this->_columns_locked) {
            $this->_columns = [];
        }

        $this->_rows = [];

        $this->_borders = [];

        $this->__col = $this->__row = 0;
    }

    
    public function lock_columns()
    {
        $this->_columns_locked = true;
    }

    
    public function is_columns_locked()
    {
        return $this->_columns_locked;
    }

    
    public function set_layout_fixed($fixed)
    {
        $this->_fixed_layout = $fixed;
    }

    
    public function is_layout_fixed()
    {
        return $this->_fixed_layout;
    }

    
    public function get_num_rows()
    {
        return $this->_num_rows;
    }

    
    public function get_num_cols()
    {
        return $this->_num_cols;
    }

    
    public function &get_columns()
    {
        return $this->_columns;
    }

    
    public function set_columns($columns)
    {
        $this->_columns = $columns;
    }

    
    public function &get_column($i)
    {
        if (!isset($this->_columns[$i])) {
            $this->_columns[$i] = [
                "x"          => 0,
                "min-width"  => 0,
                "max-width"  => 0,
                "used-width" => null,
                "absolute"   => 0,
                "percent"    => 0,
                "auto"       => true,
            ];
        }

        return $this->_columns[$i];
    }

    
    public function &get_rows()
    {
        return $this->_rows;
    }

    
    public function &get_row($j)
    {
        if (!isset($this->_rows[$j])) {
            $this->_rows[$j] = [
                "y"            => 0,
                "first-column" => 0,
                "height"       => null,
            ];
        }

        return $this->_rows[$j];
    }

    
    public function get_border($i, $j, $h_v, $prop = null)
    {
        if (!isset($this->_borders[$i][$j][$h_v])) {
            $this->_borders[$i][$j][$h_v] = [
                "width" => 0,
                "style" => "solid",
                "color" => "black",
            ];
        }

        if (isset($prop)) {
            return $this->_borders[$i][$j][$h_v][$prop];
        }

        return $this->_borders[$i][$j][$h_v];
    }

    
    public function get_border_properties($i, $j)
    {
        return [
            "top"    => $this->get_border($i, $j, "horizontal"),
            "right"  => $this->get_border($i, $j + 1, "vertical"),
            "bottom" => $this->get_border($i + 1, $j, "horizontal"),
            "left"   => $this->get_border($i, $j, "vertical"),
        ];
    }

    
    public function get_spanned_cells(Frame $frame)
    {
        $key = $frame->get_id();

        if (isset($this->_frames[$key])) {
            return $this->_frames[$key];
        }

        return null;
    }

    
    public function frame_exists_in_cellmap(Frame $frame)
    {
        $key = $frame->get_id();

        return isset($this->_frames[$key]);
    }

    
    public function get_frame_position(Frame $frame)
    {
        global $_dompdf_warnings;

        $key = $frame->get_id();

        if (!isset($this->_frames[$key])) {
            throw new Exception("Frame not found in cellmap");
        }

        $col = $this->_frames[$key]["columns"][0];
        $row = $this->_frames[$key]["rows"][0];

        if (!isset($this->_columns[$col])) {
            $_dompdf_warnings[] = "Frame not found in columns array.  Check your table layout for missing or extra TDs.";
            $x = 0;
        } else {
            $x = $this->_columns[$col]["x"];
        }

        if (!isset($this->_rows[$row])) {
            $_dompdf_warnings[] = "Frame not found in row array.  Check your table layout for missing or extra TDs.";
            $y = 0;
        } else {
            $y = $this->_rows[$row]["y"];
        }

        return [$x, $y, "x" => $x, "y" => $y];
    }

    
    public function get_frame_width(Frame $frame)
    {
        $key = $frame->get_id();

        if (!isset($this->_frames[$key])) {
            throw new Exception("Frame not found in cellmap");
        }

        $cols = $this->_frames[$key]["columns"];
        $w = 0;
        foreach ($cols as $i) {
            $w += $this->_columns[$i]["used-width"];
        }

        return $w;
    }

    
    public function get_frame_height(Frame $frame)
    {
        $key = $frame->get_id();

        if (!isset($this->_frames[$key])) {
            throw new Exception("Frame not found in cellmap");
        }

        $rows = $this->_frames[$key]["rows"];
        $h = 0;
        foreach ($rows as $i) {
            if (!isset($this->_rows[$i])) {
                throw new Exception("The row #$i could not be found, please file an issue in the tracker with the HTML code");
            }

            $h += $this->_rows[$i]["height"];
        }

        return $h;
    }

    
    public function set_column_width($j, $width)
    {
        if ($this->_columns_locked) {
            return;
        }

        $col =& $this->get_column($j);
        $col["used-width"] = $width;
        $next_col =& $this->get_column($j + 1);
        $next_col["x"] = $col["x"] + $width;
    }

    
    public function set_row_height($i, $height)
    {
        $row =& $this->get_row($i);
        if ($height > $row["height"]) {
            $row["height"] = $height;
        }
        $next_row =& $this->get_row($i + 1);
        $next_row["y"] = $row["y"] + $row["height"];
    }

    
    protected function _resolve_border($i, $j, $h_v, $border_spec)
    {
        $n_width = $border_spec["width"];
        $n_style = $border_spec["style"];

        if (!isset($this->_borders[$i][$j][$h_v])) {
            $this->_borders[$i][$j][$h_v] = $border_spec;

            return $this->_borders[$i][$j][$h_v]["width"];
        }

        $border = & $this->_borders[$i][$j][$h_v];

        $o_width = $border["width"];
        $o_style = $border["style"];

        if (($n_style === "hidden" ||
                $n_width > $o_width ||
                $o_style === "none")

            or

            ($o_width == $n_width &&
                in_array($n_style, self::$_BORDER_STYLE_SCORE) &&
                self::$_BORDER_STYLE_SCORE[$n_style] > self::$_BORDER_STYLE_SCORE[$o_style])
        ) {
            $border = $border_spec;
        }

        return $border["width"];
    }

    
    public function add_frame(Frame $frame)
    {
        $style = $frame->get_style();
        $display = $style->display;

        $collapse = $this->_table->get_style()->border_collapse == "collapse";

        
        if ($display === "table-row" ||
            $display === "table" ||
            $display === "inline-table" ||
            in_array($display, TableFrameDecorator::$ROW_GROUPS)
        ) {
            $start_row = $this->__row;
            foreach ($frame->get_children() as $child) {
                
                if (!($child instanceof FrameDecorator\Text) && $child->get_node()->nodeName !== 'dompdf_generated') {
                    $this->add_frame($child);
                }
            }

            if ($display === "table-row") {
                $this->add_row();
            }

            $num_rows = $this->__row - $start_row - 1;
            $key = $frame->get_id();

            
            $this->_frames[$key]["columns"] = range(0, max(0, $this->_num_cols - 1));
            $this->_frames[$key]["rows"] = range($start_row, max(0, $this->__row - 1));
            $this->_frames[$key]["frame"] = $frame;

            if ($display !== "table-row" && $collapse) {
                $bp = $style->get_border_properties();

                
                for ($i = 0; $i < $num_rows + 1; $i++) {
                    $this->_resolve_border($start_row + $i, 0, "vertical", $bp["left"]);
                    $this->_resolve_border($start_row + $i, $this->_num_cols, "vertical", $bp["right"]);
                }

                for ($j = 0; $j < $this->_num_cols; $j++) {
                    $this->_resolve_border($start_row, $j, "horizontal", $bp["top"]);
                    $this->_resolve_border($this->__row, $j, "horizontal", $bp["bottom"]);
                }
            }
            return;
        }

        $node = $frame->get_node();

        
        $colspan = $node->getAttribute("colspan");
        $rowspan = $node->getAttribute("rowspan");

        if (!$colspan) {
            $colspan = 1;
            $node->setAttribute("colspan", 1);
        }

        if (!$rowspan) {
            $rowspan = 1;
            $node->setAttribute("rowspan", 1);
        }
        $key = $frame->get_id();

        $bp = $style->get_border_properties();


        
        $max_left = $max_right = 0;

        
        $ac = $this->__col;
        while (isset($this->_cells[$this->__row][$ac])) {
            $ac++;
        }

        $this->__col = $ac;

        
        for ($i = 0; $i < $rowspan; $i++) {
            $row = $this->__row + $i;

            $this->_frames[$key]["rows"][] = $row;

            for ($j = 0; $j < $colspan; $j++) {
                $this->_cells[$row][$this->__col + $j] = $frame;
            }

            if ($collapse) {
                
                $max_left = max($max_left, $this->_resolve_border($row, $this->__col, "vertical", $bp["left"]));
                $max_right = max($max_right, $this->_resolve_border($row, $this->__col + $colspan, "vertical", $bp["right"]));
            }
        }

        $max_top = $max_bottom = 0;

        
        for ($j = 0; $j < $colspan; $j++) {
            $col = $this->__col + $j;
            $this->_frames[$key]["columns"][] = $col;

            if ($collapse) {
                
                $max_top = max($max_top, $this->_resolve_border($this->__row, $col, "horizontal", $bp["top"]));
                $max_bottom = max($max_bottom, $this->_resolve_border($this->__row + $rowspan, $col, "horizontal", $bp["bottom"]));
            }
        }

        $this->_frames[$key]["frame"] = $frame;

        
        if (!$collapse) {
            list($h, $v) = $this->_table->get_style()->border_spacing;

            
            $v = $style->length_in_pt($v);
            if (is_numeric($v)) {
                $v = $v / 2;
            }
            $h = $style->length_in_pt($h);
            if (is_numeric($h)) {
                $h = $h / 2;
            }
            $style->margin = "$v $h";

            
        } else {
            
            $style->border_left_width = $max_left / 2;
            $style->border_right_width = $max_right / 2;
            $style->border_top_width = $max_top / 2;
            $style->border_bottom_width = $max_bottom / 2;
            $style->margin = "none";
        }

        if (!$this->_columns_locked) {
            
            if ($this->_fixed_layout) {
                list($frame_min, $frame_max) = [0, 10e-10];
            } else {
                list($frame_min, $frame_max) = $frame->get_min_max_width();
            }

            $width = $style->width;

            $val = null;
            if (Helpers::is_percent($width) && $colspan === 1) {
                $var = "percent";
                $val = (float)rtrim($width, "% ") / $colspan;
            } else if ($width !== "auto" && $colspan === 1) {
                $var = "absolute";
                $val = $style->length_in_pt($frame_min);
            }

            $min = 0;
            $max = 0;
            for ($cs = 0; $cs < $colspan; $cs++) {

                
                $col =& $this->get_column($this->__col + $cs);

                
                
                
                if (isset($var) && $val > $col[$var]) {
                    $col[$var] = $val;
                    $col["auto"] = false;
                }

                $min += $col["min-width"];
                $max += $col["max-width"];
            }

            if ($frame_min > $min && $colspan === 1) {
                
                
                $inc = ($this->is_layout_fixed() ? 10e-10 : ($frame_min - $min));
                for ($c = 0; $c < $colspan; $c++) {
                    $col =& $this->get_column($this->__col + $c);
                    $col["min-width"] += $inc;
                }
            }

            if ($frame_max > $max) {
                
                $inc = ($this->is_layout_fixed() ? 10e-10 : ($frame_max - $max) / $colspan);
                for ($c = 0; $c < $colspan; $c++) {
                    $col =& $this->get_column($this->__col + $c);
                    $col["max-width"] += $inc;
                }
            }
        }

        $this->__col += $colspan;
        if ($this->__col > $this->_num_cols) {
            $this->_num_cols = $this->__col;
        }
    }

    
    public function add_row()
    {
        $this->__row++;
        $this->_num_rows++;

        
        $i = 0;
        while (isset($this->_cells[$this->__row][$i])) {
            $i++;
        }

        $this->__col = $i;
    }

    
    public function remove_row(Frame $row)
    {
        $key = $row->get_id();
        if (!isset($this->_frames[$key])) {
            return; 
        }

        $this->__row = $this->_num_rows--;

        $rows = $this->_frames[$key]["rows"];
        $columns = $this->_frames[$key]["columns"];

        
        foreach ($rows as $r) {
            foreach ($columns as $c) {
                if (isset($this->_cells[$r][$c])) {
                    $id = $this->_cells[$r][$c]->get_id();

                    $this->_cells[$r][$c] = null;
                    unset($this->_cells[$r][$c]);

                    
                    if (isset($this->_frames[$id]) && count($this->_frames[$id]["rows"]) > 1) {
                        
                        if (($row_key = array_search($r, $this->_frames[$id]["rows"])) !== false) {
                            unset($this->_frames[$id]["rows"][$row_key]);
                        }
                        continue;
                    }

                    $this->_frames[$id] = null;
                    unset($this->_frames[$id]);
                }
            }

            $this->_rows[$r] = null;
            unset($this->_rows[$r]);
        }

        $this->_frames[$key] = null;
        unset($this->_frames[$key]);
    }

    
    public function remove_row_group(Frame $group)
    {
        $key = $group->get_id();
        if (!isset($this->_frames[$key])) {
            return; 
        }

        $iter = $group->get_first_child();
        while ($iter) {
            $this->remove_row($iter);
            $iter = $iter->get_next_sibling();
        }

        $this->_frames[$key] = null;
        unset($this->_frames[$key]);
    }

    
    public function update_row_group(Frame $group, Frame $last_row)
    {
        $g_key = $group->get_id();
        $r_key = $last_row->get_id();

        $r_rows = $this->_frames[$g_key]["rows"];
        $this->_frames[$g_key]["rows"] = range($this->_frames[$g_key]["rows"][0], end($r_rows));
    }

    
    public function assign_x_positions()
    {
        
        

        if ($this->_columns_locked) {
            return;
        }

        $x = $this->_columns[0]["x"];
        foreach (array_keys($this->_columns) as $j) {
            $this->_columns[$j]["x"] = $x;
            $x += $this->_columns[$j]["used-width"];
        }
    }

    
    public function assign_frame_heights()
    {
        
        
        foreach ($this->_frames as $arr) {
            $frame = $arr["frame"];

            $h = 0;
            foreach ($arr["rows"] as $row) {
                if (!isset($this->_rows[$row])) {
                    
                    continue;
                }

                $h += $this->_rows[$row]["height"];
            }

            if ($frame instanceof TableCellFrameDecorator) {
                $frame->set_cell_height($h);
            } else {
                $frame->get_style()->height = $h;
            }
        }
    }

    
    public function set_frame_heights($table_height, $content_height)
    {
        
        foreach ($this->_frames as $arr) {
            $frame = $arr["frame"];

            $h = 0;
            foreach ($arr["rows"] as $row) {
                if (!isset($this->_rows[$row])) {
                    continue;
                }

                $h += $this->_rows[$row]["height"];
            }

            if ($content_height > 0) {
                $new_height = ($h / $content_height) * $table_height;
            } else {
                $new_height = 0;
            }

            if ($frame instanceof TableCellFrameDecorator) {
                $frame->set_cell_height($new_height);
            } else {
                $frame->get_style()->height = $new_height;
            }
        }
    }

    
    public function __toString()
    {
        $str = "";
        $str .= "Columns:<br/>";
        $str .= Helpers::pre_r($this->_columns, true);
        $str .= "Rows:<br/>";
        $str .= Helpers::pre_r($this->_rows, true);

        $str .= "Frames:<br/>";
        $arr = [];
        foreach ($this->_frames as $key => $val) {
            $arr[$key] = ["columns" => $val["columns"], "rows" => $val["rows"]];
        }

        $str .= Helpers::pre_r($arr, true);

        if (php_sapi_name() == "cli") {
            $str = strip_tags(str_replace(["<br/>", "<b>", "</b>"],
                ["\n", chr(27) . "[01;33m", chr(27) . "[0m"],
                $str));
        }

        return $str;
    }
}
