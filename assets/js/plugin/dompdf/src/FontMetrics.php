<?php


namespace Dompdf;

use FontLib\Font;


class FontMetrics
{
    
    const CACHE_FILE = "dompdf_font_family_cache.php";

    
    protected $pdf;

    
    protected $canvas;

    
    protected $fontLookup = [];

    
    private $options;

    
    public function __construct(Canvas $canvas, Options $options)
    {
        $this->setCanvas($canvas);
        $this->setOptions($options);
        $this->loadFontFamilies();
    }

    
    public function save_font_families()
    {
        $this->saveFontFamilies();
    }

    
    public function saveFontFamilies()
    {
        
        $cacheData = sprintf("<?php return array (%s", PHP_EOL);
        foreach ($this->fontLookup as $family => $variants) {
            $cacheData .= sprintf("  '%s' => array(%s", addslashes($family), PHP_EOL);
            foreach ($variants as $variant => $path) {
                $path = sprintf("'%s'", $path);
                $path = str_replace('\'' . $this->getOptions()->getFontDir() , '$fontDir . \'' , $path);
                $path = str_replace('\'' . $this->getOptions()->getRootDir() , '$rootDir . \'' , $path);
                $cacheData .= sprintf("    '%s' => %s,%s", $variant, $path, PHP_EOL);
            }
            $cacheData .= sprintf("  ),%s", PHP_EOL);
        }
        $cacheData .= ") ?>";
        file_put_contents($this->getCacheFile(), $cacheData);
    }

    
    public function load_font_families()
    {
        $this->loadFontFamilies();
    }

    
    public function loadFontFamilies()
    {
        $fontDir = $this->getOptions()->getFontDir();
        $rootDir = $this->getOptions()->getRootDir();

        
        if (!defined("DOMPDF_DIR")) { define("DOMPDF_DIR", $rootDir); }
        if (!defined("DOMPDF_FONT_DIR")) { define("DOMPDF_FONT_DIR", $fontDir); }

        $file = $rootDir . "/lib/fonts/dompdf_font_family_cache.dist.php";
        $distFonts = require $file;

        if (!is_readable($this->getCacheFile())) {
            $this->fontLookup = $distFonts;
            return;
        }

        $cacheData = require $this->getCacheFile();

        $this->fontLookup = [];
        if (is_array($this->fontLookup)) {
            foreach ($cacheData as $key => $value) {
                $this->fontLookup[stripslashes($key)] = $value;
            }
        }

        
        $this->fontLookup += $distFonts;
    }

    
    public function register_font($style, $remote_file, $context = null)
    {
        return $this->registerFont($style, $remote_file);
    }

    
    public function registerFont($style, $remoteFile, $context = null)
    {
        $fontname = mb_strtolower($style["family"]);
        $families = $this->getFontFamilies();

        $entry = [];
        if (isset($families[$fontname])) {
            $entry = $families[$fontname];
        }

        $styleString = $this->getType("{$style['weight']} {$style['style']}");

        $fontDir = $this->getOptions()->getFontDir();
        $remoteHash = md5($remoteFile);

        $prefix = $fontname . "_" . $styleString;
        $prefix = trim($prefix, "-");
        if (function_exists('iconv')) {
            $prefix = @iconv('utf-8', 'us-ascii
        }
        $prefix_encoding = mb_detect_encoding($prefix, mb_detect_order(), true);
        $substchar = mb_substitute_character();
        mb_substitute_character(0x005F);
        $prefix = mb_convert_encoding($prefix, "ISO-8859-1", $prefix_encoding);
        mb_substitute_character($substchar);
        $prefix = preg_replace("[\W]", "_", $prefix);
        $prefix = preg_replace("/[^-_\w]+/", "", $prefix);
        
        $localFile = $fontDir . "/" . $prefix . "_" . $remoteHash;

        if (isset($entry[$styleString]) && $localFile == $entry[$styleString]) {
            return true;
        }

        $cacheEntry = $localFile;
        $localFile .= ".".strtolower(pathinfo(parse_url($remoteFile, PHP_URL_PATH), PATHINFO_EXTENSION));

        $entry[$styleString] = $cacheEntry;

        
        [$protocol, $baseHost, $basePath] = Helpers::explode_url($remoteFile);
        if (!$this->options->isRemoteEnabled() && ($protocol != "" && $protocol !== "file:
            Helpers::record_warnings(E_USER_WARNING, "Remote font resource $remoteFile referenced, but remote file download is disabled.", __FILE__, __LINE__);
            return false;
        }
        if ($protocol == "" || $protocol === "file:
            $realfile = realpath($remoteFile);

            $rootDir = realpath($this->options->getRootDir());
            if (strpos($realfile, $rootDir) !== 0) {
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
                    Helpers::record_warnings(E_USER_WARNING, "Permission denied on $remoteFile. The file could not be found under the paths specified by Options::chroot.", __FILE__, __LINE__);
                    return false;
                }
            }

            if (!$realfile) {
                Helpers::record_warnings(E_USER_WARNING, "File '$realfile' not found.", __FILE__, __LINE__);
                return false;
            }

            $remoteFile = $realfile;
        }
        list($remoteFileContent, $http_response_header) = @Helpers::getFileContent($remoteFile, $context);
        if (empty($remoteFileContent)) {
            return false;
        }

        $localTempFile = @tempnam($this->options->get("tempDir"), "dompdf-font-");
        file_put_contents($localTempFile, $remoteFileContent);

        $font = Font::load($localTempFile);

        if (!$font) {
            unlink($localTempFile);
            return false;
        }

        $font->parse();
        $font->saveAdobeFontMetrics("$cacheEntry.ufm");
        $font->close();

        unlink($localTempFile);

        if ( !file_exists("$cacheEntry.ufm") ) {
            return false;
        }

        
        file_put_contents($localFile, $remoteFileContent);

        if ( !file_exists($localFile) ) {
            unlink("$cacheEntry.ufm");
            return false;
        }

        $this->setFontFamily($fontname, $entry);
        $this->saveFontFamilies();

        return true;
    }

    
    public function get_text_width($text, $font, $size, $word_spacing = 0.0, $char_spacing = 0.0)
    {
        
        return $this->getTextWidth($text, $font, $size, $word_spacing, $char_spacing);
    }

    
    public function getTextWidth($text, $font, $size, $wordSpacing = 0.0, $charSpacing = 0.0)
    {
        
        static $cache = [];

        if ($text === "") {
            return 0;
        }

        
        $useCache = !isset($text[50]); 

        $key = "$font/$size/$wordSpacing/$charSpacing";

        if ($useCache && isset($cache[$key][$text])) {
            return $cache[$key]["$text"];
        }

        $width = $this->getCanvas()->get_text_width($text, $font, $size, $wordSpacing, $charSpacing);

        if ($useCache) {
            $cache[$key][$text] = $width;
        }

        return $width;
    }

    
    public function get_font_height($font, $size)
    {
        return $this->getFontHeight($font, $size);
    }

    
    public function getFontHeight($font, $size)
    {
        return $this->getCanvas()->get_font_height($font, $size);
    }

    
    public function get_font($family_raw, $subtype_raw = "normal")
    {
        return $this->getFont($family_raw, $subtype_raw);
    }

    
    public function getFont($familyRaw, $subtypeRaw = "normal")
    {
        static $cache = [];

        if (isset($cache[$familyRaw][$subtypeRaw])) {
            return $cache[$familyRaw][$subtypeRaw];
        }

        

        $subtype = strtolower($subtypeRaw);

        if ($familyRaw) {
            $family = str_replace(["'", '"'], "", strtolower($familyRaw));

            if (isset($this->fontLookup[$family][$subtype])) {
                return $cache[$familyRaw][$subtypeRaw] = $this->fontLookup[$family][$subtype];
            }

            return null;
        }

        $family = "serif";

        if (isset($this->fontLookup[$family][$subtype])) {
            return $cache[$familyRaw][$subtypeRaw] = $this->fontLookup[$family][$subtype];
        }

        if (!isset($this->fontLookup[$family])) {
            return null;
        }

        $family = $this->fontLookup[$family];

        foreach ($family as $sub => $font) {
            if (strpos($subtype, $sub) !== false) {
                return $cache[$familyRaw][$subtypeRaw] = $font;
            }
        }

        if ($subtype !== "normal") {
            foreach ($family as $sub => $font) {
                if ($sub !== "normal") {
                    return $cache[$familyRaw][$subtypeRaw] = $font;
                }
            }
        }

        $subtype = "normal";

        if (isset($family[$subtype])) {
            return $cache[$familyRaw][$subtypeRaw] = $family[$subtype];
        }

        return null;
    }

    
    public function get_family($family)
    {
        return $this->getFamily($family);
    }

    
    public function getFamily($family)
    {
        $family = str_replace(["'", '"'], "", mb_strtolower($family));

        if (isset($this->fontLookup[$family])) {
            return $this->fontLookup[$family];
        }

        return null;
    }

    
    public function get_type($type)
    {
        return $this->getType($type);
    }

    
    public function getType($type)
    {
        if (preg_match('/bold/i', $type)) {
            $weight = 700;
        } elseif (preg_match('/([1-9]00)/', $type, $match)) {
            $weight = (int)$match[0];
        } else {
            $weight = 400;
        }
        $weight = $weight === 400 ? 'normal' : $weight;
        $weight = $weight === 700 ? 'bold' : $weight;

        $style = preg_match('/italic|oblique/i', $type) ? 'italic' : null;

        if ($weight === 'normal' && $style !== null) {
            return $style;
        }

        return $style === null
            ? $weight
            : $weight.'_'.$style;
    }

    
    public function get_font_families()
    {
        return $this->getFontFamilies();
    }

    
    public function getFontFamilies()
    {
        return $this->fontLookup;
    }

    
    public function set_font_family($fontname, $entry)
    {
        $this->setFontFamily($fontname, $entry);
    }

    
    public function setFontFamily($fontname, $entry)
    {
        $this->fontLookup[mb_strtolower($fontname)] = $entry;
    }

    
    public function getCacheFile()
    {
        return $this->getOptions()->getFontDir() . '/' . self::CACHE_FILE;
    }

    
    public function setOptions(Options $options)
    {
        $this->options = $options;
        return $this;
    }

    
    public function getOptions()
    {
        return $this->options;
    }

    
    public function setCanvas(Canvas $canvas)
    {
        $this->canvas = $canvas;
        
        $this->pdf = $canvas;
        return $this;
    }

    
    public function getCanvas()
    {
        return $this->canvas;
    }
}