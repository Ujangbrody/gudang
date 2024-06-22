<?php

namespace Dompdf\Renderer;

use Dompdf\Frame;
use Dompdf\Helpers;


class Inline extends AbstractRenderer
{
    function render(Frame $frame)
    {
        if (!$frame->get_first_child()) {
            return; 
        }

        $style = $frame->get_style();
        $dompdf = $this->_dompdf;

        
        $bp = $style->get_border_properties();
        $widths = [
            (float)$style->length_in_pt($bp["top"]["width"]),
            (float)$style->length_in_pt($bp["right"]["width"]),
            (float)$style->length_in_pt($bp["bottom"]["width"]),
            (float)$style->length_in_pt($bp["left"]["width"])
        ];

        
        
        list($x, $y) = $frame->get_first_child()->get_position();

        $this->_set_opacity($frame->get_opacity($style->opacity));

        $do_debug_layout_line = $dompdf->getOptions()->getDebugLayout()
            && $dompdf->getOptions()->getDebugLayoutInline();

        list($w, $h) = $this->get_child_size($frame, $do_debug_layout_line);

        
        $left_margin = (float)$style->length_in_pt($style->margin_left);
        $x += $left_margin;

        
        if (($bg = $style->background_color) !== "transparent") {
            $this->_canvas->filled_rectangle($x + $widths[3], $y + $widths[0], $w, $h, $bg);
        }

        
        
        
        
        
        
        
        
        if (($url = $style->background_image) && $url !== "none") {
            $this->_background_image($url, $x + $widths[3], $y + $widths[0], $w, $h, $style);
        }

        
        $w += (float)$widths[1] + (float)$widths[3];
        $h += (float)$widths[0] + (float)$widths[2];

        
        if ($bp["left"]["style"] !== "none" && $bp["left"]["color"] !== "transparent" && $widths[3] > 0) {
            $method = "_border_" . $bp["left"]["style"];
            $this->$method($x, $y, $h, $bp["left"]["color"], $widths, "left");
        }

        
        if ($bp["top"]["style"] !== "none" && $bp["top"]["color"] !== "transparent" && $widths[0] > 0) {
            $method = "_border_" . $bp["top"]["style"];
            $this->$method($x, $y, $w, $bp["top"]["color"], $widths, "top");
        }

        if ($bp["bottom"]["style"] !== "none" && $bp["bottom"]["color"] !== "transparent" && $widths[2] > 0) {
            $method = "_border_" . $bp["bottom"]["style"];
            $this->$method($x, $y + $h, $w, $bp["bottom"]["color"], $widths, "bottom");
        }

        
        
        
        if ($bp["right"]["style"] !== "none" && $bp["right"]["color"] !== "transparent" && $widths[1] > 0) {
            $method = "_border_" . $bp["right"]["style"];
            $this->$method($x + $w, $y, $h, $bp["right"]["color"], $widths, "right");
        }

        $node = $frame->get_node();
        $id = $node->getAttribute("id");
        if (strlen($id) > 0)  {
            $this->_canvas->add_named_dest($id);
        }

        
        $is_link_node = $node->nodeName === "a";
        if ($is_link_node) {
            if (($name = $node->getAttribute("name"))) {
                $this->_canvas->add_named_dest($name);
            }
        }

        if ($frame->get_parent() && $frame->get_parent()->get_node()->nodeName === "a") {
            $link_node = $frame->get_parent()->get_node();
        }

        
        if ($is_link_node) {
            if ($href = $node->getAttribute("href")) {
                $href = Helpers::build_url($dompdf->getProtocol(), $dompdf->getBaseHost(), $dompdf->getBasePath(), $href);
                $this->_canvas->add_link($href, $x, $y, $w, $h);
            }
        }
    }

    protected function get_child_size(Frame $frame, bool $do_debug_layout_line): array {
        $w = 0.0;
        $h = 0.0;

        foreach ($frame->get_children() as $child) {
            if ($child->get_node()->nodeValue === ' ' && $child->get_prev_sibling() && !$child->get_next_sibling()) {
                break;
            }
            list($child_x, $child_y, $child_w, $child_h) = $child->get_padding_box();

            $child_h2 = 0.0;

            if ($child_w === 'auto') {
                list($child_w, $child_h2) = $this->get_child_size($child, $do_debug_layout_line);
                $w += (float)$child_w;
            } else {
                $w += (float)$child_w;
            }

            if ($child_h === 'auto') {
                list($child_w, $child_h2) = $this->get_child_size($child, $do_debug_layout_line);
            }

            $h = max($h, (float)$child_h, (float)$child_h2);
            if ($do_debug_layout_line) {
                $debug_border_box = $child->get_border_box();
                $this->_debug_layout([$debug_border_box['x'], $debug_border_box['y'], (float)$debug_border_box['w'], (float)$debug_border_box['h']], "blue");
                if ($this->_dompdf->getOptions()->getDebugLayoutPaddingBox()) {
                    $debug_padding_box = $child->get_padding_box();
                    $this->_debug_layout([$debug_padding_box['x'], $debug_padding_box['y'], (float)$debug_padding_box['w'], (float)$debug_padding_box['h']], "blue", [0.5, 0.5]);
                }
            }
        }

        return [$w, $h];
    }
}