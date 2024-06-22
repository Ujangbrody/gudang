<?php



namespace Dompdf\Adapter;

use Dompdf\Canvas;
use Dompdf\Dompdf;
use Dompdf\Helpers;
use Dompdf\Exception;
use Dompdf\Image\Cache;
use Dompdf\PhpEvaluator;
use FontLib\Exception\FontNotFoundException;


class CPDF implements Canvas
{

    
    static $PAPER_SIZES = [
        "4a0" => [0, 0, 4767.87, 6740.79],
        "2a0" => [0, 0, 3370.39, 4767.87],
        "a0" => [0, 0, 2383.94, 3370.39],
        "a1" => [0, 0, 1683.78, 2383.94],
        "a2" => [0, 0, 1190.55, 1683.78],
        "a3" => [0, 0, 841.89, 1190.55],
        "a4" => [0, 0, 595.28, 841.89],
        "a5" => [0, 0, 419.53, 595.28],
        "a6" => [0, 0, 297.64, 419.53],
        "a7" => [0, 0, 209.76, 297.64],
        "a8" => [0, 0, 147.40, 209.76],
        "a9" => [0, 0, 104.88, 147.40],
        "a10" => [0, 0, 73.70, 104.88],
        "b0" => [0, 0, 2834.65, 4008.19],
        "b1" => [0, 0, 2004.09, 2834.65],
        "b2" => [0, 0, 1417.32, 2004.09],
        "b3" => [0, 0, 1000.63, 1417.32],
        "b4" => [0, 0, 708.66, 1000.63],
        "b5" => [0, 0, 498.90, 708.66],
        "b6" => [0, 0, 354.33, 498.90],
        "b7" => [0, 0, 249.45, 354.33],
        "b8" => [0, 0, 175.75, 249.45],
        "b9" => [0, 0, 124.72, 175.75],
        "b10" => [0, 0, 87.87, 124.72],
        "c0" => [0, 0, 2599.37, 3676.54],
        "c1" => [0, 0, 1836.85, 2599.37],
        "c2" => [0, 0, 1298.27, 1836.85],
        "c3" => [0, 0, 918.43, 1298.27],
        "c4" => [0, 0, 649.13, 918.43],
        "c5" => [0, 0, 459.21, 649.13],
        "c6" => [0, 0, 323.15, 459.21],
        "c7" => [0, 0, 229.61, 323.15],
        "c8" => [0, 0, 161.57, 229.61],
        "c9" => [0, 0, 113.39, 161.57],
        "c10" => [0, 0, 79.37, 113.39],
        "ra0" => [0, 0, 2437.80, 3458.27],
        "ra1" => [0, 0, 1729.13, 2437.80],
        "ra2" => [0, 0, 1218.90, 1729.13],
        "ra3" => [0, 0, 864.57, 1218.90],
        "ra4" => [0, 0, 609.45, 864.57],
        "sra0" => [0, 0, 2551.18, 3628.35],
        "sra1" => [0, 0, 1814.17, 2551.18],
        "sra2" => [0, 0, 1275.59, 1814.17],
        "sra3" => [0, 0, 907.09, 1275.59],
        "sra4" => [0, 0, 637.80, 907.09],
        "letter" => [0, 0, 612.00, 792.00],
        "half-letter" => [0, 0, 396.00, 612.00],
        "legal" => [0, 0, 612.00, 1008.00],
        "ledger" => [0, 0, 1224.00, 792.00],
        "tabloid" => [0, 0, 792.00, 1224.00],
        "executive" => [0, 0, 521.86, 756.00],
        "folio" => [0, 0, 612.00, 936.00],
        "commercial #10 envelope" => [0, 0, 684, 297],
        "catalog #10 1/2 envelope" => [0, 0, 648, 864],
        "8.5x11" => [0, 0, 612.00, 792.00],
        "8.5x14" => [0, 0, 612.00, 1008.0],
        "11x17" => [0, 0, 792.00, 1224.00],
    ];

    
    protected $_dompdf;

    
    protected $_pdf;

    
    protected $_width;

    
    protected $_height;

    
    protected $_page_number;

    
    protected $_page_count;

    
    protected $_page_text;

    
    protected $_pages;

    
    protected $_image_cache;

    
    protected $_current_opacity = 1;

    
    public function __construct($paper = "letter", $orientation = "portrait", Dompdf $dompdf = null)
    {
        if (is_array($paper)) {
            $size = $paper;
        } else if (isset(self::$PAPER_SIZES[mb_strtolower($paper)])) {
            $size = self::$PAPER_SIZES[mb_strtolower($paper)];
        } else {
            $size = self::$PAPER_SIZES["letter"];
        }

        if (mb_strtolower($orientation) === "landscape") {
            [$size[2], $size[3]] = [$size[3], $size[2]];
        }

        if ($dompdf === null) {
            $this->_dompdf = new Dompdf();
        } else {
            $this->_dompdf = $dompdf;
        }

        $this->_pdf = new \Dompdf\Cpdf(
            $size,
            true,
            $dompdf->getOptions()->getFontCache(),
            $dompdf->getOptions()->getTempDir()
        );

        $this->_pdf->addInfo("Producer", sprintf("%s + CPDF", $dompdf->version));
        $time = substr_replace(date('YmdHisO'), '\'', -2, 0) . '\'';
        $this->_pdf->addInfo("CreationDate", "D:$time");
        $this->_pdf->addInfo("ModDate", "D:$time");

        $this->_width = $size[2] - $size[0];
        $this->_height = $size[3] - $size[1];

        $this->_page_number = $this->_page_count = 1;
        $this->_page_text = [];

        $this->_pages = [$this->_pdf->getFirstPageId()];

        $this->_image_cache = [];
    }

    
    public function get_dompdf()
    {
        return $this->_dompdf;
    }

    
    public function __destruct()
    {
        foreach ($this->_image_cache as $img) {
            
            
            
            
            if (!file_exists($img)) {
                continue;
            }

            if ($this->_dompdf->getOptions()->getDebugPng()) {
                print '[__destruct unlink ' . $img . ']';
            }
            if (!$this->_dompdf->getOptions()->getDebugKeepTemp()) {
                unlink($img);
            }
        }
    }

    
    public function get_cpdf()
    {
        return $this->_pdf;
    }

    
    public function add_info($label, $value)
    {
        $this->_pdf->addInfo($label, $value);
    }

    
    public function open_object()
    {
        $ret = $this->_pdf->openObject();
        $this->_pdf->saveState();
        return $ret;
    }

    
    public function reopen_object($object)
    {
        $this->_pdf->reopenObject($object);
        $this->_pdf->saveState();
    }

    
    public function close_object()
    {
        $this->_pdf->restoreState();
        $this->_pdf->closeObject();
    }

    
    public function add_object($object, $where = 'all')
    {
        $this->_pdf->addObject($object, $where);
    }

    
    public function stop_object($object)
    {
        $this->_pdf->stopObject($object);
    }

    
    public function serialize_object($id)
    {
        
        return $this->_pdf->serializeObject($id);
    }

    
    public function reopen_serialized_object($obj)
    {
        return $this->_pdf->restoreSerializedObject($obj);
    }

    

    
    public function get_width()
    {
        return $this->_width;
    }

    
    public function get_height()
    {
        return $this->_height;
    }

    
    public function get_page_number()
    {
        return $this->_page_number;
    }

    
    public function get_page_count()
    {
        return $this->_page_count;
    }

    
    public function set_page_number($num)
    {
        $this->_page_number = $num;
    }

    
    public function set_page_count($count)
    {
        $this->_page_count = $count;
    }

    
    protected function _set_stroke_color($color)
    {
        $this->_pdf->setStrokeColor($color);
        $alpha = isset($color["alpha"]) ? $color["alpha"] : 1;
        if ($this->_current_opacity != 1) {
            $alpha *= $this->_current_opacity;
        }
        $this->_set_line_transparency("Normal", $alpha);
    }

    
    protected function _set_fill_color($color)
    {
        $this->_pdf->setColor($color);
        $alpha = isset($color["alpha"]) ? $color["alpha"] : 1;
        if ($this->_current_opacity) {
            $alpha *= $this->_current_opacity;
        }
        $this->_set_fill_transparency("Normal", $alpha);
    }

    
    protected function _set_line_transparency($mode, $opacity)
    {
        $this->_pdf->setLineTransparency($mode, $opacity);
    }

    
    protected function _set_fill_transparency($mode, $opacity)
    {
        $this->_pdf->setFillTransparency($mode, $opacity);
    }

    
    protected function _set_line_style($width, $cap, $join, $dash)
    {
        $this->_pdf->setLineStyle($width, $cap, $join, $dash);
    }

    
    public function set_opacity($opacity, $mode = "Normal")
    {
        $this->_set_line_transparency($mode, $opacity);
        $this->_set_fill_transparency($mode, $opacity);
        $this->_current_opacity = $opacity;
    }

