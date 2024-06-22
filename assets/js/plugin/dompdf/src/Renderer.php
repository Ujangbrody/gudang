<?php

namespace Dompdf;

use Dompdf\Renderer\AbstractRenderer;
use Dompdf\Renderer\Block;
use Dompdf\Renderer\Image;
use Dompdf\Renderer\ListBullet;
use Dompdf\Renderer\TableCell;
use Dompdf\Renderer\TableRowGroup;
use Dompdf\Renderer\Text;


class Renderer extends AbstractRenderer
{

    
    protected $_renderers;

    
    private $_callbacks;

    
    function new_page()
    {
        $this->_canvas->new_page();
    }

    
    public function render(Frame $frame)
    {
        global $_dompdf_debug;

        $this->_check_callbacks("begin_frame", $frame);

        if ($_dompdf_debug) {
            echo $frame;
            flush();
        }

        $style = $frame->get_style();

        if (in_array($style->visibility, ["hidden", "collapse"])) {
            return;
        }

        $display = $style->display;

        
        if ($style->transform && is_array($style->transform)) {
            $this->_canvas->save();
            list($x, $y) = $frame->get_padding_box();
            $origin = $style->transform_origin;

            foreach ($style->transform as $transform) {
                list($function, $values) = $transform;
                if ($function === "matrix") {
                    $function = "transform";
                }

                $values = array_map("floatval", $values);
                $values[] = $x + (float)$style->length_in_pt($origin[0], (float)$style->length_in_pt($style->width));
                $values[] = $y + (float)$style->length_in_pt($origin[1], (float)$style->length_in_pt($style->height));

                call_user_func_array([$this->_canvas, $function], $values);
            }
        }

        switch ($display) {

            case "block":
            case "list-item":
            case "inline-block":
            case "table":
            case "inline-table":
                $this->_render_frame("block", $frame);
                break;

            case "inline":
                if ($frame->is_text_node()) {
                    $this->_render_frame("text", $frame);
                } else {
                    $this->_render_frame("inline", $frame);
                }
                break;

            case "table-cell":
                $this->_render_frame("table-cell", $frame);
                break;

            case "table-row-group":
            case "table-header-group":
            case "table-footer-group":
                $this->_render_frame("table-row-group", $frame);
                break;

            case "-dompdf-list-bullet":
                $this->_render_frame("list-bullet", $frame);
                break;

            case "-dompdf-image":
                $this->_render_frame("image", $frame);
                break;

            case "none":
                $node = $frame->get_node();

                if ($node->nodeName === "script") {
                    if ($node->getAttribute("type") === "text/php" ||
                        $node->getAttribute("language") === "php"
                    ) {
                        
                        $this->_render_frame("php", $frame);
                    } elseif ($node->getAttribute("type") === "text/javascript" ||
                        $node->getAttribute("language") === "javascript"
                    ) {
                        
                        $this->_render_frame("javascript", $frame);
                    }
                }

                
                return;

            default:
                break;

        }

        
        if ($style->overflow === "hidden") {
            list($x, $y, $w, $h) = $frame->get_padding_box();

            
            $style = $frame->get_style();
            list($tl, $tr, $br, $bl) = $style->get_computed_border_radius($w, $h);

            if ($tl + $tr + $br + $bl > 0) {
                $this->_canvas->clipping_roundrectangle($x, $y, (float)$w, (float)$h, $tl, $tr, $br, $bl);
            } else {
                $this->_canvas->clipping_rectangle($x, $y, (float)$w, (float)$h);
            }
        }

        $stack = [];

        foreach ($frame->get_children() as $child) {
            
            
            
            
            $child_style = $child->get_style();
            $child_z_index = $child_style->z_index;
            $z_index = 0;

            if ($child_z_index !== "auto") {
                $z_index = intval($child_z_index) + 1;
            } elseif ($child_style->float !== "none" || $child->is_positionned()) {
                $z_index = 1;
            }

            $stack[$z_index][] = $child;
        }

        ksort($stack);

        foreach ($stack as $by_index) {
            foreach ($by_index as $child) {
                $this->render($child);
            }
        }

        
        if ($style->overflow === "hidden") {
            $this->_canvas->clipping_end();
        }

        if ($style->transform && is_array($style->transform)) {
            $this->_canvas->restore();
        }

        
        $this->_check_callbacks("end_frame", $frame);
    }

    
    protected function _check_callbacks($event, $frame)
    {
        if (!isset($this->_callbacks)) {
            $this->_callbacks = $this->_dompdf->getCallbacks();
        }

        if (is_array($this->_callbacks) && isset($this->_callbacks[$event])) {
            $info = [0 => $this->_canvas, "canvas" => $this->_canvas,
                1 => $frame, "frame" => $frame];
            $fs = $this->_callbacks[$event];
            foreach ($fs as $f) {
                if (is_callable($f)) {
                    if (is_array($f)) {
                        $f[0]->{$f[1]}($info);
                    } else {
                        $f($info);
                    }
                }
            }
        }
    }

    
    protected function _render_frame($type, $frame)
    {

        if (!isset($this->_renderers[$type])) {

            switch ($type) {
                case "block":
                    $this->_renderers[$type] = new Block($this->_dompdf);
                    break;

                case "inline":
                    $this->_renderers[$type] = new Renderer\Inline($this->_dompdf);
                    break;

                case "text":
                    $this->_renderers[$type] = new Text($this->_dompdf);
                    break;

                case "image":
                    $this->_renderers[$type] = new Image($this->_dompdf);
                    break;

                case "table-cell":
                    $this->_renderers[$type] = new TableCell($this->_dompdf);
                    break;

                case "table-row-group":
                    $this->_renderers[$type] = new TableRowGroup($this->_dompdf);
                    break;

                case "list-bullet":
                    $this->_renderers[$type] = new ListBullet($this->_dompdf);
                    break;

                case "php":
                    $this->_renderers[$type] = new PhpEvaluator($this->_canvas);
                    break;

                case "javascript":
                    $this->_renderers[$type] = new JavascriptEmbedder($this->_dompdf);
                    break;

            }
        }

        $this->_renderers[$type]->render($frame);
    }
}
