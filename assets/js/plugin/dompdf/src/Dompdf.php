<?php

namespace Dompdf;

use DOMDocument;
use DOMNode;
use Dompdf\Adapter\CPDF;
use DOMXPath;
use Dompdf\Frame\Factory;
use Dompdf\Frame\FrameTree;
use HTML5_Tokenizer;
use HTML5_TreeBuilder;
use Dompdf\Image\Cache;
use Dompdf\Renderer\ListBullet;
use Dompdf\Css\Stylesheet;
use Dompdf\Helpers;


class Dompdf
{
    
    private $version = 'dompdf';

    
    private $dom;

    
    private $tree;

    
    private $css;

    
    private $canvas;

    
    private $paperSize;

    
    private $paperOrientation = "portrait";

    
    private $callbacks = [];

    
    private $cacheId;

    
    private $baseHost = "";

    
    private $basePath = "";

    
    private $protocol;

    
    private $httpContext;

    
    private $startTime = null;

    
    private $systemLocale = null;

    
    private $mbstringEncoding = null;

    
    private $pcreJit = null;

    
    private $defaultView = "Fit";

    
    private $defaultViewOptions = [];

    
    private $quirksmode = false;

    
    private $allowedProtocols = [null, "", "file:

    
    private $allowedLocalFileExtensions = ["htm", "html"];

    
    private $messages = [];

    
    private $options;

    
    private $fontMetrics;

    
    public static $native_fonts = [
        "courier", "courier-bold", "courier-oblique", "courier-boldoblique",
        "helvetica", "helvetica-bold", "helvetica-oblique", "helvetica-boldoblique",
        "times-roman", "times-bold", "times-italic", "times-bolditalic",
        "symbol", "zapfdinbats"
    ];

    
    public static $nativeFonts = [
        "courier", "courier-bold", "courier-oblique", "courier-boldoblique",
        "helvetica", "helvetica-bold", "helvetica-oblique", "helvetica-boldoblique",
        "times-roman", "times-bold", "times-italic", "times-bolditalic",
        "symbol", "zapfdinbats"
    ];

    
    public function __construct($options = null)
    {
        if (isset($options) && $options instanceof Options) {
            $this->setOptions($options);
        } elseif (is_array($options)) {
            $this->setOptions(new Options($options));
        } else {
            $this->setOptions(new Options());
        }

        $versionFile = realpath(__DIR__ . '/../VERSION');
        if (file_exists($versionFile) && ($version = trim(file_get_contents($versionFile))) !== false && $version !== '$Format:<%h>$') {
          $this->version = sprintf('dompdf %s', $version);
        }

        $this->setPhpConfig();

        $this->paperSize = $this->options->getDefaultPaperSize();
        $this->paperOrientation = $this->options->getDefaultPaperOrientation();

        $this->setCanvas(CanvasFactory::get_instance($this, $this->paperSize, $this->paperOrientation));
        $this->setFontMetrics(new FontMetrics($this->getCanvas(), $this->getOptions()));
        $this->css = new Stylesheet($this);

        $this->restorePhpConfig();
    }

    
    private function setPhpConfig()
    {
        if (sprintf('%.1f', 1.0) !== '1.0') {
            $this->systemLocale = setlocale(LC_NUMERIC, "0");
            setlocale(LC_NUMERIC, "C");
        }

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $this->pcreJit = @ini_get('pcre.jit');
            @ini_set('pcre.jit', '0');
        }

        $this->mbstringEncoding = mb_internal_encoding();
        mb_internal_encoding('UTF-8');
    }

    
    private function restorePhpConfig()
    {
        if (!empty($this->systemLocale)) {
            setlocale(LC_NUMERIC, $this->systemLocale);
            $this->systemLocale = null;
        }

        if (!empty($this->pcreJit)) {
            @ini_set('pcre.jit', $this->pcreJit);
            $this->pcreJit = null;
        }

        if (!empty($this->mbstringEncoding)) {
            mb_internal_encoding($this->mbstringEncoding);
            $this->mbstringEncoding = null;
        }
    }

    
    public function load_html_file($file)
    {
        $this->loadHtmlFile($file);
    }

    
    public function loadHtmlFile($file, $encoding = null)
    {
        $this->setPhpConfig();

        if (!$this->protocol && !$this->baseHost && !$this->basePath) {
            [$this->protocol, $this->baseHost, $this->basePath] = Helpers::explode_url($file);
        }
        $protocol = strtolower($this->protocol);
        
        $uri = Helpers::build_url($this->protocol, $this->baseHost, $this->basePath, $file);

        if ( !in_array($protocol, $this->allowedProtocols) ) {
            throw new Exception("Permission denied on $file. The communication protocol is not supported.");
        }

        if (!$this->options->isRemoteEnabled() && ($protocol != "" && $protocol !== "file:
            throw new Exception("Remote file requested, but remote file download is disabled.");
        }

        if ($protocol == "" || $protocol === "file:
            $realfile = realpath($uri);

            $chroot = $this->options->getChroot();
            $chrootValid = false;
            foreach($chroot as $chrootPath) {
                $chrootPath = realpath($chrootPath);
                if ($chrootPath !== false && strpos($realfile, $chrootPath) === 0) {
                    $chrootValid = true;
                    break;
                }
            }
            if ($chrootValid !== true) {
                throw new Exception("Permission denied on $file. The file could not be found under the paths specified by Options::chroot.");
            }

            $ext = strtolower(pathinfo($realfile, PATHINFO_EXTENSION));
            if (!in_array($ext, $this->allowedLocalFileExtensions)) {
                throw new Exception("Permission denied on $file. This file extension is forbidden");
            }

            if (!$realfile) {
                throw new Exception("File '$file' not found.");
            }

            $uri = $realfile;
        }

        [$contents, $http_response_header] = Helpers::getFileContent($uri, $this->httpContext);
        if (empty($contents)) {
            throw new Exception("File '$file' not found.");
        }

        
        if (isset($http_response_header)) {
            foreach ($http_response_header as $_header) {
                if (preg_match("@Content-Type:\s*[\w/]+;\s*?charset=([^\s]+)@i", $_header, $matches)) {
                    $encoding = strtoupper($matches[1]);
                    break;
                }
            }
        }

        $this->restorePhpConfig();

        $this->loadHtml($contents, $encoding);
    }

    
    public function load_html($str, $encoding = null)
    {
        $this->loadHtml($str, $encoding);
    }

    public function loadDOM($doc, $quirksmode = false) {
        
        $tag_names = ["html", "head", "table", "tbody", "thead", "tfoot", "tr"];
        foreach ($tag_names as $tag_name) {
            $nodes = $doc->getElementsByTagName($tag_name);

            foreach ($nodes as $node) {
                self::removeTextNodes($node);
            }
        }

        $this->dom = $doc;
        $this->quirksmode = $quirksmode;
        $this->tree = new FrameTree($this->dom);
    }

    
    public function loadHtml($str, $encoding = null)
    {
        $this->setPhpConfig();

        
        if ($encoding === null) {
            mb_detect_order('auto');
            if (($encoding = mb_detect_encoding($str, null, true)) === false) {

                
                $encoding = "auto";
            }
        }

        if (in_array(strtoupper($encoding), array('UTF-8','UTF8')) === false) {
            $str = mb_convert_encoding($str, 'UTF-8', $encoding);

            
            $encoding = 'UTF-8';
        }

        $metatags = [
            '@<meta\s+http-equiv="Content-Type"\s+content="(?:[\w/]+)(?:;\s*?charset=([^\s"]+))?@i',
            '@<meta\s+content="(?:[\w/]+)(?:;\s*?charset=([^\s"]+))"?\s+http-equiv="Content-Type"@i',
            '@<meta [^>]*charset\s*=\s*["\']?\s*([^"\' ]+)@i',
        ];
        foreach ($metatags as $metatag) {
            if (preg_match($metatag, $str, $matches)) {
                if (isset($matches[1]) && in_array($matches[1], mb_list_encodings())) {
                    $document_encoding = $matches[1];
                    break;
                }
            }
        }
        if (isset($document_encoding) && in_array(strtoupper($document_encoding), ['UTF-8','UTF8']) === false) {
            $str = preg_replace('/charset=([^\s"]+)/i', 'charset=UTF-8', $str);
        } elseif (isset($document_encoding) === false && strpos($str, '<head>') !== false) {
            $str = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $str);
        } elseif (isset($document_encoding) === false) {
            $str = '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">' . $str;
        }

        
        
        if (substr($str, 0, 3) == chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            $str = substr($str, 3);
        }

        
        set_error_handler([Helpers::class, 'record_warnings']);

        try {
            
            
            
            $quirksmode = false;

            if ($this->options->isHtml5ParserEnabled() && class_exists(HTML5_Tokenizer::class)) {
                $tokenizer = new HTML5_Tokenizer($str);
                $tokenizer->parse();
                $doc = $tokenizer->save();

                $quirksmode = ($tokenizer->getTree()->getQuirksMode() > HTML5_TreeBuilder::NO_QUIRKS);
            } else {
                
                
                
                $doc = new DOMDocument("1.0", $encoding);
                $doc->preserveWhiteSpace = true;
                $doc->loadHTML($str);
                $doc->encoding = $encoding;

                
                if (preg_match("/^(.+)<!doctype/i", ltrim($str), $matches)) {
                    $quirksmode = true;
                } 
                elseif (!preg_match("/^<!doctype/i", ltrim($str), $matches)) {
                    $quirksmode = true;
                } else {
                    
                    if (!$doc->doctype->publicId && !$doc->doctype->systemId) {
                        $quirksmode = false;
                    }

                    
                    if (!preg_match("/xhtml/i", $doc->doctype->publicId)) {
                        $quirksmode = true;
                    }
                }
            }

            $this->loadDOM($doc, $quirksmode);
        } finally {
            restore_error_handler();
            $this->restorePhpConfig();
        }
    }

    
    public static function remove_text_nodes(DOMNode $node)
    {
        self::removeTextNodes($node);
    }

    
    public static function removeTextNodes(DOMNode $node)
    {
        $children = [];
        for ($i = 0; $i < $node->childNodes->length; $i++) {
            $child = $node->childNodes->item($i);
            if ($child->nodeName === "#text") {
                $children[] = $child;
            }
        }

        foreach ($children as $child) {
            $node->removeChild($child);
        }
    }

    
    private function processHtml()
    {
        $this->tree->build_tree();

        $this->css->load_css_file($this->css->getDefaultStylesheet(), Stylesheet::ORIG_UA);

        $acceptedmedia = Stylesheet::$ACCEPTED_GENERIC_MEDIA_TYPES;
        $acceptedmedia[] = $this->options->getDefaultMediaType();

        
        $base_nodes = $this->dom->getElementsByTagName("base");
        if ($base_nodes->length && ($href = $base_nodes->item(0)->getAttribute("href"))) {
            [$this->protocol, $this->baseHost, $this->basePath] = Helpers::explode_url($href);
        }

        
        $this->css->set_protocol($this->protocol);
        $this->css->set_host($this->baseHost);
        $this->css->set_base_path($this->basePath);

        
        $xpath = new DOMXPath($this->dom);
        $stylesheets = $xpath->query("

        
        foreach ($stylesheets as $tag) {
            switch (strtolower($tag->nodeName)) {
                
                case "link":
                    if (mb_strtolower(stripos($tag->getAttribute("rel"), "stylesheet") !== false) || 
                        mb_strtolower($tag->getAttribute("type")) === "text/css"
                    ) {
                        
                        
                        $formedialist = preg_split("/[\s\n,]/", $tag->getAttribute("media"), -1, PREG_SPLIT_NO_EMPTY);
                        if (count($formedialist) > 0) {
                            $accept = false;
                            foreach ($formedialist as $type) {
                                if (in_array(mb_strtolower(trim($type)), $acceptedmedia)) {
                                    $accept = true;
                                    break;
                                }
                            }

                            if (!$accept) {
                                
                                
                                break;
                            }
                        }

                        $url = $tag->getAttribute("href");
                        $url = Helpers::build_url($this->protocol, $this->baseHost, $this->basePath, $url);

                        $this->css->load_css_file($url, Stylesheet::ORIG_AUTHOR);
                    }
                    break;

                
                case "style":
                    
                    
                    
                    
                    if ($tag->hasAttributes() &&
                        ($media = $tag->getAttribute("media")) &&
                        !in_array($media, $acceptedmedia)
                    ) {
                        break;
                    }

                    $css = "";
                    if ($tag->hasChildNodes()) {
                        $child = $tag->firstChild;
                        while ($child) {
                            $css .= $child->nodeValue; 
                            $child = $child->nextSibling;
                        }
                    } else {
                        $css = $tag->nodeValue;
                    }

                    
                    $this->css->set_protocol($this->protocol);
                    $this->css->set_host($this->baseHost);
                    $this->css->set_base_path($this->basePath);

                    $this->css->load_css($css, Stylesheet::ORIG_AUTHOR);
                    break;
            }

            
            $this->css->set_protocol($this->protocol);
            $this->css->set_host($this->baseHost);
            $this->css->set_base_path($this->basePath);
        }
    }

    
    public function enable_caching($cacheId)
    {
        $this->enableCaching($cacheId);
    }

    
    public function enableCaching($cacheId)
    {
        $this->cacheId = $cacheId;
    }

    
    public function parse_default_view($value)
    {
        return $this->parseDefaultView($value);
    }

    
    public function parseDefaultView($value)
    {
        $valid = ["XYZ", "Fit", "FitH", "FitV", "FitR", "FitB", "FitBH", "FitBV"];

        $options = preg_split("/\s*,\s*/", trim($value));
        $defaultView = array_shift($options);

        if (!in_array($defaultView, $valid)) {
            return false;
        }

        $this->setDefaultView($defaultView, $options);
        return true;
    }

    
    public function render()
    {
        $this->setPhpConfig();
        $options = $this->options;

        $logOutputFile = $options->getLogOutputFile();
        if ($logOutputFile) {
            if (!file_exists($logOutputFile) && is_writable(dirname($logOutputFile))) {
                touch($logOutputFile);
            }

            $this->startTime = microtime(true);
            if (is_writable($logOutputFile)) {
                ob_start();
            }
        }

        $this->processHtml();

        $this->css->apply_styles($this->tree);

        
        $pageStyles = $this->css->get_page_styles();
        $basePageStyle = $pageStyles["base"];
        unset($pageStyles["base"]);

        foreach ($pageStyles as $pageStyle) {
            $pageStyle->inherit($basePageStyle);
        }

        $defaultOptionPaperSize = $this->getPaperSize($options->getDefaultPaperSize());
        
        
        if (is_array($basePageStyle->size)) {
            $basePageStyleSize = $basePageStyle->size;
            $this->setPaper([0, 0, $basePageStyleSize[0], $basePageStyleSize[1]]);
        }

        $paperSize = $this->getPaperSize();
        if (
            $defaultOptionPaperSize[2] !== $paperSize[2] ||
            $defaultOptionPaperSize[3] !== $paperSize[3] ||
            $options->getDefaultPaperOrientation() !== $this->paperOrientation
        ) {
            $this->setCanvas(CanvasFactory::get_instance($this, $this->paperSize, $this->paperOrientation));
            $this->fontMetrics->setCanvas($this->getCanvas());
        }

        $canvas = $this->getCanvas();

        $root = null;

        foreach ($this->tree->get_frames() as $frame) {
            
            if (is_null($root)) {
                $root = Factory::decorate_root($this->tree->get_root(), $this);
                continue;
            }

            
            Factory::decorate_frame($frame, $this, $root);
        }

        
        $title = $this->dom->getElementsByTagName("title");
        if ($title->length) {
            $canvas->add_info("Title", trim($title->item(0)->nodeValue));
        }

        $metas = $this->dom->getElementsByTagName("meta");
        $labels = [
            "author" => "Author",
            "keywords" => "Keywords",
            "description" => "Subject",
        ];
        
        foreach ($metas as $meta) {
            $name = mb_strtolower($meta->getAttribute("name"));
            $value = trim($meta->getAttribute("content"));

            if (isset($labels[$name])) {
                $canvas->add_info($labels[$name], $value);
                continue;
            }

            if ($name === "dompdf.view" && $this->parseDefaultView($value)) {
                $canvas->set_default_view($this->defaultView, $this->defaultViewOptions);
            }
        }

        $root->set_containing_block(0, 0, $canvas->get_width(), $canvas->get_height());
        $root->set_renderer(new Renderer($this));

        
        $root->reflow();

        
        Cache::clear();

        global $_dompdf_warnings, $_dompdf_show_warnings;
        if ($_dompdf_show_warnings && isset($_dompdf_warnings)) {
            echo '<b>Dompdf Warnings</b><br><pre>';
            foreach ($_dompdf_warnings as $msg) {
                echo $msg . "\n";
            }

            if ($canvas instanceof CPDF) {
                echo $canvas->get_cpdf()->messages;
            }
            echo '</pre>';
            flush();
        }

        if ($logOutputFile && is_writable($logOutputFile)) {
            $this->write_log();
            ob_end_clean();
        }

        $this->restorePhpConfig();
    }

    
    public function add_info($label, $value)
    {
        $canvas = $this->getCanvas();
        if (!is_null($canvas)) {
            $canvas->add_info($label, $value);
        }
    }

    
    private function write_log()
    {
        $log_output_file = $this->getOptions()->getLogOutputFile();
        if (!$log_output_file || !is_writable($log_output_file)) {
            return;
        }

        $frames = Frame::$ID_COUNTER;
        $memory = memory_get_peak_usage(true) / 1024;
        $time = (microtime(true) - $this->startTime) * 1000;

        $out = sprintf(
            "<span style='color: #000' title='Frames'>%6d</span>" .
            "<span style='color: #009' title='Memory'>%10.2f KB</span>" .
            "<span style='color: #900' title='Time'>%10.2f ms</span>" .
            "<span  title='Quirksmode'>  " .
            ($this->quirksmode ? "<span style='color: #d00'> ON</span>" : "<span style='color: #0d0'>OFF</span>") .
            "</span><br />", $frames, $memory, $time);

        $out .= ob_get_contents();
        ob_clean();

        file_put_contents($log_output_file, $out);
    }

    
    public function stream($filename = "document.pdf", $options = [])
    {
        $this->setPhpConfig();

        $canvas = $this->getCanvas();
        if (!is_null($canvas)) {
            $canvas->stream($filename, $options);
        }

        $this->restorePhpConfig();
    }

    
    public function output($options = [])
    {
        $this->setPhpConfig();

        $canvas = $this->getCanvas();
        if (is_null($canvas)) {
            return null;
        }

        $output = $canvas->output($options);

        $this->restorePhpConfig();

        return $output;
    }

    
    public function output_html()
    {
        return $this->outputHtml();
    }

    
    public function outputHtml()
    {
        return $this->dom->saveHTML();
    }

    
    public function get_option($key)
    {
        return $this->options->get($key);
    }

    
    public function set_option($key, $value)
    {
        $this->options->set($key, $value);
        return $this;
    }

    
    public function set_options(array $options)
    {
        $this->options->set($options);
        return $this;
    }

    
    public function set_paper($size, $orientation = "portrait")
    {
        $this->setPaper($size, $orientation);
    }

    
    public function setPaper($size, $orientation = "portrait")
    {
        $this->paperSize = $size;
        $this->paperOrientation = $orientation;
        return $this;
    }

    
    public function getPaperSize($paperSize = null)
    {
        $size = $paperSize !== null ? $paperSize : $this->paperSize;
        if (is_array($size)) {
            return $size;
        } else if (isset(Adapter\CPDF::$PAPER_SIZES[mb_strtolower($size)])) {
            return Adapter\CPDF::$PAPER_SIZES[mb_strtolower($size)];
        } else {
            return Adapter\CPDF::$PAPER_SIZES["letter"];
        }
    }

    
    public function getPaperOrientation()
    {
        return $this->paperOrientation;
    }

    
    public function setTree(FrameTree $tree)
    {
        $this->tree = $tree;
        return $this;
    }

    
    public function get_tree()
    {
        return $this->getTree();
    }

    
    public function getTree()
    {
        return $this->tree;
    }

    
    public function set_protocol($protocol)
    {
        return $this->setProtocol($protocol);
    }

    
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    
    public function get_protocol()
    {
        return $this->getProtocol();
    }

    
    public function getProtocol()
    {
        return $this->protocol;
    }

    
    public function set_host($host)
    {
        $this->setBaseHost($host);
    }

    
    public function setBaseHost($baseHost)
    {
        $this->baseHost = $baseHost;
        return $this;
    }

    
    public function get_host()
    {
        return $this->getBaseHost();
    }

    
    public function getBaseHost()
    {
        return $this->baseHost;
    }

    
    public function set_base_path($path)
    {
        $this->setBasePath($path);
    }

    
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    
    public function get_base_path()
    {
        return $this->getBasePath();
    }

    
    public function getBasePath()
    {
        return $this->basePath;
    }

    
    public function set_default_view($default_view, $options)
    {
        return $this->setDefaultView($default_view, $options);
    }

    
    public function setDefaultView($defaultView, $options)
    {
        $this->defaultView = $defaultView;
        $this->defaultViewOptions = $options;
        return $this;
    }

    
    public function set_http_context($http_context)
    {
        return $this->setHttpContext($http_context);
    }

    
    public function setHttpContext($httpContext)
    {
        $this->httpContext = $httpContext;
        return $this;
    }

    
    public function get_http_context()
    {
        return $this->getHttpContext();
    }

    
    public function getHttpContext()
    {
        return $this->httpContext;
    }

    
    public function setCanvas(Canvas $canvas)
    {
        $this->canvas = $canvas;
        return $this;
    }

    
    public function get_canvas()
    {
        return $this->getCanvas();
    }

    
    public function getCanvas()
    {
        return $this->canvas;
    }

    
    public function setCss(Stylesheet $css)
    {
        $this->css = $css;
        return $this;
    }

    
    public function get_css()
    {
        return $this->getCss();
    }

    
    public function getCss()
    {
        return $this->css;
    }

    
    public function setDom(DOMDocument $dom)
    {
        $this->dom = $dom;
        return $this;
    }

    
    public function get_dom()
    {
        return $this->getDom();
    }

    
    public function getDom()
    {
        return $this->dom;
    }

    
    public function setOptions(Options $options)
    {
        $this->options = $options;
        $fontMetrics = $this->getFontMetrics();
        if (isset($fontMetrics)) {
            $fontMetrics->setOptions($options);
        }
        return $this;
    }

    
    public function getOptions()
    {
        return $this->options;
    }

    
    public function get_callbacks()
    {
        return $this->getCallbacks();
    }

    
    public function getCallbacks()
    {
        return $this->callbacks;
    }

    
    public function set_callbacks($callbacks)
    {
        $this->setCallbacks($callbacks);
    }

    
    public function setCallbacks($callbacks)
    {
        if (is_array($callbacks)) {
            $this->callbacks = [];
            foreach ($callbacks as $c) {
                if (is_array($c) && isset($c['event']) && isset($c['f'])) {
                    $event = $c['event'];
                    $f = $c['f'];
                    if (is_callable($f) && is_string($event)) {
                        $this->callbacks[$event][] = $f;
                    }
                }
            }
        }
    }

    
    public function get_quirksmode()
    {
        return $this->getQuirksmode();
    }

    
    public function getQuirksmode()
    {
        return $this->quirksmode;
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

    
    function __get($prop)
    {
        switch ($prop)
        {
            case 'version' :
                return $this->version;
            default:
                throw new Exception( 'Invalid property: ' . $prop );
        }
    }
}