    public function set_default_view($view, $options = [])
    {
        array_unshift($options, $view);
        call_user_func_array([$this->_pdf, "openHere"], $options);
    }

    
    protected function y($y)
    {
        return $this->_height - $y;
    }

    
    public function line($x1, $y1, $x2, $y2, $color, $width, $style = [])
    {
        $this->_set_stroke_color($color);
        $this->_set_line_style($width, "butt", "", $style);

        $this->_pdf->line($x1, $this->y($y1),
            $x2, $this->y($y2));
        $this->_set_line_transparency("Normal", $this->_current_opacity);
    }

    
    public function page_line($x1, $y1, $x2, $y2, $color, $width, $style = [])
    {
        $_t = 'line';
        $this->_page_text[] = compact('_t', 'x1', 'y1', 'x2', 'y2', 'color', 'width', 'style');
    }

    
    public function arc($x, $y, $r1, $r2, $astart, $aend, $color, $width, $style = [])
    {
        $this->_set_stroke_color($color);
        $this->_set_line_style($width, "butt", "", $style);

        $this->_pdf->ellipse($x, $this->y($y), $r1, $r2, 0, 8, $astart, $aend, false, false, true, false);
        $this->_set_line_transparency("Normal", $this->_current_opacity);
    }

    
    protected function _convert_gif_bmp_to_png($image_url, $type)
    {
        $func_name = "imagecreatefrom$type";

        if (!function_exists($func_name)) {
            if (!method_exists(Helpers::class, $func_name)) {
                throw new Exception("Function $func_name() not found.  Cannot convert $type image: $image_url.  Please install the image PHP extension.");
            }
            $func_name = "\\Dompdf\\Helpers::" . $func_name;
        }

        set_error_handler([Helpers::class, 'record_warnings']);

        try {
            $im = call_user_func($func_name, $image_url);

            if ($im) {
                imageinterlace($im, false);

                $tmp_dir = $this->_dompdf->getOptions()->getTempDir();
                $tmp_name = @tempnam($tmp_dir, "{$type}dompdf_img_");
                @unlink($tmp_name);
                $filename = "$tmp_name.png";
                $this->_image_cache[] = $filename;

                imagepng($im, $filename);
                imagedestroy($im);
            } else {
                $filename = Cache::$broken_image;
            }
        } finally {
            restore_error_handler();
        }

        return $filename;
    }

    
    public function rectangle($x1, $y1, $w, $h, $color, $width, $style = [])
    {
        $this->_set_stroke_color($color);
        $this->_set_line_style($width, "butt", "", $style);
        $this->_pdf->rectangle($x1, $this->y($y1) - $h, $w, $h);
        $this->_set_line_transparency("Normal", $this->_current_opacity);
    }

    
    public function filled_rectangle($x1, $y1, $w, $h, $color)
    {
        $this->_set_fill_color($color);
        $this->_pdf->filledRectangle($x1, $this->y($y1) - $h, $w, $h);
        $this->_set_fill_transparency("Normal", $this->_current_opacity);
    }

    
    public function clipping_rectangle($x1, $y1, $w, $h)
    {
        $this->_pdf->clippingRectangle($x1, $this->y($y1) - $h, $w, $h);
    }

    
    public function clipping_roundrectangle($x1, $y1, $w, $h, $rTL, $rTR, $rBR, $rBL)
    {
        $this->_pdf->clippingRectangleRounded($x1, $this->y($y1) - $h, $w, $h, $rTL, $rTR, $rBR, $rBL);
    }

    
    public function clipping_end()
    {
        $this->_pdf->clippingEnd();
    }

    
    public function save()
    {
        $this->_pdf->saveState();
    }

    
    public function restore()
    {
        $this->_pdf->restoreState();
    }

    
    public function rotate($angle, $x, $y)
    {
        $this->_pdf->rotate($angle, $x, $y);
    }

    
    public function skew($angle_x, $angle_y, $x, $y)
    {
        $this->_pdf->skew($angle_x, $angle_y, $x, $y);
    }

    
    public function scale($s_x, $s_y, $x, $y)
    {
        $this->_pdf->scale($s_x, $s_y, $x, $y);
    }

    
    public function translate($t_x, $t_y)
    {
        $this->_pdf->translate($t_x, $t_y);
    }

    
    public function transform($a, $b, $c, $d, $e, $f)
    {
        $this->_pdf->transform([$a, $b, $c, $d, $e, $f]);
    }

    
    public function polygon($points, $color, $width = null, $style = [], $fill = false)
    {
        $this->_set_fill_color($color);
        $this->_set_stroke_color($color);

        
        for ($i = 1; $i < count($points); $i += 2) {
            $points[$i] = $this->y($points[$i]);
        }

        $this->_pdf->polygon($points, count($points) / 2, $fill);

        $this->_set_fill_transparency("Normal", $this->_current_opacity);
        $this->_set_line_transparency("Normal", $this->_current_opacity);
    }

    
    public function circle($x, $y, $r1, $color, $width = null, $style = null, $fill = false)
    {
        $this->_set_fill_color($color);
        $this->_set_stroke_color($color);

        if (!$fill && isset($width)) {
            $this->_set_line_style($width, "round", "round", $style);
        }

        $this->_pdf->ellipse($x, $this->y($y), $r1, 0, 0, 8, 0, 360, 1, $fill);

        $this->_set_fill_transparency("Normal", $this->_current_opacity);
        $this->_set_line_transparency("Normal", $this->_current_opacity);
    }

    
    public function image($img, $x, $y, $w, $h, $resolution = "normal")
    {
        [$width, $height, $type] = Helpers::dompdf_getimagesize($img, $this->get_dompdf()->getHttpContext());

        $debug_png = $this->_dompdf->getOptions()->getDebugPng();

        if ($debug_png) {
            print "[image:$img|$width|$height|$type]";
        }

        switch ($type) {
            case "jpeg":
                if ($debug_png) {
                    print '!!!jpg!!!';
                }
                $this->_pdf->addJpegFromFile($img, $x, $this->y($y) - $h, $w, $h);
                break;

            case "gif":
            
            case "bmp":
                if ($debug_png) print '!!!bmp or gif!!!';
                
                $img = $this->_convert_gif_bmp_to_png($img, $type);

            case "png":
                if ($debug_png) print '!!!png!!!';

                $this->_pdf->addPngFromFile($img, $x, $this->y($y) - $h, $w, $h);
                break;

            case "svg":
                if ($debug_png) print '!!!SVG!!!';

                $this->_pdf->addSvgFromFile($img, $x, $this->y($y) - $h, $w, $h);
                break;

            default:
                if ($debug_png) print '!!!unknown!!!';
        }
    }

