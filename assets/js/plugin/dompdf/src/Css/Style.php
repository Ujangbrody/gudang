<?php


namespace Dompdf\Css;

use Dompdf\Adapter\CPDF;
use Dompdf\Exception;
use Dompdf\FontMetrics;
use Dompdf\Frame;
use Dompdf\Helpers;


class Style
{

    const CSS_IDENTIFIER = "-?[_a-zA-Z]+[_a-zA-Z0-9-]*";
    const CSS_INTEGER = "-?\d+";

    
    static $default_font_size = 12;

    
    static $default_line_height = 1.2;

    
    static $font_size_keywords = [
        "xx-small" => 0.6, 
        "x-small" => 0.75, 
        "small" => 0.889, 
        "medium" => 1, 
        "large" => 1.2, 
        "x-large" => 1.5, 
        "xx-large" => 2.0, 
    ];

    
    static $text_align_keywords = ["left", "right", "center", "justify"];

    
    static $vertical_align_keywords = ["baseline", "bottom", "middle", "sub",
        "super", "text-bottom", "text-top", "top"];

    
    static $INLINE_TYPES = ["inline"];

    
    static $BLOCK_TYPES = ["block", "inline-block", "table-cell", "list-item"];

    
    static $POSITIONNED_TYPES = ["relative", "absolute", "fixed"];

    
    static $TABLE_TYPES = ["table", "inline-table"];

    
    static $BORDER_STYLES = ["none", "hidden", "dotted", "dashed", "solid",
        "double", "groove", "ridge", "inset", "outset"];

    
    protected static $_props_shorthand = ["background", "border",
        "border_bottom", "border_color", "border_left", "border_radius",
        "border_right", "border_style", "border_top", "border_width",
        "flex", "font", "list_style", "margin", "padding"];

    
    protected static $_defaults = null;

    
    protected static $_inherited = null;

    
    protected static $_methods_cache = [];

    
    protected $_stylesheet; 

    
    protected $_media_queries;

    
    protected $_props = [];

    
    protected $_important_props = [];

    
    protected $_props_computed = [];

    protected static $_dependency_map = [
        "border_top_style" => [
            "border_top_width"
        ],
        "border_bottom_style" => [
            "border_bottom_width"
        ],
        "border_left_style" => [
            "border_left_width"
        ],
        "border_right_style" => [
            "border_right_width"
        ],
        "direction" => [
            "text_align"
        ],
        "font_size" => [
            "background_position",
            "background_size",
            "border_top_width",
            "border_right_width",
            "border_bottom_width",
            "border_left_width",
            "line_height",
            "margin_top",
            "margin_right",
            "margin_bottom",
            "margin_left",
            "outline_width",
            "padding_top",
            "padding_right",
            "padding_bottom",
            "padding_left"
        ]
    ];

    
    protected $_prop_cache = [];

    
    protected $_parent_font_size;

    
    protected $_frame;

    
    protected $_origin = Stylesheet::ORIG_AUTHOR;

    
    
    private $_computed_bottom_spacing = null;

    
    private $_computed_border_radius = null;

    
    public $_has_border_radius = false;

    
    private $fontMetrics;

    
    public function __construct(Stylesheet $stylesheet, $origin = Stylesheet::ORIG_AUTHOR)
    {
        $this->setFontMetrics($stylesheet->getFontMetrics());

        $this->_props = [];
        $this->_important_props = [];
        $this->_stylesheet = $stylesheet;
        $this->_media_queries = [];
        $this->_origin = $origin;
        $this->_parent_font_size = null;

        if (!isset(self::$_defaults)) {

            
            $d =& self::$_defaults;

            
            $d["azimuth"] = "center";
            $d["background_attachment"] = "scroll";
            $d["background_color"] = "transparent";
            $d["background_image"] = "none";
            $d["background_image_resolution"] = "normal";
            $d["background_position"] = "0% 0%";
            $d["background_repeat"] = "repeat";
            $d["background"] = "";
            $d["border_collapse"] = "separate";
            $d["border_color"] = "";
            $d["border_spacing"] = "0";
            $d["border_style"] = "";
            $d["border_top"] = "";
            $d["border_right"] = "";
            $d["border_bottom"] = "";
            $d["border_left"] = "";
            $d["border_top_color"] = "";
            $d["border_right_color"] = "";
            $d["border_bottom_color"] = "";
            $d["border_left_color"] = "";
            $d["border_top_style"] = "none";
            $d["border_right_style"] = "none";
            $d["border_bottom_style"] = "none";
            $d["border_left_style"] = "none";
            $d["border_top_width"] = "medium";
            $d["border_right_width"] = "medium";
            $d["border_bottom_width"] = "medium";
            $d["border_left_width"] = "medium";
            $d["border_width"] = "medium";
            $d["border_bottom_left_radius"] = "";
            $d["border_bottom_right_radius"] = "";
            $d["border_top_left_radius"] = "";
            $d["border_top_right_radius"] = "";
            $d["border_radius"] = "";
            $d["border"] = "";
            $d["bottom"] = "auto";
            $d["caption_side"] = "top";
            $d["clear"] = "none";
            $d["clip"] = "auto";
            $d["color"] = "#000000";
            $d["content"] = "normal";
            $d["counter_increment"] = "none";
            $d["counter_reset"] = "none";
            $d["cue_after"] = "none";
            $d["cue_before"] = "none";
            $d["cue"] = "";
            $d["cursor"] = "auto";
            $d["direction"] = "ltr";
            $d["display"] = "inline";
            $d["elevation"] = "level";
            $d["empty_cells"] = "show";
            $d["float"] = "none";
            $d["font_family"] = $stylesheet->get_dompdf()->getOptions()->getDefaultFont();
            $d["font_size"] = "medium";
            $d["font_style"] = "normal";
            $d["font_variant"] = "normal";
            $d["font_weight"] = "normal";
            $d["font"] = "";
            $d["height"] = "auto";
            $d["image_resolution"] = "normal";
            $d["left"] = "auto";
            $d["letter_spacing"] = "normal";
            $d["line_height"] = "normal";
            $d["list_style_image"] = "none";
            $d["list_style_position"] = "outside";
            $d["list_style_type"] = "disc";
            $d["list_style"] = "";
            $d["margin_right"] = "0";
            $d["margin_left"] = "0";
            $d["margin_top"] = "0";
            $d["margin_bottom"] = "0";
            $d["margin"] = "";
            $d["max_height"] = "none";
            $d["max_width"] = "none";
            $d["min_height"] = "0";
            $d["min_width"] = "0";
            $d["orphans"] = "2";
            $d["outline_color"] = ""; 
            $d["outline_style"] = "none";
            $d["outline_width"] = "medium";
            $d["outline"] = "";
            $d["overflow"] = "visible";
            $d["padding_top"] = "0";
            $d["padding_right"] = "0";
            $d["padding_bottom"] = "0";
            $d["padding_left"] = "0";
            $d["padding"] = "";
            $d["page_break_after"] = "auto";
            $d["page_break_before"] = "auto";
            $d["page_break_inside"] = "auto";
            $d["pause_after"] = "0";
            $d["pause_before"] = "0";
            $d["pause"] = "";
            $d["pitch_range"] = "50";
            $d["pitch"] = "medium";
            $d["play_during"] = "auto";
            $d["position"] = "static";
            $d["quotes"] = "";
            $d["richness"] = "50";
            $d["right"] = "auto";
            $d["size"] = "auto"; 
            $d["speak_header"] = "once";
            $d["speak_numeral"] = "continuous";
            $d["speak_punctuation"] = "none";
            $d["speak"] = "normal";
            $d["speech_rate"] = "medium";
            $d["stress"] = "50";
            $d["table_layout"] = "auto";
            $d["text_align"] = "";
            $d["text_decoration"] = "none";
            $d["text_indent"] = "0";
            $d["text_transform"] = "none";
            $d["top"] = "auto";
            $d["unicode_bidi"] = "normal";
            $d["vertical_align"] = "baseline";
            $d["visibility"] = "visible";
            $d["voice_family"] = "";
            $d["volume"] = "medium";
            $d["white_space"] = "normal";
            $d["word_wrap"] = "normal";
            $d["widows"] = "2";
            $d["width"] = "auto";
            $d["word_spacing"] = "normal";
            $d["z_index"] = "auto";

            
            $d["opacity"] = "1.0";
            $d["background_size"] = "auto auto";
            $d["transform"] = "none";
            $d["transform_origin"] = "50% 50%";

            
            $d["src"] = "";
            $d["unicode_range"] = "";

            
            $d["_dompdf_background_image_resolution"] = &$d["background_image_resolution"];
            $d["_dompdf_image_resolution"] = &$d["image_resolution"];
            $d["_dompdf_keep"] = "";
            $d["_webkit_transform"] = &$d["transform"];
            $d["_webkit_transform_origin"] = &$d["transform_origin"];

            
            self::$_inherited = [
                "azimuth",
                "background_image_resolution",
                "border_collapse",
                "border_spacing",
                "caption_side",
                "color",
                "cursor",
                "direction",
                "elevation",
                "empty_cells",
                "font_family",
                "font_size",
                "font_style",
                "font_variant",
                "font_weight",
                "font",
                "image_resolution",
                "letter_spacing",
                "line_height",
                "list_style_image",
                "list_style_position",
                "list_style_type",
                "list_style",
                "orphans",
                "page_break_inside",
                "pitch_range",
                "pitch",
                "quotes",
                "richness",
                "speak_header",
                "speak_numeral",
                "speak_punctuation",
                "speak",
                "speech_rate",
                "stress",
                "text_align",
                "text_indent",
                "text_transform",
                "visibility",
                "voice_family",
                "volume",
                "white_space",
                "word_wrap",
                "widows",
                "word_spacing",
            ];
        }
    }

    
    function dispose()
    {
    }

    
    function set_media_queries($media_queries)
    {
        $this->_media_queries = $media_queries;
    }

    
    function get_media_queries()
    {
        return $this->_media_queries;
    }

    
    function set_frame(Frame $frame)
    {
        $this->_frame = $frame;
    }

    
    function get_frame()
    {
        return $this->_frame;
    }

    
    function set_origin($origin)
    {
        $this->_origin = $origin;
    }

    
    function get_origin()
    {
        return $this->_origin;
    }

    
    function get_stylesheet()
    {
        return $this->_stylesheet;
    }

    
    function length_in_pt($length, $ref_size = null)
    {
        static $cache = [];

        if (!isset($ref_size)) {
            $ref_size = $this->__get("font_size");
        }

        if (!is_array($length)) {
            $key = $length . "/$ref_size";
            
            if (isset($cache[$key])) {
                return $cache[$key];
            }
            $length = [$length];
        } else {
            $key = implode("@", $length) . "/$ref_size";
            if (isset($cache[$key])) {
                return $cache[$key];
            }
        }

        $ret = 0;
        foreach ($length as $l) {

            if ($l === "auto") {
                return "auto";
            }

            if ($l === "none") {
                return "none";
            }

            
            if (is_numeric($l)) {
                $ret += $l;
                continue;
            }

            if ($l === "normal") {
                $ret += (float)$ref_size;
                continue;
            }

            
            if ($l === "thin") {
                $ret += 0.5;
                continue;
            }

            if ($l === "medium") {
                $ret += 1.5;
                continue;
            }

            if ($l === "thick") {
                $ret += 2.5;
                continue;
            }

            if (($i = mb_stripos($l, "px")) !== false) {
                $dpi = $this->_stylesheet->get_dompdf()->getOptions()->getDpi();
                $ret += ((float)mb_substr($l, 0, $i) * 72) / $dpi;
                continue;
            }

            if (($i = mb_stripos($l, "pt")) !== false) {
                $ret += (float)mb_substr($l, 0, $i);
                continue;
            }

            if (($i = mb_stripos($l, "%")) !== false) {
                $ret += (float)mb_substr($l, 0, $i) / 100 * (float)$ref_size;
                continue;
            }

            if (($i = mb_stripos($l, "rem")) !== false) {
                if ($this->_stylesheet->get_dompdf()->getTree()->get_root()->get_style() === null) {
                    
                    $ret += (float)mb_substr($l, 0, $i) * $this->__get("font_size");
                } else {
                    $ret += (float)mb_substr($l, 0, $i) * $this->_stylesheet->get_dompdf()->getTree()->get_root()->get_style()->font_size;
                }
                continue;
            }

            if (($i = mb_stripos($l, "em")) !== false) {
                $ret += (float)mb_substr($l, 0, $i) * $this->__get("font_size");
                continue;
            }

            if (($i = mb_stripos($l, "cm")) !== false) {
                $ret += (float)mb_substr($l, 0, $i) * 72 / 2.54;
                continue;
            }

            if (($i = mb_stripos($l, "mm")) !== false) {
                $ret += (float)mb_substr($l, 0, $i) * 72 / 25.4;
                continue;
            }

            
            if (($i = mb_stripos($l, "ex")) !== false) {
                $ret += (float)mb_substr($l, 0, $i) * $this->__get("font_size") / 2;
                continue;
            }

            if (($i = mb_stripos($l, "in")) !== false) {
                $ret += (float)mb_substr($l, 0, $i) * 72;
                continue;
            }

            if (($i = mb_stripos($l, "pc")) !== false) {
                $ret += (float)mb_substr($l, 0, $i) * 12;
                continue;
            }

            
            $ret += (float)$ref_size;
        }

        return $cache[$key] = $ret;
    }


    
    function inherit(Style $parent)
    {
        
        if ($this->_parent_font_size !== $parent->font_size) {
            $this->_parent_font_size = $parent->font_size;
            if (isset($this->_props["font_size"])) {
                $this->__set("font_size", $this->_props["font_size"]);
            }
        }

        foreach (self::$_inherited as $prop) {
            
            if (in_array($prop, self::$_props_shorthand) === true) {
                continue;
            }

            
            

            if (isset($parent->_props_computed[$prop]) &&
                (
                    !isset($this->_props[$prop])
                    || (isset($parent->_important_props[$prop]) && !isset($this->_important_props[$prop]))
                )
            ) {
                if (isset($parent->_important_props[$prop])) {
                    $this->_important_props[$prop] = true;
                }
                if (isset($parent->_props_computed[$prop])) {
                    $this->__set($prop, $parent->_props_computed[$prop]);
                } else {
                    
                    $this->__set($prop, self::$_defaults[$prop]);
                }
            }
        }

        foreach ($this->_props as $prop => $value) {
            
            if (in_array($prop, self::$_props_shorthand) === true) {
                continue;
            }
            if ($value === "inherit") {
                if (isset($parent->_important_props[$prop])) {
                    $this->_important_props[$prop] = true;
                }
                
                
                
                
                
                
                
                
                
                
                
                if (isset($parent->_props_computed[$prop])) {
                    $this->__set($prop, $parent->_props_computed[$prop]);
                } else {
                    
                    $this->__set($prop, self::$_defaults[$prop]);
                }
                
                $this->_props[$prop] = "inherit";
            }
        }

        return $this;
    }

    
    function merge(Style $style)
    {
        
        
        
        foreach ($style->_props as $prop => $val) {
            $can_merge = false;
            if (isset($style->_important_props[$prop])) {
                $this->_important_props[$prop] = true;
                $can_merge = true;
            } else if (isset($val) && !isset($this->_important_props[$prop])) {
                $can_merge = true;
            }

            if ($can_merge) {
                
                $shorthands = array_filter(self::$_props_shorthand, function ($el) use ($prop) {
                    return (strpos($prop, $el . "_") !== false);
                });
                foreach ($shorthands as $shorthand) {
                    if (array_key_exists($shorthand, $this->_props) && $this->_props[$shorthand] === "inherit") {
                        unset($this->_props[$shorthand]);
                        unset($this->_props_computed[$shorthand]);
                        unset($this->_prop_cache[$shorthand]);
                    }
                }
                
                if (($prop === "background_image" || $prop === "list_style_image") && isset($style->_props_computed[$prop])) {
                    $this->__set($prop, $style->_props_computed[$prop]);
                } else {
                    
                    $this->__set($prop, $val);
                }
            }
        }
    }

    
    function munge_color($color)
    {
        return Color::parse($color);
    }

    
    function important_set($prop)
    {
        $prop = str_replace("-", "_", $prop);
        $this->_important_props[$prop] = true;
    }

    
    function important_get($prop)
    {
        return isset($this->_important_props[$prop]);
    }

    
    function __set($prop, $val)
    {
        $prop = str_replace("-", "_", $prop);

        if (!isset(self::$_defaults[$prop])) {
            global $_dompdf_warnings;
            $_dompdf_warnings[] = "'$prop' is not a recognized CSS property.";
            return;
        }

        if ($prop !== "content" && is_string($val) && strlen($val) > 5 && mb_strpos($val, "url") === false) {
            $val = mb_strtolower(trim(str_replace(["\n", "\t"], [" "], $val)));
            $val = preg_replace("/([0-9]+) (pt|px|pc|em|ex|in|cm|mm|%)/S", "\\1\\2", $val);
        }

        $this->_props[$prop] = $val;
        $this->_props_computed[$prop] = null;
        $this->_prop_cache[$prop] = null;

        $method = "set_$prop";

        if (!isset(self::$_methods_cache[$method])) {
            self::$_methods_cache[$method] = method_exists($this, $method);
        }

        if (self::$_methods_cache[$method]) {
            $this->$method($val);
        }
        if (isset($this->_props_computed[$prop]) === false && isset($val) && $val !== '' && $val !== 'inherit') {
            $this->_props_computed[$prop] = $val;
        }

        if (isset($this->_props_computed[$prop])) {
            
            if (array_key_exists($prop, self::$_dependency_map)) {
                foreach (self::$_dependency_map[$prop] as $dependent) {
                    if (isset($this->_props[$dependent]) === true) {
                        $this->__set($dependent, $this->_props[$dependent]);
                    }
                }
            }
        }
    }

    
    function __get($prop)
    {
        
        if (!isset(self::$_defaults[$prop])) {
            throw new Exception("'$prop' is not a recognized CSS property.");
        }

        if (isset($this->_prop_cache[$prop])) {
            return $this->_prop_cache[$prop];
        }

        $method = "get_$prop";

        $retval = null;

        
        
        $reset_value = false;
        $specified_value = null;
        $computed_value = null;
        if (!isset($this->_prop_cache[$prop]) && !isset($this->_props_computed[$prop])) {
            $reset_value = true;
            if (isset($this->_props[$prop])) {
                $specified_value = $this->_props[$prop];
            }
            if (isset($this->_props_computed[$prop])) {
                $computed_value = $this->_props_computed[$prop];
            }
            if (empty($this->_props[$prop]) || $this->_props[$prop] === "inherit") {
                $this->__set($prop, self::$_defaults[$prop]);
            }
            if (empty($this->_props_computed[$prop])) {
                
                $this->__set($prop, $this->_props[$prop]);
            }
        }

        if (!isset(self::$_methods_cache[$method])) {
            self::$_methods_cache[$method] = method_exists($this, $method);
        }

        if (self::$_methods_cache[$method]) {
            $retval = $this->_prop_cache[$prop] = $this->$method();
        }

        if (!isset($retval)) {
            $retval = $this->_prop_cache[$prop] = $this->_props_computed[$prop];
        }

        
        
        if ($reset_value) {
            $this->_props[$prop] = $specified_value;
            $this->_props_computed[$prop] = $computed_value;
        }

        return $retval;
    }

    
    function set_prop($prop, $val)
    {
        $prop = str_replace("-", "_", $prop);

        if (!isset(self::$_defaults[$prop])) {
            global $_dompdf_warnings;
            $_dompdf_warnings[] = "'$prop' is not a recognized CSS property.";
            return;
        }

        if ($prop !== "content" && is_string($val) && strlen($val) > 5 && mb_strpos($val, "url") === false) {
            $val = mb_strtolower(trim(str_replace(["\n", "\t"], [" "], $val)));
            $val = preg_replace("/([0-9]+) (pt|px|pc|em|ex|in|cm|mm|%)/S", "\\1\\2", $val);
        }

        $this->_props[$prop] = $val;
        $this->_props_computed[$prop] = null;
        $this->_prop_cache[$prop] = null;

        
    }

    
    function get_prop($prop)
    {
        if (!isset(self::$_defaults[$prop])) {
            throw new Exception("'$prop' is not a recognized CSS property.");
        }

        $method = "get_$prop";

        
        if (!isset($this->_props_computed[$prop])) {
            return self::$_defaults[$prop];
        }

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->_props[$prop];
    }

    
    function compute_props()
    {
        foreach ($this->_props as $prop => $val) {
            if (in_array($prop, self::$_props_shorthand) === false) {
                $this->__set($prop, $val);
            }
        }
    }

    
    function computed_bottom_spacing()
    {
        if ($this->_computed_bottom_spacing !== null) {
            return $this->_computed_bottom_spacing;
        }
        return $this->_computed_bottom_spacing = $this->length_in_pt(
            [
                $this->margin_bottom,
                $this->padding_bottom,
                $this->border_bottom_width
            ]
        );
    }

    
    function get_font_family_raw()
    {
        return trim($this->_props["font_family"], " \t\n\r\x0B\"'");
    }

    
    function get_font_family()
    {
        

        $DEBUGCSS = $this->_stylesheet->get_dompdf()->getOptions()->getDebugCss();

        
        

        
        $weight = $this->__get("font_weight");
        if ($weight === 'bold') {
            $weight = 700;
        } elseif (preg_match('/^[0-9]+$/', $weight, $match)) {
            $weight = (int)$match[0];
        } else {
            $weight = 400;
        }

        
        $font_style = $this->__get("font_style");
        $subtype = $this->getFontMetrics()->getType($weight . ' ' . $font_style);

        $families = preg_split("/\s*,\s*/", $this->_props_computed["font_family"]);

        $font = null;
        foreach ($families as $family) {
            
            
            $family = trim($family, " \t\n\r\x0B\"'");
            if ($DEBUGCSS) {
                print '(' . $family . ')';
            }
            $font = $this->getFontMetrics()->getFont($family, $subtype);

            if ($font) {
                if ($DEBUGCSS) {
                    print "<pre>[get_font_family:";
                    print '(' . $this->_props_computed["font_family"] . '.' . $font_style . '.' . $weight . '.' . $subtype . ')';
                    print '(' . $font . ")get_font_family]\n</pre>";
                }
                return $font;
            }
        }

        $family = null;
        if ($DEBUGCSS) {
            print '(default)';
        }
        $font = $this->getFontMetrics()->getFont($family, $subtype);

        if ($font) {
            if ($DEBUGCSS) {
                print '(' . $font . ")get_font_family]\n</pre>";
            }
            return $font;
        }

        throw new Exception("Unable to find a suitable font replacement for: '" . $this->_props_computed["font_family"] . "'");
    }

    
    function get_word_spacing()
    {
        $word_spacing = $this->_props_computed["word_spacing"];

        if ($word_spacing === "normal") {
            return 0;
        }

        if (strpos($word_spacing, "%") !== false) {
            return $word_spacing;
        }

        return (float)$this->length_in_pt($word_spacing, $this->__get("font_size"));
    }

    
    function get_letter_spacing()
    {
        $letter_spacing = $this->_props_computed["letter_spacing"];

        if ($letter_spacing === "normal") {
            return 0;
        }

        return (float)$this->length_in_pt($letter_spacing, $this->__get("font_size"));
    }

    
    function get_line_height()
    {
        $line_height = $this->_props_computed["line_height"];

        if ($line_height === "normal") {
            return self::$default_line_height * $this->__get("font_size");
        }

        if (is_numeric($line_height)) {
            return $line_height * $this->__get("font_size");
        }

        return (float)$this->length_in_pt($line_height, $this->__get("font_size"));
    }

    
    function get_color()
    {
        return $this->munge_color($this->_props_computed["color"]);
    }

    
    function get_background_color()
    {
        return $this->munge_color($this->_props_computed["background_color"]);
    }

    
    function get_background_image()
    {
        return $this->_image($this->_props_computed["background_image"]);
    }

    
    function get_background_position()
    {
        if (strpos($this->_props_computed["background_position"], " ") === false) {
            $this->__set("background_position", $this->_props["background_position"]);
        }
        $tmp = explode(" ", $this->_props_computed["background_position"]);

        return [
            0 => $tmp[0], "x" => $tmp[0],
            1 => $tmp[1], "y" => $tmp[1],
        ];
    }


    
    function get_background_size()
    {
        switch ($this->_props_computed["background_size"]) {
            case "cover":
                return "cover";
            case "contain":
                return "contain";
            default:
                break;
        }

        if (strpos($this->_props_computed["background_size"], " ") === false) {
            $this->__set("background_size", $this->_props["background_size"]);
        }
        $result = explode(" ", $this->_props_computed["background_size"]);
        return [$result[0], $result[1]];
    }

    
    function get_border_top_color()
    {
        return $this->munge_color($this->_props_computed["border_top_color"]);
    }

    
    function get_border_right_color()
    {
        return $this->munge_color($this->_props_computed["border_right_color"]);
    }

    
    function get_border_bottom_color()
    {
        return $this->munge_color($this->_props_computed["border_bottom_color"]);
    }

    
    function get_border_left_color()
    {
        return $this->munge_color($this->_props_computed["border_left_color"]);
    }

    

    
    function get_border_properties()
    {
        return [
            "top" => [
                "width" => $this->__get("border_top_width"),
                "style" => $this->__get("border_top_style"),
                "color" => $this->__get("border_top_color"),
            ],
            "bottom" => [
                "width" => $this->__get("border_bottom_width"),
                "style" => $this->__get("border_bottom_style"),
                "color" => $this->__get("border_bottom_color"),
            ],
            "right" => [
                "width" => $this->__get("border_right_width"),
                "style" => $this->__get("border_right_style"),
                "color" => $this->__get("border_right_color"),
            ],
            "left" => [
                "width" => $this->__get("border_left_width"),
                "style" => $this->__get("border_left_style"),
                "color" => $this->__get("border_left_color"),
            ],
        ];
    }

    
    protected function _get_border($side)
    {
        $color = $this->__get("border_" . $side . "_color");

        return $this->__get("border_" . $side . "_width") . " " .
            $this->__get("border_" . $side . "_style") . " " . $color["hex"];
    }

    
    function get_border_top()
    {
        return $this->_get_border("top");
    }

    
    function get_border_right()
    {
        return $this->_get_border("right");
    }

    
    function get_border_bottom()
    {
        return $this->_get_border("bottom");
    }

    
    function get_border_left()
    {
        return $this->_get_border("left");
    }