    public function select($x, $y, $w, $h, $font, $size, $color = [0, 0, 0], $opts = [])
    {
        $pdf = $this->_pdf;

        $font .= ".afm";
        $pdf->selectFont($font);

        if (!isset($pdf->acroFormId)) {
            $pdf->addForm();
        }

        $ft = \Dompdf\Cpdf::ACROFORM_FIELD_CHOICE;
        $ff = \Dompdf\Cpdf::ACROFORM_FIELD_CHOICE_COMBO;

        $id = $pdf->addFormField($ft, rand(), $x, $this->y($y) - $h, $x + $w, $this->y($y), $ff, $size, $color);
        $pdf->setFormFieldOpt($id, $opts);
    }

    public function textarea($x, $y, $w, $h, $font, $size, $color = [0, 0, 0])
    {
        $pdf = $this->_pdf;

        $font .= ".afm";
        $pdf->selectFont($font);

        if (!isset($pdf->acroFormId)) {
            $pdf->addForm();
        }

        $ft = \Dompdf\Cpdf::ACROFORM_FIELD_TEXT;
        $ff = \Dompdf\Cpdf::ACROFORM_FIELD_TEXT_MULTILINE;

        $pdf->addFormField($ft, rand(), $x, $this->y($y) - $h, $x + $w, $this->y($y), $ff, $size, $color);
    }

    public function input($x, $y, $w, $h, $type, $font, $size, $color = [0, 0, 0])
    {
        $pdf = $this->_pdf;

        $font .= ".afm";
        $pdf->selectFont($font);

        if (!isset($pdf->acroFormId)) {
            $pdf->addForm();
        }

        $ft = \Dompdf\Cpdf::ACROFORM_FIELD_TEXT;
        $ff = 0;

        switch($type) {
            case 'text':
                $ft = \Dompdf\Cpdf::ACROFORM_FIELD_TEXT;
                break;
            case 'password':
                $ft = \Dompdf\Cpdf::ACROFORM_FIELD_TEXT;
                $ff = \Dompdf\Cpdf::ACROFORM_FIELD_TEXT_PASSWORD;
                break;
            case 'submit':
                $ft = \Dompdf\Cpdf::ACROFORM_FIELD_BUTTON;
                break;
        }

        $pdf->addFormField($ft, rand(), $x, $this->y($y) - $h, $x + $w, $this->y($y), $ff, $size, $color);
    }

    
    public function text($x, $y, $text, $font, $size, $color = [0, 0, 0], $word_space = 0.0, $char_space = 0.0, $angle = 0.0)
    {
        $pdf = $this->_pdf;

        $this->_set_fill_color($color);

        $is_font_subsetting = $this->_dompdf->getOptions()->getIsFontSubsettingEnabled();
        $pdf->selectFont($font . '.afm', '', true, $is_font_subsetting);

        $pdf->addText($x, $this->y($y) - $pdf->getFontHeight($size), $size, $text, $angle, $word_space, $char_space);

        $this->_set_fill_transparency("Normal", $this->_current_opacity);
    }

    
    public function javascript($code)
    {
        $this->_pdf->addJavascript($code);
    }

    

    
    public function add_named_dest($anchorname)
    {
        $this->_pdf->addDestination($anchorname, "Fit");
    }

    
    public function add_link($url, $x, $y, $width, $height)
    {
        $y = $this->y($y) - $height;

        if (strpos($url, '#') === 0) {
            
            $name = substr($url, 1);
            if ($name) {
                $this->_pdf->addInternalLink($name, $x, $y, $x + $width, $y + $height);
            }
        } else {
            $this->_pdf->addLink(rawurldecode($url), $x, $y, $x + $width, $y + $height);
        }
    }

    
    public function get_text_width($text, $font, $size, $word_spacing = 0, $char_spacing = 0)
    {
        $this->_pdf->selectFont($font, '', true, $this->_dompdf->getOptions()->getIsFontSubsettingEnabled());
        return $this->_pdf->getTextWidth($size, $text, $word_spacing, $char_spacing);
    }

    
    public function register_string_subset($font, $string)
    {
        $this->_pdf->registerText($font, $string);
    }

    
    public function get_font_height($font, $size)
    {
        $options = $this->_dompdf->getOptions();
        $this->_pdf->selectFont($font, '', true, $options->getIsFontSubsettingEnabled());

        return $this->_pdf->getFontHeight($size) * $options->getFontHeightRatio();
    }

    

    
    public function get_font_baseline($font, $size)
    {
        $ratio = $this->_dompdf->getOptions()->getFontHeightRatio();
        return $this->get_font_height($font, $size) / $ratio;
    }

    
    public function page_text($x, $y, $text, $font, $size, $color = [0, 0, 0], $word_space = 0.0, $char_space = 0.0, $angle = 0.0)
    {
        $_t = "text";
        $this->_page_text[] = compact("_t", "x", "y", "text", "font", "size", "color", "word_space", "char_space", "angle");
    }

    
    public function page_script($code, $type = "text/php")
    {
        $_t = "script";
        $this->_page_text[] = compact("_t", "code", "type");
    }

    
    public function new_page()
    {
        $this->_page_number++;
        $this->_page_count++;

        $ret = $this->_pdf->newPage();
        $this->_pages[] = $ret;
        return $ret;
    }

    
    protected function _add_page_text()
    {
        if (!count($this->_page_text)) {
            return;
        }

        $page_number = 1;
        $eval = null;

        foreach ($this->_pages as $pid) {
            $this->reopen_object($pid);

            foreach ($this->_page_text as $pt) {
                extract($pt);

                switch ($_t) {
                    case "text":
                        $text = str_replace(["{PAGE_NUM}", "{PAGE_COUNT}"],
                            [$page_number, $this->_page_count], $text);
                        $this->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
                        break;

                    case "script":
                        if (!$eval) {
                            $eval = new PhpEvaluator($this);
                        }
                        $eval->evaluate($code, ['PAGE_NUM' => $page_number, 'PAGE_COUNT' => $this->_page_count]);
                        break;

                    case 'line':
                        $this->line( $x1, $y1, $x2, $y2, $color, $width, $style );
                        break;
                }
            }

            $this->close_object();
            $page_number++;
        }
    }

    
    public function stream($filename = "document.pdf", $options = [])
    {
        if (headers_sent()) {
            die("Unable to stream pdf: headers already sent");
        }

        if (!isset($options["compress"])) $options["compress"] = true;
        if (!isset($options["Attachment"])) $options["Attachment"] = true;

        $this->_add_page_text();

        $debug = !$options['compress'];
        $tmp = ltrim($this->_pdf->output($debug));

        header("Cache-Control: private");
        header("Content-Type: application/pdf");
        header("Content-Length: " . mb_strlen($tmp, "8bit"));

        $filename = str_replace(["\n", "'"], "", basename($filename, ".pdf")) . ".pdf";
        $attachment = $options["Attachment"] ? "attachment" : "inline";
        header(Helpers::buildContentDispositionHeader($attachment, $filename));

        echo $tmp;
        flush();
    }

    
    public function output($options = [])
    {
        if (!isset($options["compress"])) $options["compress"] = true;

        $this->_add_page_text();

        $debug = !$options['compress'];

        return $this->_pdf->output($debug);
    }

    
    public function get_messages()
    {
        return $this->_pdf->messages;
    }
}