    private function _get_width($prop)
    {
        
        if (strpos($this->_props_computed[$prop], "%") !== false) {
            
            return $this->_props_computed[$prop];
        }
        return $this->length_in_pt($this->_props_computed[$prop], $this->__get("font_size"));
    }

    function get_margin_top()
    {
        return $this->_get_width("margin_top");
    }

    function get_margin_right()
    {
        return $this->_get_width("margin_right");
    }

    function get_margin_bottom()
    {
        return $this->_get_width("margin_bottom");
    }

    function get_margin_left()
    {
        return $this->_get_width("margin_left");
    }

    function get_padding_top()
    {
        return $this->_get_width("padding_top");
    }

    function get_padding_right()
    {
        return $this->_get_width("padding_right");
    }

    function get_padding_bottom()
    {
        return $this->_get_width("padding_bottom");
    }

    function get_padding_left()
    {
        return $this->_get_width("padding_left");
    }

    
    function get_computed_border_radius($w, $h)
    {
        if (!empty($this->_computed_border_radius)) {
            return $this->_computed_border_radius;
        }

        $w = (float)$w;
        $h = (float)$h;
        $rTL = (float)$this->__get("border_top_left_radius");
        $rTR = (float)$this->__get("border_top_right_radius");
        $rBL = (float)$this->__get("border_bottom_left_radius");
        $rBR = (float)$this->__get("border_bottom_right_radius");

        if ($rTL + $rTR + $rBL + $rBR == 0) {
            return $this->_computed_border_radius = [
                0, 0, 0, 0,
                "top-left" => 0,
                "top-right" => 0,
                "bottom-right" => 0,
                "bottom-left" => 0,
            ];
        }

        $t = (float)$this->__get("border_top_width");
        $r = (float)$this->__get("border_right_width");
        $b = (float)$this->__get("border_bottom_width");
        $l = (float)$this->__get("border_left_width");

        $rTL = min($rTL, $h - $rBL - $t / 2 - $b / 2, $w - $rTR - $l / 2 - $r / 2);
        $rTR = min($rTR, $h - $rBR - $t / 2 - $b / 2, $w - $rTL - $l / 2 - $r / 2);
        $rBL = min($rBL, $h - $rTL - $t / 2 - $b / 2, $w - $rBR - $l / 2 - $r / 2);
        $rBR = min($rBR, $h - $rTR - $t / 2 - $b / 2, $w - $rBL - $l / 2 - $r / 2);

        return $this->_computed_border_radius = [
            $rTL, $rTR, $rBR, $rBL,
            "top-left" => $rTL,
            "top-right" => $rTR,
            "bottom-right" => $rBR,
            "bottom-left" => $rBL,
        ];
    }

    
    function get_outline_color()
    {
        return $this->munge_color($this->_props_computed["outline_color"]);
    }

    
    function get_outline_width()
    {
        $style = $this->__get("outline_style");
        return $style !== "none" && $style !== "hidden" ? $this->length_in_pt($this->_props_computed["outline_width"]) : 0;
    }

    
    function get_outline()
    {
        $color = $this->__get("outline_color");
        return
            $this->__get("outline_width") . " " .
            $this->__get("outline_style") . " " .
            $color["hex"];
    }
    

    
    function get_border_spacing()
    {
        $arr = explode(" ", $this->_props_computed["border_spacing"]);
        if (count($arr) == 1) {
            $arr[1] = $arr[0];
        }
        return $arr;
    }

    
    function get_list_style_image()
    {
        return $this->_image($this->_props_computed["list_style_image"]);
    }

    
    function get_counter_increment()
    {
        $val = trim($this->_props_computed["counter_increment"]);
        $value = null;

        if (in_array($val, ["none", "inherit"])) {
            $value = $val;
        } else {
            if (preg_match_all("/(" . self::CSS_IDENTIFIER . ")(?:\s+(" . self::CSS_INTEGER . "))?/", $val, $matches, PREG_SET_ORDER)) {
                $value = [];
                foreach ($matches as $match) {
                    $value[$match[1]] = isset($match[2]) ? $match[2] : 1;
                }
            }
        }
        return $value;
    }


    

    

    
    protected function _set_style_side_type($style, $side, $type, $val, $important)
    {
        $prop = $style;
        if (!empty($side)) {
            $prop .= "_" . $side;
        };
        if (!empty($type)) {
            $prop .= "_" . $type;
        };
        $this->_props[$prop] = $val;
        $this->_prop_cache[$prop] = null;

        if ($val === "inherit") {
            $this->_props_computed[$prop] = null;
            return;
        }

        if (!isset($this->_important_props[$prop]) || $important) {
            $val_computed = (float)$this->length_in_pt($val);
            if ($side === "bottom") {
                $this->_computed_bottom_spacing = null; 
            }
            if ($important) {
                $this->_important_props[$prop] = true;
            }

            if ($val_computed < 0 && ($style === "border" || $style === "padding" || $style === "outline")) {
                $this->_props[$prop] = null; 
            } else if (
                (($style === "border" || $style === "outline") && $type === "width" && strpos($val, "%") !== false)
                ||
                ($style === "padding" && strpos($val, "%") !== false)
                ||
                ($style === "margin" && (strpos($val, "%") !== false || $val === "auto"))
            ) {
                $this->_props_computed[$prop] = $val;
            } elseif (($style === "border" || $style === "outline") && $type === "width" && strpos($val, "%") === false) {
                $line_style_prop = $style;
                if (!empty($side)) {
                    $line_style_prop .= "_" . $side;
                };
                $line_style_prop .= "_style";
                $line_style = $this->__get($line_style_prop);
                $this->_props_computed[$prop] = ($line_style !== "none" && $line_style !== "hidden" ? $val_computed : 0);
            } elseif (($style === "margin" || $style === "padding")) {
                $this->_props_computed[$prop] = ($val !== "none" && $val !== "hidden" ? $val_computed : 0);
            } elseif ($type === "color") {
                $this->set_prop_color($prop, $val);
            } elseif (!empty($val)) {
                $this->_props_computed[$prop] = $val;
            }
        }
    }

    
    protected function _set_style_sides_type($style, $top, $right, $bottom, $left, $type, $important)
    {
        $this->_set_style_side_type($style, 'top', $type, $top, $important);
        $this->_set_style_side_type($style, 'right', $type, $right, $important);
        $this->_set_style_side_type($style, 'bottom', $type, $bottom, $important);
        $this->_set_style_side_type($style, 'left', $type, $left, $important);
    }

    
    protected function _set_style_type($style, $type, $val, $important)
    {
        $val = preg_replace("/\s*\,\s*/", ",", $val); 
        $arr = explode(" ", $val);

        switch (count($arr)) {
            case 1:
                $this->_set_style_sides_type($style, $arr[0], $arr[0], $arr[0], $arr[0], $type, $important);
                break;
            case 2:
                $this->_set_style_sides_type($style, $arr[0], $arr[1], $arr[0], $arr[1], $type, $important);
                break;
            case 3:
                $this->_set_style_sides_type($style, $arr[0], $arr[1], $arr[2], $arr[1], $type, $important);
                break;
            case 4:
                $this->_set_style_sides_type($style, $arr[0], $arr[1], $arr[2], $arr[3], $type, $important);
                break;
        }
    }

    
    protected function _set_style_type_important($style, $type, $val)
    {
        $this->_set_style_type($style, $type, $val, isset($this->_important_props[$style . $type]));
    }

    
    protected function _set_style_side_width_important($style, $side, $val)
    {
        $this->_set_style_side_type($style, $side, "", $val, isset($this->_important_props[$style . $side]));
    }

    
    protected function _set_style($style, $val, $important)
    {
        if (!isset($this->_important_props[$style]) || $important) {
            if ($important) {
                $this->_important_props[$style] = true;
            }
            $this->__set($style, $val);
        }
    }

    
    protected function _image($val)
    {
        $DEBUGCSS = $this->_stylesheet->get_dompdf()->getOptions()->getDebugCss();
        $parsed_url = "none";

        if (empty($val) || $val === "none") {
            $path = "none";
        } elseif (mb_strpos($val, "url") === false) {
            $path = "none"; 
        } else {
            $val = preg_replace("/url\(\s*['\"]?([^'\")]+)['\"]?\s*\)/", "\\1", trim($val));

            
            $parsed_url = Helpers::explode_url($val);
            $path = Helpers::build_url($this->_stylesheet->get_protocol(),
                $this->_stylesheet->get_host(),
                $this->_stylesheet->get_base_path(),
                $val);
            if (($parsed_url["protocol"] == "" || $parsed_url["protocol"] == "file:
                $path = realpath($path);
                
                if (!$path) {
                    $path = 'none';
                }
            }
        }
        if ($DEBUGCSS) {
            print "<pre>[_image\n";
            print_r($parsed_url);
            print $this->_stylesheet->get_protocol() . "\n" . $this->_stylesheet->get_base_path() . "\n" . $path . "\n";
            print "_image]</pre>";;
        }
        return $path;
    }

    

    protected function set_prop_color($prop, $color)
    {
        $munged_color = $this->munge_color($color);

        if (is_null($munged_color)) {
            return;
        }

        $this->_props[$prop] = $color;
        $this->_props_computed[$prop] = null;
        $this->_prop_cache[$prop] = null;

        $this->_props_computed[$prop] = (is_array($munged_color) ? $munged_color["hex"] : $munged_color);
    }

    
    function set_color($color)
    {
        $this->set_prop_color("color", $color);
    }

    
    function set_background_color($color)
    {
        $this->set_prop_color("background_color", $color);
    }

    
    function set_background_image($val)
    {
        $this->_props["background_image"] = $val;
        $parsed_val = $this->_image($val);
        if ($parsed_val === "none") {
            $this->_props_computed["background_image"] = "none";
        } else {
            $this->_props_computed["background_image"] = "url(" . $parsed_val . ")";
        }
        $this->_prop_cache["background_image"] = null;
    }

    
    function set_background_repeat($val)
    {
        $this->_props["background_repeat"] = $val;
        $this->_props_computed["background_repeat"] = null;
        $this->_prop_cache["background_repeat"] = null;

        if ($val === 'inherit') {
            return;
        }

        $this->_props_computed["background_repeat"] = $val;
    }

    
    function set_background_attachment($val)
    {
        $this->_props["background_attachment"] = $val;
        $this->_props_computed["background_attachment"] = null;
        $this->_prop_cache["background_attachment"] = null;
        
        if ($val === 'inherit') {
            return;
        }

        $this->_props_computed["background_attachment"] = $val;
    }

    
    function set_background_position($val)
    {
        $this->_props["background_position"] = $val;

        $tmp = explode(" ", $val);

        switch ($tmp[0]) {
            case "left":
                $x = "0%";
                break;

            case "right":
                $x = "100%";
                break;

            case "top":
                $y = "0%";
                break;

            case "bottom":
                $y = "100%";
                break;

            case "center":
                $x = "50%";
                $y = "50%";
                break;

            default:
                $x = $tmp[0];
                break;
        }

        if (isset($tmp[1])) {
            switch ($tmp[1]) {
                case "left":
                    $x = "0%";
                    break;

                case "right":
                    $x = "100%";
                    break;

                case "top":
                    $y = "0%";
                    break;

                case "bottom":
                    $y = "100%";
                    break;

                case "center":
                    if ($tmp[0] === "left" || $tmp[0] === "right" || $tmp[0] === "center") {
                        $y = "50%";
                    } else {
                        $x = "50%";
                    }
                    break;

                default:
                    $y = $tmp[1];
                    break;
            }
        } else {
            $y = "50%";
        }

        if (!isset($x)) {
            $x = "0%";
        }

        if (!isset($y)) {
            $y = "0%";
        }
        
        $this->_props_computed["background_position"] = "$x $y";
        $this->_prop_cache["background_position"] = null;
    }

    
    function set_background_size($val)
    {
        $this->_props["background_size"] = $val;
        $this->_prop_cache["background_size"] = null;

        $result = explode(" ", $val);
        $width = $result[0];

        switch ($width) {
            case "cover":
            case "contain":
            case "inherit":
                $this->_props_computed["background_size"] = $width;
                return;
        }

        if ($width !== "auto" && strpos($width, "%") === false) {
            $width = (float)$this->length_in_pt($width);
        }

        $height = $result[1] ?? "auto";
        if ($height !== "auto" && strpos($height, "%") === false) {
            $height = (float)$this->length_in_pt($height);
        }

        $this->_props_computed["background_size"] = "$width $height";
    }

    
    function set_background($val)
    {
        $val = trim($val);
        $important = isset($this->_important_props["background"]);

        if ($val === "none") {
            $this->_set_style("background_image", "none", $important);
            $this->_set_style("background_color", "transparent", $important);
        } else {
            $pos = [];
            $tmp = preg_replace("/\s*\,\s*/", ",", $val); 
            $tmp = preg_split("/\s+/", $tmp);

            foreach ($tmp as $attr) {
                if (mb_substr($attr, 0, 3) === "url" || $attr === "none") {
                    $this->_set_style("background_image", $attr, $important);
                } elseif ($attr === "fixed" || $attr === "scroll") {
                    $this->_set_style("background_attachment", $attr, $important);
                } elseif ($attr === "repeat" || $attr === "repeat-x" || $attr === "repeat-y" || $attr === "no-repeat") {
                    $this->_set_style("background_repeat", $attr, $important);
                } elseif (($col = $this->munge_color($attr)) != null) {
                    $this->_set_style("background_color", is_array($col) ? $col["hex"] : $col, $important);
                } else {
                    $pos[] = $attr;
                }
            }

            if (count($pos)) {
                $this->_set_style("background_position", implode(" ", $pos), $important);
            }
        }

        
        $this->_props["background"] = $val;
        $this->_props_computed["background"] = null;
        $this->_prop_cache["background"] = null;
    }

    
    function set_font_size($size)
    {
        $this->_props["font_size"] = $size;
        $this->_props_computed["font_size"] = null;
        $this->_prop_cache["font_size"] = null;

        if ($size === "inherit") {
            return;
        }
        if (!isset($this->_parent_font_size)) {
            $this->_parent_font_size = self::$default_font_size;
        }

        switch ((string)$size) {
            case "xx-small":
            case "x-small":
            case "small":
            case "medium":
            case "large":
            case "x-large":
            case "xx-large":
                $fs = self::$default_font_size * self::$font_size_keywords[$size];
                break;

            case "smaller":
                $fs = 8 / 9 * $this->_parent_font_size;
                break;

            case "larger":
                $fs = 6 / 5 * $this->_parent_font_size;
                break;

            default:
                $fs = $size;
                break;
        }

        
        if (($i = mb_strpos($fs, "rem")) !== false) {
            if ($this->_stylesheet->get_dompdf()->getTree()->get_root()->get_style() === null) {
                
                $fs = (float)mb_substr($fs, 0, $i) * $this->_parent_font_size;
            } else {
                $fs = (float)mb_substr($fs, 0, $i) * $this->_stylesheet->get_dompdf()->getTree()->get_root()->get_style()->font_size;
            }
        } elseif (($i = mb_strpos($fs, "em")) !== false) {
            $fs = (float)mb_substr($fs, 0, $i) * $this->_parent_font_size;
        } elseif (($i = mb_strpos($fs, "ex")) !== false) {
            $fs = (float)mb_substr($fs, 0, $i) * $this->_parent_font_size / 2;
        } else {
            
            $fs = (float)$this->length_in_pt($fs, $this->_parent_font_size);
        }

        $this->_props_computed["font_size"] = $fs;
    }

    
    function set_font_weight($weight)
    {
        $this->_props["font_weight"] = $weight;
        $this->_props_computed["font_weight"] = null;
        $this->_prop_cache["font_weight"] = null;

        $computed_weight = $weight;

        if ($weight === "bolder") {
            
            $computed_weight = "bold";
        } elseif ($weight === "lighter") {
            
            $computed_weight = "normal";
        }

        $this->_props_computed["font_weight"] = $computed_weight;
    }

    
    function set_font($val)
    {
        
        $this->_prop_cache["font"] = null;
        $this->_props["font"] = $val;
        $this->_props_computed["font"] = null;

        $important = isset($this->_important_props["font"]);

        if (strtolower($val) === "inherit") {
            $this->_set_style("font_family", "inherit", $important);
            $this->_set_style("font_size", "inherit", $important);
            $this->_set_style("font_style", "inherit", $important);
            $this->_set_style("font_variant", "inherit", $important);
            $this->_set_style("font_weight", "inherit", $important);
            $this->_set_style("line_height", "inherit", $important);
            return;
        }

        if (preg_match("/^(italic|oblique|normal)\s*(.*)$/i", $val, $match)) {
            $this->_set_style("font_style", $match[1], $important);
            $val = $match[2];
        }

        if (preg_match("/^(small-caps|normal)\s*(.*)$/i", $val, $match)) {
            $this->_set_style("font_variant", $match[1], $important);
            $val = $match[2];
        }

        
        if (preg_match("/^(bold|bolder|lighter|100|200|300|400|500|600|700|800|900|normal)\s*(.*)$/i", $val, $match) &&
            !preg_match("/^(?:pt|px|pc|em|ex|in|cm|mm|%)/", $match[2])
        ) {
            $this->_set_style("font_weight", $match[1], $important);
            $val = $match[2];
        }

        if (preg_match("/^(xx-small|x-small|small|medium|large|x-large|xx-large|smaller|larger|\d+\s*(?:pt|px|pc|em|ex|in|cm|mm|%))(?:\/|\s*)(.*)$/i", $val, $match)) {
            $this->_set_style("font_size", $match[1], $important);
            $val = $match[2];
            if (preg_match("/^(?:\/|\s*)(\d+\s*(?:pt|px|pc|em|ex|in|cm|mm|%)?)\s*(.*)$/i", $val, $match)) {
                $this->_set_style("line_height", $match[1], $important);
                $val = $match[2];
            }
        }

        if (strlen($val) != 0) {
            $this->_set_style("font_family", $val, $important);
        }
    }

    
    public function set_text_align($val)
    {
        $alignment = "";
        if (in_array($val, self::$text_align_keywords)) {
            $alignment = $val;
        }
        if ($alignment === "") {
            $alignment = "left";
            if ($this->__get("direction") === "rtl") {
                $alignment = "right";
            }

        }
        $this->_props_computed["text_align"] = $alignment;
    }
    
    
    function set_word_spacing($val)
    {
        $this->_props["word_spacing"] = $val;
        $this->_props_computed["word_spacing"] = null;
        $this->_prop_cache["word_spacing"] = null;

        if ($val === 'inherit') {
            return;
        }

        if ($val === "normal" || strpos($val, "%") !== false) {
            $this->_props_computed["word_spacing"] = $val;
        } else {
            $this->_props_computed["word_spacing"] = ((float)$this->length_in_pt($val, $this->__get("font_size"))) . "pt";
        }
    }

    
    function set_letter_spacing($val)
    {
        $this->_props["letter_spacing"] = $val;
        $this->_props_computed["letter_spacing"] = null;
        $this->_prop_cache["letter_spacing"] = null;

        if ($val === 'inherit') {
            return;
        }

        if ($val === "normal") {
            $this->_props_computed["letter_spacing"] = $val;
        } else {
            $this->_props_computed["letter_spacing"] = ((float)$this->length_in_pt($val, $this->__get("font_size"))) . "pt";
        }
    }

    
    function set_line_height($val)
    {
        $this->_props["line_height"] = $val;
        $this->_props_computed["line_height"] = null;
        $this->_prop_cache["line_height"] = null;

        if ($val === 'inherit') {
            return;
        }

        if ($val === "normal" || is_numeric($val)) {
            $this->_props_computed["line_height"] = $val;
        } else {
            $this->_props_computed["line_height"] = ((float)$this->length_in_pt($val, $this->__get("font_size"))) . "pt";
        }
    }

    
    function set_page_break_before($break)
    {
        $this->_props["page_break_before"] = $break;
        $this->_props_computed["page_break_before"] = null;
        $this->_prop_cache["page_break_before"] = null;

        if ($break === 'inherit') {
            return;
        }

        if ($break === "left" || $break === "right") {
            $break = "always";
        }

        $this->_props_computed["page_break_before"] = $break;
    }

    
    function set_page_break_after($break)
    {
        $this->_props["page_break_after"] = $break;
        $this->_props_computed["page_break_after"] = null;
        $this->_prop_cache["page_break_after"] = null;

        if ($break === 'inherit') {
            return;
        }

        if ($break === "left" || $break === "right") {
            $break = "always";
        }

        $this->_props_computed["page_break_after"] = $break;
    }

    
    function set_margin_top($val)
    {
        $this->_set_style_side_width_important('margin', 'top', $val);
    }

    
    function set_margin_right($val)
    {
        $this->_set_style_side_width_important('margin', 'right', $val);
    }

    
    function set_margin_bottom($val)
    {
        $this->_set_style_side_width_important('margin', 'bottom', $val);
    }

    
    function set_margin_left($val)
    {
        $this->_set_style_side_width_important('margin', 'left', $val);
    }

    
    function set_margin($val)
    {
        $this->_set_style_type_important('margin', '', $val);
    }

    
    function set_padding_top($val)
    {
        $this->_set_style_side_width_important('padding', 'top', $val);
    }

    
    function set_padding_right($val)
    {
        $this->_set_style_side_width_important('padding', 'right', $val);
    }

    
    function set_padding_bottom($val)
    {
        $this->_set_style_side_width_important('padding', 'bottom', $val);
    }

    
    function set_padding_left($val)
    {
        $this->_set_style_side_width_important('padding', 'left', $val);
    }

    
    function set_padding($val)
    {
        $this->_set_style_type_important('padding', '', $val);
    }
    

    
    protected function _set_border($side, $border_spec, $important)
    {
        $border_spec = preg_replace("/\s*\,\s*/", ",", $border_spec);
        $arr = explode(" ", $border_spec);

        foreach ($arr as $value) {
            $value = trim($value);
            $prop = "";
            if (strtolower($value) === "inherit") {
                $this->__set("border_${side}_color", "inherit");
                $this->__set("border_${side}_style", "inherit");
                $this->__set("border_${side}_width", "inherit");
                continue;
            } elseif (in_array($value, self::$BORDER_STYLES)) {
                $prop = "border_${side}_style";
            } elseif ($value === "0" || preg_match("/[.0-9]+(?:px|pt|pc|em|ex|%|in|mm|cm)|(?:thin|medium|thick)/", $value)) {
                $prop = "border_${side}_width";
            } else {
                
                $prop = "border_${side}_color";
            }

            if ($important) {
                $this->_important_props[$prop] = true;
            }
            $this->__set($prop, $value);
        }
    }

    
    function set_border_top($val)
    {
        $this->_set_border("top", $val, isset($this->_important_props['border_top']));
    }

    function set_border_top_color($val)
    {
        $color = $val;
        if ($val === "") {
            $color = $this->__get("color");
        }
        $this->_set_style_side_type('border', 'top', 'color', $color, isset($this->_important_props['border_top_color']));
    }

    function set_border_top_style($val)
    {
        $this->_set_style_side_type('border', 'top', 'style', $val, isset($this->_important_props['border_top_style']));
    }

    function set_border_top_width($val)
    {
        $this->_set_style_side_type('border', 'top', 'width', $val, isset($this->_important_props['border_top_width']));
    }

    
    function set_border_right($val)
    {
        $this->_set_border("right", $val, isset($this->_important_props['border_right']));
    }

    function set_border_right_color($val)
    {
        $color = $val;
        if ($val === "") {
            $color = $this->__get("color");
        }
        $this->_set_style_side_type('border', 'right', 'color', $color, isset($this->_important_props['border_right_color']));
    }

    function set_border_right_style($val)
    {
        $this->_set_style_side_type('border', 'right', 'style', $val, isset($this->_important_props['border_right_style']));
    }

    function set_border_right_width($val)
    {
        $this->_set_style_side_type('border', 'right', 'width', $val, isset($this->_important_props['border_right_width']));
    }

    
    function set_border_bottom($val)
    {
        $this->_set_border("bottom", $val, isset($this->_important_props['border_bottom']));
    }

    function set_border_bottom_color($val)
    {
        $color = $val;
        if ($val === "") {
            $color = $this->__get("color");
        }
        $this->_set_style_side_type('border', 'bottom', 'color', $color, isset($this->_important_props['border_bottom_color']));
    }

    function set_border_bottom_style($val)
    {
        $this->_set_style_side_type('border', 'bottom', 'style', $val, isset($this->_important_props['border_bottom_style']));
    }

    function set_border_bottom_width($val)
    {
        $this->_set_style_side_type('border', 'bottom', 'width', $val, isset($this->_important_props['border_bottom_width']));
    }

    
    function set_border_left($val)
    {
        $this->_set_border("left", $val, isset($this->_important_props['border_left']));
    }

    function set_border_left_color($val)
    {
        $color = $val;
        if ($val === "") {
            $color = $this->__get("color");
        }
        $this->_set_style_side_type('border', 'left', 'color', $color, isset($this->_important_props['border_left_color']));
    }

    function set_border_left_style($val)
    {
        $this->_set_style_side_type('border', 'left', 'style', $val, isset($this->_important_props['border_left_style']));
    }

    function set_border_left_width($val)
    {
        $this->_set_style_side_type('border', 'left', 'width', $val, isset($this->_important_props['border_left_width']));
    }

    
    function set_border($val)
    {
        $important = isset($this->_important_props["border"]);

        $this->_set_border("top", $val, $important);
        $this->_set_border("right", $val, $important);
        $this->_set_border("bottom", $val, $important);
        $this->_set_border("left", $val, $important);
    }

    
    function set_border_width($val)
    {
        $this->_set_style_type_important('border', 'width', $val);
    }

    
    function set_border_color($val)
    {
        $this->_set_style_type_important('border', 'color', $val);
    }

    
    function set_border_style($val)
    {
        $this->_set_style_type_important('border', 'style', $val);
    }

    
    function set_border_top_left_radius($val)
    {
        $this->_set_border_radius_corner($val, "top_left");
    }

    
    function set_border_top_right_radius($val)
    {
        $this->_set_border_radius_corner($val, "top_right");
    }

    
    function set_border_bottom_left_radius($val)
    {
        $this->_set_border_radius_corner($val, "bottom_left");
    }

    
    function set_border_bottom_right_radius($val)
    {
        $this->_set_border_radius_corner($val, "bottom_right");
    }

    
    function set_border_radius($val)
    {
        $val = preg_replace("/\s*\,\s*/", ",", $val); 
        $arr = explode(" ", $val);

        switch (count($arr)) {
            case 1:
                $this->_set_border_radii($arr[0], $arr[0], $arr[0], $arr[0]);
                break;
            case 2:
                $this->_set_border_radii($arr[0], $arr[1], $arr[0], $arr[1]);
                break;
            case 3:
                $this->_set_border_radii($arr[0], $arr[1], $arr[2], $arr[1]);
                break;
            case 4:
                $this->_set_border_radii($arr[0], $arr[1], $arr[2], $arr[3]);
                break;
        }
    }

    
    protected function _set_border_radii($val1, $val2, $val3, $val4)
    {
        $this->_set_border_radius_corner($val1, "top_left");
        $this->_set_border_radius_corner($val2, "top_right");
        $this->_set_border_radius_corner($val3, "bottom_right");
        $this->_set_border_radius_corner($val4, "bottom_left");
    }

    
    protected function _set_border_radius_corner($val, $corner)
    {
        $this->_has_border_radius = true;

        $this->_props["border_" . $corner . "_radius"] = $val;
        $this->_props_computed["border_" . $corner . "_radius"] = null;
        $this->_prop_cache["border_" . $corner . "_radius"] = null;

        if ($val === 'inherit') {
            return;
        }

        $this->_props_computed["border_" . $corner . "_radius"] = $val;
    }

    
    function get_border_top_left_radius()
    {
        return $this->_get_border_radius_corner("top_left");
    }

    
    function get_border_top_right_radius()
    {
        return $this->_get_border_radius_corner("top_right");
    }

    
    function get_border_bottom_left_radius()
    {
        return $this->_get_border_radius_corner("bottom_left");
    }

    
    function get_border_bottom_right_radius()
    {
        return $this->_get_border_radius_corner("bottom_right");
    }

    
    protected function _get_border_radius_corner($corner)
    {
        if (!isset($this->_props_computed["border_" . $corner . "_radius"]) || empty($this->_props_computed["border_" . $corner . "_radius"])) {
            return 0;
        }

        return $this->length_in_pt($this->_props_computed["border_" . $corner . "_radius"]);
    }

    
    function set_outline($val)
    {
        $important = isset($this->_important_props["outline"]);

        $props = [
            "outline_style",
            "outline_width",
            "outline_color",
        ];

        foreach ($props as $prop) {
            $_val = self::$_defaults[$prop];

            if (!isset($this->_important_props[$prop]) || $important) {
                
                $this->_prop_cache[$prop] = null;
                if ($important) {
                    $this->_important_props[$prop] = true;
                }
                $this->_props[$prop] = $_val;
            }
        }

        $val = preg_replace("/\s*\,\s*/", ",", $val); 
        $arr = explode(" ", $val);
        foreach ($arr as $value) {
            $value = trim($value);

            if (in_array($value, self::$BORDER_STYLES)) {
                $this->__set("outline_style", $value);
            } else if ($value === "0" || preg_match("/[.0-9]+(?:px|pt|pc|em|ex|%|in|mm|cm)|(?:thin|medium|thick)/", $value)) {
                $this->__set("outline_width", $value);
            } else {
                
                $this->__set("outline_color", $value);
            }
        }

        
        $this->_props["outline"] = $val;
        $this->_props_computed["outline"] = null;
        $this->_prop_cache["outline"] = null;
    }

    
    function set_outline_width($val)
    {
        $this->_set_style_side_type("outline", null, "width", $val, isset($this->_important_props["outline_width"]));
    }

    
    function set_outline_color($val)
    {
        $color = $val;
        if ($val === "") {
            $color = $this->__get("color");
        }
        $this->_set_style_side_type("outline", null, "color", $color, isset($this->_important_props["outline_color"]));
    }

    
    function set_outline_style($val)
    {
        $this->_set_style_side_type("outline", null, "style", $val, isset($this->_important_props["outline_style"]));
    }

    
    function set_border_spacing($val)
    {
        $arr = explode(" ", $val);

        if (count($arr) == 1) {
            $arr[1] = $arr[0];
        }

        $this->_props["border_spacing"] = $val;
        $this->_props_computed["border_spacing"] = null;
        $this->_prop_cache["border_spacing"] = null;

        if ($val === 'inherit') {
            return;
        }

        $this->_props_computed["border_spacing"] = "$arr[0] $arr[1]";
    }

    
    function set_list_style_image($val)
    {
        $this->_props["list_style_image"] = $val;
        $parsed_val = $this->_image($val);
        if ($parsed_val === "none") {
            $this->_props_computed["list_style_image"] = "none";
        } else {
            $this->_props_computed["list_style_image"] = "url(" . $parsed_val . ")";
        }
        $this->_prop_cache["list_style_image"] = null;
    }

    
    function set_list_style($val)
    {
        $important = isset($this->_important_props["list_style"]);
        $arr = explode(" ", str_replace(",", " ", $val));

        static $types = [
            "disc", "circle", "square",
            "decimal-leading-zero", "decimal", "1",
            "lower-roman", "upper-roman", "a", "A",
            "lower-greek",
            "lower-latin", "upper-latin",
            "lower-alpha", "upper-alpha",
            "armenian", "georgian", "hebrew",
            "cjk-ideographic", "hiragana", "katakana",
            "hiragana-iroha", "katakana-iroha", "none"
        ];

        static $positions = ["inside", "outside"];

        foreach ($arr as $value) {
            
            if ($value === "none") {
                $this->_set_style("list_style_type", $value, $important);
                $this->_set_style("list_style_image", $value, $important);
                continue;
            }

            
            
            
            

            if (mb_substr($value, 0, 3) === "url") {
                $this->_set_style("list_style_image", $value, $important);
                continue;
            }

            if (in_array($value, $types)) {
                $this->_set_style("list_style_type", $value, $important);
            } else if (in_array($value, $positions)) {
                $this->_set_style("list_style_position", $value, $important);
            }
        }

        $this->_props["list_style"] = $val;
        $this->_props_computed["list_style"] = null;
        $this->_prop_cache["list_style"] = null;
    }

    
    function set_size($val)
    {
        $this->_props["size"] = $val;
        $this->_props_computed["size"] = null;
        $this->_prop_cache["size"] = null;

        $length_re = "/(\d+\s*(?:pt|px|pc|em|ex|in|cm|mm|%))/";

        $val = mb_strtolower($val);

        if ($val === "auto") {
            $this->_props["size"] = $val;
            return;
        }

        $parts = preg_split("/\s+/", $val);

        $computed = [];
        if (preg_match($length_re, $parts[0])) {
            $computed[] = $this->length_in_pt($parts[0]);

            if (isset($parts[1]) && preg_match($length_re, $parts[1])) {
                $computed[] = $this->length_in_pt($parts[1]);
            } else {
                $computed[] = $computed[0];
            }

            if (isset($parts[2]) && $parts[2] === "landscape") {
                $computed = array_reverse($computed);
            }
        } elseif (isset(CPDF::$PAPER_SIZES[$parts[0]])) {
            $computed = array_slice(CPDF::$PAPER_SIZES[$parts[0]], 2, 2);

            if (isset($parts[1]) && $parts[1] === "landscape") {
                $computed = array_reverse($computed);
            }
        } else {
            return;
        }

        $this->_props_computed["size"] = $computed;
    }

    
    function get_transform()
    {
        

        $number = "\s*([^,\s]+)\s*";
        $tr_value = "\s*([^,\s]+)\s*";
        $angle = "\s*([^,\s]+(?:deg|rad)?)\s*";

        if (!preg_match_all("/[a-z]+\([^\)]+\)/i", $this->_props_computed["transform"], $parts, PREG_SET_ORDER)) {
            return null;
        }

        $functions = [
            

            "translate" => "\($tr_value(?:,$tr_value)?\)",
            "translateX" => "\($tr_value\)",
            "translateY" => "\($tr_value\)",

            "scale" => "\($number(?:,$number)?\)",
            "scaleX" => "\($number\)",
            "scaleY" => "\($number\)",

            "rotate" => "\($angle\)",

            "skew" => "\($angle(?:,$angle)?\)",
            "skewX" => "\($angle\)",
            "skewY" => "\($angle\)",
        ];

        $transforms = [];

        foreach ($parts as $part) {
            $t = $part[0];

            foreach ($functions as $name => $pattern) {
                if (preg_match("/$name\s*$pattern/i", $t, $matches)) {
                    $values = array_slice($matches, 1);

                    switch ($name) {
                        
                        case "rotate":
                        case "skew":
                        case "skewX":
                        case "skewY":

                            foreach ($values as $i => $value) {
                                if (strpos($value, "rad")) {
                                    $values[$i] = rad2deg(floatval($value));
                                } else {
                                    $values[$i] = floatval($value);
                                }
                            }

                            switch ($name) {
                                case "skew":
                                    if (!isset($values[1])) {
                                        $values[1] = 0;
                                    }
                                    break;
                                case "skewX":
                                    $name = "skew";
                                    $values = [$values[0], 0];
                                    break;
                                case "skewY":
                                    $name = "skew";
                                    $values = [0, $values[0]];
                                    break;
                            }
                            break;

                        
                        case "translate":
                            $values[0] = $this->length_in_pt($values[0], (float)$this->length_in_pt($this->width));

                            if (isset($values[1])) {
                                $values[1] = $this->length_in_pt($values[1], (float)$this->length_in_pt($this->height));
                            } else {
                                $values[1] = 0;
                            }
                            break;

                        case "translateX":
                            $name = "translate";
                            $values = [$this->length_in_pt($values[0], (float)$this->length_in_pt($this->width)), 0];
                            break;

                        case "translateY":
                            $name = "translate";
                            $values = [0, $this->length_in_pt($values[0], (float)$this->length_in_pt($this->height))];
                            break;

                        
                        case "scale":
                            if (!isset($values[1])) {
                                $values[1] = $values[0];
                            }
                            break;

                        case "scaleX":
                            $name = "scale";
                            $values = [$values[0], 1.0];
                            break;

                        case "scaleY":
                            $name = "scale";
                            $values = [1.0, $values[0]];
                            break;
                    }

                    $transforms[] = [
                        $name,
                        $values,
                    ];
                }
            }
        }

        return $transforms;
    }

    
    function set_transform($val)
    {
        
        $this->_props["transform"] = $val;
        $this->_props_computed["transform"] = null;
        $this->_prop_cache["transform"] = null;

        if ($val === 'inherit') {
            return;
        }
        
        $this->_props_computed["transform"] = $val;
    }

    
    function set__webkit_transform($val)
    {
        $this->__set("transform", $val);
    }

    
    function set__webkit_transform_origin($val)
    {
        $this->__set("transform_origin", $val);
    }

    
    function set_transform_origin($val)
    {
        $this->_props["transform_origin"] = $val;
        $this->_props_computed["transform_origin"] = null;
        $this->_prop_cache["transform_origin"] = null;

        if ($val === 'inherit') {
            return;
        }

        $this->_props_computed["transform_origin"] = $val;
    }

    
    function get_transform_origin()
    {
        
        
        $values = preg_split("/\s+/", $this->_props_computed['transform_origin']);

        $values = array_map(function ($value) {
            if (in_array($value, ["top", "left"])) {
                return 0;
            } else if (in_array($value, ["bottom", "right"])) {
                return "100%";
            } else {
                return $value;
            }
        }, $values);

        if (!isset($values[1])) {
            $values[1] = $values[0];
        }

        return $values;
    }

    
    protected function parse_image_resolution($val)
    {
        
        

        $re = '/^\s*(\d+|normal|auto)\s*$/';

        if (!preg_match($re, $val, $matches)) {
            return null;
        }

        return $matches[1];
    }

    
    function set_background_image_resolution($val)
    {
        $this->_props["background_image_resolution"] = $val;
        $this->_props_computed["background_image_resolution"] = null;
        $this->_prop_cache["background_image_resolution"] = null;

        $parsed = $this->parse_image_resolution($val);

        $this->_props_computed["background_image_resolution"] = $parsed;
    }

    
    function set_image_resolution($val)
    {
        $this->_props["image_resolution"] = $val;
        $this->_props_computed["image_resolution"] = null;
        $this->_prop_cache["image_resolution"] = null;

        $parsed = $this->parse_image_resolution($val);

        $this->_props_computed["image_resolution"] = $parsed;
    }

    
    function set__dompdf_background_image_resolution($val)
    {
        $this->__set("background_image_resolution", $val);
    }

    
    function set__dompdf_image_resolution($val)
    {
        $this->__set("image_resolution", $val);
    }

    
    function set_z_index($val)
    {
        $this->_props["z_index"] = $val;
        $this->_props_computed["z_index"] = null;
        $this->_prop_cache["z_index"] = null;

        if ($val !== "auto" && round($val) != $val) {
            return;
        }

        $this->_props_computed["z_index"] = $val;
    }

    
    public function setFontMetrics(FontMetrics $fontMetrics)
    {
        $this->fontMetrics = $fontMetrics;
        return $this;
    }

    
    public function getFontMetrics()
    {
        return $this->fontMetrics;
    }

    
    
    function __toString()
    {
        return print_r(array_merge(["parent_font_size" => $this->_parent_font_size],
            $this->_props), true);
    }

    
    function debug_print()
    {
        print "    parent_font_size:" . $this->_parent_font_size . ";\n";
        print "    Props [\n";
        print "      specified [\n";
        foreach ($this->_props as $prop => $val) {
            print '        ' . $prop . ': ' . preg_replace("/\r\n/", ' ', print_r($val, true));
            if (isset($this->_important_props[$prop])) {
                print ' !important';
            }
            print ";\n";
        }
        print "      ]\n";
        print "      computed [\n";
        foreach ($this->_props_computed as $prop => $val) {
            print '        ' . $prop . ': ' . preg_replace("/\r\n/", ' ', print_r($val, true));
            print ";\n";
        }
        print "      ]\n";
        print "      cached [\n";
        foreach ($this->_prop_cache as $prop => $val) {
            print '        ' . $prop . ': ' . preg_replace("/\r\n/", ' ', print_r($val, true));
            print ";\n";
        }
        print "      ]\n";
        print "    ]\n";
    }
}
