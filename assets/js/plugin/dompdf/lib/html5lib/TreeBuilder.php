<?php











class HTML5_TreeBuilder {
    public $stack = [];
    public $content_model;

    private $mode;
    private $original_mode;
    private $secondary_mode;
    private $dom;
    
    
    private $foster_parent = false;
    private $a_formatting  = [];

    private $head_pointer = null;
    private $form_pointer = null;

    private $flag_frameset_ok = true;
    private $flag_force_quirks = false;
    private $ignored = false;
    private $quirks_mode = null;
    
    
    
    
    private $ignore_lf_token = 0;
    private $fragment = false;
    private $root;

    private $scoping = ['applet','button','caption','html','marquee','object','table','td','th', 'svg:foreignObject'];
    private $formatting = ['a','b','big','code','em','font','i','nobr','s','small','strike','strong','tt','u'];
    
    private $special = ['address','area','article','aside','base','basefont','bgsound',
    'blockquote','body','br','center','col','colgroup','command','dc','dd','details','dir','div','dl','ds',
    'dt','embed','fieldset','figure','footer','form','frame','frameset','h1','h2','h3','h4','h5',
    'h6','head','header','hgroup','hr','iframe','img','input','isindex','li','link',
    'listing','menu','meta','nav','noembed','noframes','noscript','ol',
    'p','param','plaintext','pre','script','select','spacer','style',
    'tbody','textarea','tfoot','thead','title','tr','ul','wbr'];

    private $pendingTableCharacters;
    private $pendingTableCharactersDirty;

    
    const INITIAL           = 0;
    const BEFORE_HTML       = 1;
    const BEFORE_HEAD       = 2;
    const IN_HEAD           = 3;
    const IN_HEAD_NOSCRIPT  = 4;
    const AFTER_HEAD        = 5;
    const IN_BODY           = 6;
    const IN_CDATA_RCDATA   = 7;
    const IN_TABLE          = 8;
    const IN_TABLE_TEXT     = 9;
    const IN_CAPTION        = 10;
    const IN_COLUMN_GROUP   = 11;
    const IN_TABLE_BODY     = 12;
    const IN_ROW            = 13;
    const IN_CELL           = 14;
    const IN_SELECT         = 15;
    const IN_SELECT_IN_TABLE= 16;
    const IN_FOREIGN_CONTENT= 17;
    const AFTER_BODY        = 18;
    const IN_FRAMESET       = 19;
    const AFTER_FRAMESET    = 20;
    const AFTER_AFTER_BODY  = 21;
    const AFTER_AFTER_FRAMESET = 22;

    
    private function strConst($number) {
        static $lookup;
        if (!$lookup) {
            $lookup = [];
            $r = new ReflectionClass('HTML5_TreeBuilder');
            $consts = $r->getConstants();
            foreach ($consts as $const => $num) {
                if (!is_int($num)) {
                    continue;
                }
                $lookup[$num] = $const;
            }
        }
        return $lookup[$number];
    }

    
    const SPECIAL    = 100;
    const SCOPING    = 101;
    const FORMATTING = 102;
    const PHRASING   = 103;

    
    const NO_QUIRKS             = 200;
    const QUIRKS_MODE           = 201;
    const LIMITED_QUIRKS_MODE   = 202;

    
    const MARKER     = 300;

    
    const NS_HTML   = null; 
    const NS_MATHML = 'http:
    const NS_SVG    = 'http:
    const NS_XLINK  = 'http:
    const NS_XML    = 'http:
    const NS_XMLNS  = 'http:

    
    const SCOPE = 0;
    const SCOPE_LISTITEM = 1;
    const SCOPE_TABLE = 2;

    
    public function __construct() {
        $this->mode = self::INITIAL;
        $this->dom = new DOMDocument;

        $this->dom->encoding = 'UTF-8';
        $this->dom->preserveWhiteSpace = true;
        $this->dom->substituteEntities = true;
        $this->dom->strictErrorChecking = false;
    }

    public function getQuirksMode(){
      return $this->quirks_mode;
    }

    
    public function emitToken($token, $mode = null) {
        
        if ($token['type'] === HTML5_Tokenizer::PARSEERROR) {
            return;
        }
        if ($mode === null) {
            $mode = $this->mode;
        }

        

        if ($this->ignore_lf_token) {
            $this->ignore_lf_token--;
        }
        $this->ignored = false;

        switch ($mode) {
            case self::INITIAL:

                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->ignored = true;
                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    if (
                        $token['name'] !== 'html' || !empty($token['public']) ||
                        !empty($token['system']) || $token !== 'about:legacy-compat'
                    ) {
                        
                        
                    }
                    
                    if (!isset($token['public'])) {
                        $token['public'] = null;
                    }
                    if (!isset($token['system'])) {
                        $token['system'] = null;
                    }
                    
                    
                    
                    
                    $impl = new DOMImplementation();
                    
                    
                    if ($token['name']) {
                        $doctype = $impl->createDocumentType($token['name'], $token['public'], $token['system']);
                        $this->dom->appendChild($doctype);
                    } else {
                        
                        
                        $this->dom->emptyDoctype = true;
                    }
                    $public = is_null($token['public']) ? false : strtolower($token['public']);
                    $system = is_null($token['system']) ? false : strtolower($token['system']);
                    $publicStartsWithForQuirks = [
                     "+
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                     "-
                    ];
                    $publicSetToForQuirks = [
                     "-
                     "-/w3c/dtd html 4.0 transitional/en",
                     "html",
                    ];
                    $publicStartsWithAndSystemForQuirks = [
                     "-
                     "-
                    ];
                    $publicStartsWithForLimitedQuirks = [
                     "-
                     "-
                    ];
                    $publicStartsWithAndSystemForLimitedQuirks = [
                     "-
                     "-
                    ];
                    
                    if (
                        !empty($token['force-quirks']) ||
                        strtolower($token['name']) !== 'html'
                    ) {
                        $this->quirks_mode = self::QUIRKS_MODE;
                    } else {
                        do {
                            if ($system) {
                                foreach ($publicStartsWithAndSystemForQuirks as $x) {
                                    if (strncmp($public, $x, strlen($x)) === 0) {
                                        $this->quirks_mode = self::QUIRKS_MODE;
                                        break;
                                    }
                                }
                                if (!is_null($this->quirks_mode)) {
                                    break;
                                }
                                foreach ($publicStartsWithAndSystemForLimitedQuirks as $x) {
                                    if (strncmp($public, $x, strlen($x)) === 0) {
                                        $this->quirks_mode = self::LIMITED_QUIRKS_MODE;
                                        break;
                                    }
                                }
                                if (!is_null($this->quirks_mode)) {
                                    break;
                                }
                            }
                            foreach ($publicSetToForQuirks as $x) {
                                if ($public === $x) {
                                    $this->quirks_mode = self::QUIRKS_MODE;
                                    break;
                                }
                            }
                            if (!is_null($this->quirks_mode)) {
                                break;
                            }
                            foreach ($publicStartsWithForLimitedQuirks as $x) {
                                if (strncmp($public, $x, strlen($x)) === 0) {
                                    $this->quirks_mode = self::LIMITED_QUIRKS_MODE;
                                }
                            }
                            if (!is_null($this->quirks_mode)) {
                                break;
                            }
                            if ($system === "http:
                                $this->quirks_mode = self::QUIRKS_MODE;
                                break;
                            }
                            foreach ($publicStartsWithForQuirks as $x) {
                                if (strncmp($public, $x, strlen($x)) === 0) {
                                    $this->quirks_mode = self::QUIRKS_MODE;
                                    break;
                                }
                            }
                            if (is_null($this->quirks_mode)) {
                                $this->quirks_mode = self::NO_QUIRKS;
                            }
                        } while (false);
                    }
                    $this->mode = self::BEFORE_HTML;
                } else {
                    
                    
                    $this->mode = self::BEFORE_HTML;
                    $this->quirks_mode = self::QUIRKS_MODE;
                    $this->emitToken($token);
                }
                break;

            case self::BEFORE_HTML:
                
                if ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    
                    $this->ignored = true;

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    
                    $comment = $this->dom->createComment($token['data']);
                    $this->dom->appendChild($comment);

                
                } elseif ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->ignored = true;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] == 'html') {
                    
                    
                    $html = $this->insertElement($token, false);
                    $this->dom->appendChild($html);
                    $this->stack[] = $html;

                    $this->mode = self::BEFORE_HEAD;

                } else {
                    
                    
                    $html = $this->dom->createElementNS(self::NS_HTML, 'html');
                    $this->dom->appendChild($html);
                    $this->stack[] = $html;

                    
                    $this->mode = self::BEFORE_HEAD;
                    $this->emitToken($token);
                }
                break;

            case self::BEFORE_HEAD:
                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->ignored = true;

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    
                    $this->ignored = true;
                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
                    
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'head') {
                    
                    $element = $this->insertElement($token);

                    
                    $this->head_pointer = $element;

                    
                    $this->mode = self::IN_HEAD;

                
                } elseif (
                    $token['type'] === HTML5_Tokenizer::ENDTAG && (
                        $token['name'] === 'head' || $token['name'] === 'body' ||
                        $token['name'] === 'html' || $token['name'] === 'br'
                )) {
                    
                    $this->emitToken([
                        'name' => 'head',
                        'type' => HTML5_Tokenizer::STARTTAG,
                        'attr' => []
                    ]);
                    $this->emitToken($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG) {
                    
                    $this->ignored = true;

                } else {
                    
                    $this->emitToken([
                        'name' => 'head',
                        'type' => HTML5_Tokenizer::STARTTAG,
                        'attr' => []
                    ]);
                    $this->emitToken($token);
                }
                break;

            case self::IN_HEAD:
                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->insertText($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    
                    $this->ignored = true;
                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'html') {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                ($token['name'] === 'base' || $token['name'] === 'command' ||
                $token['name'] === 'link')) {
                    
                    $this->insertElement($token);
                    array_pop($this->stack);

                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'meta') {
                    
                    $this->insertElement($token);
                    array_pop($this->stack);

                    

                    
                    
                    
                    
                    
                    
                    
                    
                    
                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'title') {
                    $this->insertRCDATAElement($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                ($token['name'] === 'noscript' || $token['name'] === 'noframes' || $token['name'] === 'style')) {
                    
                    $this->insertCDATAElement($token);

                

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'script') {
                    
                    $node = $this->insertElement($token, false);

                    
                    

                    
                    

                    
                    end($this->stack)->appendChild($node);
                    $this->stack[] = $node;
                    

                    
                    $this->original_mode = $this->mode;
                    
                    $this->mode = self::IN_CDATA_RCDATA;
                    
                    $this->content_model = HTML5_Tokenizer::CDATA;

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'head') {
                    
                    array_pop($this->stack);

                    
                    $this->mode = self::AFTER_HEAD;

                
                
                
                } elseif (($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'head') ||
                ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] !== 'html' &&
                $token['name'] !== 'body' && $token['name'] !== 'br')) {
                    
                    $this->ignored = true;

                
                } else {
                    
                    $this->emitToken([
                        'name' => 'head',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ]);

                    
                    $this->emitToken($token);
                }
                break;

            case self::IN_HEAD_NOSCRIPT:
                if ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
                    $this->processWithRulesFor($token, self::IN_BODY);
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'noscript') {
                    
                    array_pop($this->stack);
                    $this->mode = self::IN_HEAD;
                } elseif (
                    ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) ||
                    ($token['type'] === HTML5_Tokenizer::COMMENT) ||
                    ($token['type'] === HTML5_Tokenizer::STARTTAG && (
                        $token['name'] === 'link' || $token['name'] === 'meta' ||
                        $token['name'] === 'noframes' || $token['name'] === 'style'))) {
                    $this->processWithRulesFor($token, self::IN_HEAD);
                
                } elseif (
                    ($token['type'] === HTML5_Tokenizer::STARTTAG && (
                        $token['name'] === 'head' || $token['name'] === 'noscript')) ||
                    ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                        $token['name'] !== 'br')) {
                    
                } else {
                    
                    $this->emitToken([
                        'type' => HTML5_Tokenizer::ENDTAG,
                        'name' => 'noscript',
                    ]);
                    $this->emitToken($token);
                }
                break;

            case self::AFTER_HEAD:
                

                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->insertText($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);

                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'body') {
                    $this->insertElement($token);

                    
                    $this->flag_frameset_ok = false;

                    
                    $this->mode = self::IN_BODY;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'frameset') {
                    
                    $this->insertElement($token);

                    
                    $this->mode = self::IN_FRAMESET;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
                ['base', 'link', 'meta', 'noframes', 'script', 'style', 'title'])) {
                    
                    
                    $this->stack[] = $this->head_pointer;
                    $this->processWithRulesFor($token, self::IN_HEAD);
                    array_splice($this->stack, array_search($this->head_pointer, $this->stack, true), 1);

                
                } elseif (
                ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'head') ||
                ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                    $token['name'] !== 'body' && $token['name'] !== 'html' &&
                    $token['name'] !== 'br')) {
                    

                
                } else {
                    $this->emitToken([
                        'name' => 'body',
                        'type' => HTML5_Tokenizer::STARTTAG,
                        'attr' => []
                    ]);
                    $this->flag_frameset_ok = true;
                    $this->emitToken($token);
                }
                break;

            case self::IN_BODY:
                

                switch($token['type']) {
                    
                    case HTML5_Tokenizer::CHARACTER:
                    case HTML5_Tokenizer::SPACECHARACTER:
                        
                        $this->reconstructActiveFormattingElements();

                        
                        $this->insertText($token['data']);

                        
                        
                        if (strlen($token['data']) !== strspn($token['data'], HTML5_Tokenizer::WHITESPACE)) {
                            $this->flag_frameset_ok = false;
                        }
                    break;

                    
                    case HTML5_Tokenizer::COMMENT:
                        
                        $this->insertComment($token['data']);
                    break;

                    case HTML5_Tokenizer::DOCTYPE:
                        
                    break;

                    case HTML5_Tokenizer::EOF:
                        
                    break;

                    case HTML5_Tokenizer::STARTTAG:
                    switch($token['name']) {
                        case 'html':
                            
                            
                            foreach($token['attr'] as $attr) {
                                if (!$this->stack[0]->hasAttribute($attr['name'])) {
                                    $this->stack[0]->setAttribute($attr['name'], $attr['value']);
                                }
                            }
                        break;

                        case 'base': case 'command': case 'link': case 'meta': case 'noframes':
                        case 'script': case 'style': case 'title':
                            
                            $this->processWithRulesFor($token, self::IN_HEAD);
                        break;

                        
                        case 'body':
                            
                            if (count($this->stack) === 1 || $this->stack[1]->tagName !== 'body') {
                                $this->ignored = true;
                                

                            
                            } else {
                                foreach($token['attr'] as $attr) {
                                    if (!$this->stack[1]->hasAttribute($attr['name'])) {
                                        $this->stack[1]->setAttribute($attr['name'], $attr['value']);
                                    }
                                }
                            }
                        break;

                        case 'frameset':
                            
                            
                            if (count($this->stack) === 1 || $this->stack[1]->tagName !== 'body') {
                                $this->ignored = true;
                                
                            } elseif (!$this->flag_frameset_ok) {
                                $this->ignored = true;
                                
                            } else {
                                
                                if ($this->stack[1]->parentNode) {
                                    $this->stack[1]->parentNode->removeChild($this->stack[1]);
                                }

                                
                                array_splice($this->stack, 1);

                                $this->insertElement($token);
                                $this->mode = self::IN_FRAMESET;
                            }
                        break;

                        

                        case 'address': case 'article': case 'aside': case 'blockquote':
                        case 'center': case 'datagrid': case 'details': case 'dir':
                        case 'div': case 'dl': case 'fieldset': case 'figure': case 'footer':
                        case 'header': case 'hgroup': case 'menu': case 'nav':
                        case 'ol': case 'p': case 'section': case 'ul':
                            
                            if ($this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $this->insertElement($token);
                        break;

                        
                        case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                            
                            if ($this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $peek = array_pop($this->stack);
                            if (in_array($peek->tagName, ["h1", "h2", "h3", "h4", "h5", "h6"])) {
                                
                            } else {
                                $this->stack[] = $peek;
                            }

                            
                            $this->insertElement($token);
                        break;

                        case 'pre': case 'listing':
                            
                            if ($this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }
                            $this->insertElement($token);
                            
                            $this->ignore_lf_token = 2;
                            $this->flag_frameset_ok = false;
                        break;

                        
                        case 'form':
                            
                            if ($this->form_pointer !== null) {
                                $this->ignored = true;
                                

                            
                            } else {
                                
                                if ($this->elementInScope('p')) {
                                    $this->emitToken([
                                        'name' => 'p',
                                        'type' => HTML5_Tokenizer::ENDTAG
                                    ]);
                                }

                                
                                $element = $this->insertElement($token);
                                $this->form_pointer = $element;
                            }
                        break;

                        
                        case 'li': case 'dc': case 'dd': case 'ds': case 'dt':
                            
                            $this->flag_frameset_ok = false;

                            $stack_length = count($this->stack) - 1;
                            for($n = $stack_length; 0 <= $n; $n--) {
                                
                                $stop = false;
                                $node = $this->stack[$n];
                                $cat  = $this->getElementCategory($node);

                                
                                
                                
                                
                                if (($token['name'] === 'li' && $node->tagName === 'li') ||
                                ($token['name'] !== 'li' && ($node->tagName == 'dc' || $node->tagName === 'dd' || $node->tagName == 'ds' || $node->tagName === 'dt'))) { 
                                    $this->emitToken([
                                        'type' => HTML5_Tokenizer::ENDTAG,
                                        'name' => $node->tagName,
                                    ]);
                                    break;
                                }

                                
                                if ($cat !== self::FORMATTING && $cat !== self::PHRASING &&
                                $node->tagName !== 'address' && $node->tagName !== 'div' &&
                                $node->tagName !== 'p') {
                                    break;
                                }

                                
                            }

                            

                            
                            if ($this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $this->insertElement($token);
                        break;

                        
                        case 'plaintext':
                            
                            if ($this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $this->insertElement($token);

                            $this->content_model = HTML5_Tokenizer::PLAINTEXT;
                        break;

                        

                        
                        case 'a':
                            
                            $leng = count($this->a_formatting);

                            for ($n = $leng - 1; $n >= 0; $n--) {
                                if ($this->a_formatting[$n] === self::MARKER) {
                                    break;

                                } elseif ($this->a_formatting[$n]->tagName === 'a') {
                                    $a = $this->a_formatting[$n];
                                    $this->emitToken([
                                        'name' => 'a',
                                        'type' => HTML5_Tokenizer::ENDTAG
                                    ]);
                                    if (in_array($a, $this->a_formatting)) {
                                        $a_i = array_search($a, $this->a_formatting, true);
                                        if ($a_i !== false) {
                                            array_splice($this->a_formatting, $a_i, 1);
                                        }
                                    }
                                    if (in_array($a, $this->stack)) {
                                        $a_i = array_search($a, $this->stack, true);
                                        if ($a_i !== false) {
                                            array_splice($this->stack, $a_i, 1);
                                        }
                                    }
                                    break;
                                }
                            }

                            
                            $this->reconstructActiveFormattingElements();

                            
                            $el = $this->insertElement($token);

                            
                            $this->a_formatting[] = $el;
                        break;

                        case 'b': case 'big': case 'code': case 'em': case 'font': case 'i':
                        case 's': case 'small': case 'strike':
                        case 'strong': case 'tt': case 'u':
                            
                            $this->reconstructActiveFormattingElements();

                            
                            $el = $this->insertElement($token);

                            
                            $this->a_formatting[] = $el;
                        break;

                        case 'nobr':
                            
                            $this->reconstructActiveFormattingElements();

                            
                            if ($this->elementInScope('nobr')) {
                                $this->emitToken([
                                    'name' => 'nobr',
                                    'type' => HTML5_Tokenizer::ENDTAG,
                                ]);
                                $this->reconstructActiveFormattingElements();
                            }

                            
                            $el = $this->insertElement($token);

                            
                            $this->a_formatting[] = $el;
                        break;

                        

                        
                        case 'button':
                            
                            if ($this->elementInScope('button')) {
                                $this->emitToken([
                                    'name' => 'button',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $this->reconstructActiveFormattingElements();

                            
                            $this->insertElement($token);

                            
                            $this->a_formatting[] = self::MARKER;

                            $this->flag_frameset_ok = false;
                        break;

                        case 'applet': case 'marquee': case 'object':
                            
                            $this->reconstructActiveFormattingElements();

                            
                            $this->insertElement($token);

                            
                            $this->a_formatting[] = self::MARKER;

                            $this->flag_frameset_ok = false;
                        break;

                        

                        
                        case 'table':
                            
                            if ($this->quirks_mode !== self::QUIRKS_MODE &&
                            $this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $this->insertElement($token);

                            $this->flag_frameset_ok = false;

                            
                            $this->mode = self::IN_TABLE;
                        break;

                        
                        case 'area': case 'basefont': case 'bgsound': case 'br':
                        case 'embed': case 'img': case 'input': case 'keygen': case 'spacer':
                        case 'wbr':
                            
                            $this->reconstructActiveFormattingElements();

                            
                            $this->insertElement($token);

                            
                            array_pop($this->stack);

                            

                            $this->flag_frameset_ok = false;
                        break;

                        case 'param': case 'source':
                            
                            $this->insertElement($token);

                            
                            array_pop($this->stack);

                            
                        break;

                        
                        case 'hr':
                            
                            if ($this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $this->insertElement($token);

                            
                            array_pop($this->stack);

                            

                            $this->flag_frameset_ok = false;
                        break;

                        
                        case 'image':
                            
                            $token['name'] = 'img';
                            $this->emitToken($token);
                        break;

                        
                        case 'isindex':
                            

                            
                            if ($this->form_pointer === null) {
                                
                                
                                $attr = [];
                                $action = $this->getAttr($token, 'action');
                                if ($action !== false) {
                                    $attr[] = ['name' => 'action', 'value' => $action];
                                }
                                $this->emitToken([
                                    'name' => 'form',
                                    'type' => HTML5_Tokenizer::STARTTAG,
                                    'attr' => $attr
                                ]);

                                
                                $this->emitToken([
                                    'name' => 'hr',
                                    'type' => HTML5_Tokenizer::STARTTAG,
                                    'attr' => []
                                ]);

                                
                                $this->emitToken([
                                    'name' => 'label',
                                    'type' => HTML5_Tokenizer::STARTTAG,
                                    'attr' => []
                                ]);

                                
                                $prompt = $this->getAttr($token, 'prompt');
                                if ($prompt === false) {
                                    $prompt = 'This is a searchable index. '.
                                    'Insert your search keywords here: ';
                                }
                                $this->emitToken([
                                    'data' => $prompt,
                                    'type' => HTML5_Tokenizer::CHARACTER,
                                ]);

                                
                                $attr = [];
                                foreach ($token['attr'] as $keypair) {
                                    if ($keypair['name'] === 'name' || $keypair['name'] === 'action' ||
                                        $keypair['name'] === 'prompt') {
                                        continue;
                                    }
                                    $attr[] = $keypair;
                                }
                                $attr[] = ['name' => 'name', 'value' => 'isindex'];

                                $this->emitToken([
                                    'name' => 'input',
                                    'type' => HTML5_Tokenizer::STARTTAG,
                                    'attr' => $attr
                                ]);

                                
                                $this->emitToken([
                                    'name' => 'label',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);

                                
                                $this->emitToken([
                                    'name' => 'hr',
                                    'type' => HTML5_Tokenizer::STARTTAG
                                ]);

                                
                                $this->emitToken([
                                    'name' => 'form',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            } else {
                                $this->ignored = true;
                            }
                        break;

                        
                        case 'textarea':
                            $this->insertElement($token);

                            
                            $this->ignore_lf_token = 2;

                            $this->original_mode = $this->mode;
                            $this->flag_frameset_ok = false;
                            $this->mode = self::IN_CDATA_RCDATA;

                            
                            $this->content_model = HTML5_Tokenizer::RCDATA;
                        break;

                        
                        case 'xmp':
                            
                            if ($this->elementInScope('p')) {
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::ENDTAG
                                ]);
                            }

                            
                            $this->reconstructActiveFormattingElements();

                            $this->flag_frameset_ok = false;

                            $this->insertCDATAElement($token);
                        break;

                        case 'iframe':
                            $this->flag_frameset_ok = false;
                            $this->insertCDATAElement($token);
                        break;

                        case 'noembed': case 'noscript':
                            
                            $this->insertCDATAElement($token);
                        break;

                        
                        case 'select':
                            
                            $this->reconstructActiveFormattingElements();

                            
                            $this->insertElement($token);

                            $this->flag_frameset_ok = false;

                            
                            if (
                                $this->mode === self::IN_TABLE || $this->mode === self::IN_CAPTION ||
                                $this->mode === self::IN_COLUMN_GROUP || $this->mode ==+self::IN_TABLE_BODY ||
                                $this->mode === self::IN_ROW || $this->mode === self::IN_CELL
                            ) {
                                $this->mode = self::IN_SELECT_IN_TABLE;
                            } else {
                                $this->mode = self::IN_SELECT;
                            }
                        break;

                        case 'option': case 'optgroup':
                            if ($this->elementInScope('option')) {
                                $this->emitToken([
                                    'name' => 'option',
                                    'type' => HTML5_Tokenizer::ENDTAG,
                                ]);
                            }
                            $this->reconstructActiveFormattingElements();
                            $this->insertElement($token);
                        break;

                        case 'rp': case 'rt':
                            
                            if ($this->elementInScope('ruby')) {
                                $this->generateImpliedEndTags();
                            }
                            $peek = false;
                            do {
                                
                                $peek = array_pop($this->stack);
                            } while ($peek->tagName !== 'ruby');
                            $this->stack[] = $peek; 
                            $this->insertElement($token);
                        break;

                        

                        case 'math':
                            $this->reconstructActiveFormattingElements();
                            $token = $this->adjustMathMLAttributes($token);
                            $token = $this->adjustForeignAttributes($token);
                            $this->insertForeignElement($token, self::NS_MATHML);
                            if (isset($token['self-closing'])) {
                                
                                array_pop($this->stack);
                            }
                            if ($this->mode !== self::IN_FOREIGN_CONTENT) {
                                $this->secondary_mode = $this->mode;
                                $this->mode = self::IN_FOREIGN_CONTENT;
                            }
                        break;

                        case 'svg':
                            $this->reconstructActiveFormattingElements();
                            $token = $this->adjustSVGAttributes($token);
                            $token = $this->adjustForeignAttributes($token);
                            $this->insertForeignElement($token, self::NS_SVG);
                            if (isset($token['self-closing'])) {
                                
                                array_pop($this->stack);
                            }
                            if ($this->mode !== self::IN_FOREIGN_CONTENT) {
                                $this->secondary_mode = $this->mode;
                                $this->mode = self::IN_FOREIGN_CONTENT;
                            }
                        break;

                        case 'caption': case 'col': case 'colgroup': case 'frame': case 'head':
                        case 'tbody': case 'td': case 'tfoot': case 'th': case 'thead': case 'tr':
                            
                        break;

                        
                        default:
                            
                            $this->reconstructActiveFormattingElements();

                            $this->insertElement($token);
                            
                        break;
                    }
                    break;

                    case HTML5_Tokenizer::ENDTAG:
                    switch ($token['name']) {
                        
                        case 'body':
                            
                            if (!$this->elementInScope('body')) {
                                $this->ignored = true;

                            
                            } else {
                                
                            }

                            
                            $this->mode = self::AFTER_BODY;
                        break;

                        
                        case 'html':
                            
                            $this->emitToken([
                                'name' => 'body',
                                'type' => HTML5_Tokenizer::ENDTAG
                            ]);

                            if (!$this->ignored) {
                                $this->emitToken($token);
                            }
                        break;

                        case 'address': case 'article': case 'aside': case 'blockquote':
                        case 'center': case 'datagrid': case 'details': case 'dir':
                        case 'div': case 'dl': case 'fieldset': case 'footer':
                        case 'header': case 'hgroup': case 'listing': case 'menu':
                        case 'nav': case 'ol': case 'pre': case 'section': case 'ul':
                            
                            if ($this->elementInScope($token['name'])) {
                                $this->generateImpliedEndTags();

                                
                                

                                
                                do {
                                    $node = array_pop($this->stack);
                                } while ($node->tagName !== $token['name']);
                            } else {
                                
                            }
                        break;

                        
                        case 'form':
                            
                            $node = $this->form_pointer;
                            
                            $this->form_pointer = null;
                            
                            if ($node === null || !in_array($node, $this->stack)) {
                                
                                $this->ignored = true;
                            } else {
                                
                                $this->generateImpliedEndTags();
                                
                                if (end($this->stack) !== $node) {
                                    
                                }
                                
                                array_splice($this->stack, array_search($node, $this->stack, true), 1);
                            }

                        break;

                        
                        case 'p':
                            
                            if ($this->elementInScope('p')) {
                                
                                $this->generateImpliedEndTags(['p']);

                                
                                

                                
                                do {
                                    $node = array_pop($this->stack);
                                } while ($node->tagName !== 'p');

                            } else {
                                
                                $this->emitToken([
                                    'name' => 'p',
                                    'type' => HTML5_Tokenizer::STARTTAG,
                                ]);
                                $this->emitToken($token);
                            }
                        break;

                        
                        case 'li':
                            
                            if ($this->elementInScope($token['name'], self::SCOPE_LISTITEM)) {
                                
                                $this->generateImpliedEndTags([$token['name']]);
                                
                                
                                
                                do {
                                    $node = array_pop($this->stack);
                                } while ($node->tagName !== $token['name']);
                            }
                            
                        break;

                        
                        case 'dc': case 'dd': case 'ds': case 'dt':
                            if ($this->elementInScope($token['name'])) {
                                $this->generateImpliedEndTags([$token['name']]);

                                
                                

                                
                                do {
                                    $node = array_pop($this->stack);
                                } while ($node->tagName !== $token['name']);
                            }
                            
                        break;

                        
                        case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                            $elements = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

                            
                            if ($this->elementInScope($elements)) {
                                $this->generateImpliedEndTags();

                                
                                

                                
                                do {
                                    $node = array_pop($this->stack);
                                } while (!in_array($node->tagName, $elements));
                            }
                            
                        break;

                        
                        case 'a': case 'b': case 'big': case 'code': case 'em': case 'font':
                        case 'i': case 'nobr': case 's': case 'small': case 'strike':
                        case 'strong': case 'tt': case 'u':
                            
                            
                            while (true) {
                                for ($a = count($this->a_formatting) - 1; $a >= 0; $a--) {
                                    if ($this->a_formatting[$a] === self::MARKER) {
                                        break;
                                    } elseif ($this->a_formatting[$a]->tagName === $token['name']) {
                                        $formatting_element = $this->a_formatting[$a];
                                        $in_stack = in_array($formatting_element, $this->stack, true);
                                        $fe_af_pos = $a;
                                        break;
                                    }
                                }

                                
                                if (
                                    !isset($formatting_element) || (
                                        $in_stack &&
                                        !$this->elementInScope($token['name'])
                                    )
                                ) {
                                    $this->ignored = true;
                                    break;

                                
                                } elseif (isset($formatting_element) && !$in_stack) {
                                    unset($this->a_formatting[$fe_af_pos]);
                                    $this->a_formatting = array_merge($this->a_formatting);
                                    break;
                                }

                                
                                

                                
                                $fe_s_pos = array_search($formatting_element, $this->stack, true);
                                $length = count($this->stack);

                                for ($s = $fe_s_pos + 1; $s < $length; $s++) {
                                    $category = $this->getElementCategory($this->stack[$s]);

                                    if ($category !== self::PHRASING && $category !== self::FORMATTING) {
                                        $furthest_block = $this->stack[$s];
                                        break;
                                    }
                                }

                                
                                if (!isset($furthest_block)) {
                                    for ($n = $length - 1; $n >= $fe_s_pos; $n--) {
                                        array_pop($this->stack);
                                    }

                                    unset($this->a_formatting[$fe_af_pos]);
                                    $this->a_formatting = array_merge($this->a_formatting);
                                    break;
                                }

                                
                                $common_ancestor = $this->stack[$fe_s_pos - 1];

                                
                                $bookmark = $fe_af_pos;

                                
                                $node = $furthest_block;
                                $last_node = $furthest_block;

                                while (true) {
                                    for ($n = array_search($node, $this->stack, true) - 1; $n >= 0; $n--) {
                                        
                                        $node = $this->stack[$n];

                                        
                                        if (!in_array($node, $this->a_formatting, true)) {
                                            array_splice($this->stack, $n, 1);
                                        } else {
                                            break;
                                        }
                                    }

                                    
                                    if ($node === $formatting_element) {
                                        break;

                                    
                                    } elseif ($last_node === $furthest_block) {
                                        $bookmark = array_search($node, $this->a_formatting, true) + 1;
                                    }

                                    
                                    
                                    
                                    $clone = $node->cloneNode();
                                    $a_pos = array_search($node, $this->a_formatting, true);
                                    $s_pos = array_search($node, $this->stack, true);
                                    $this->a_formatting[$a_pos] = $clone;
                                    $this->stack[$s_pos] = $clone;
                                    $node = $clone;

                                    
                                    
                                    if ($last_node->parentNode !== null) {
                                        $last_node->parentNode->removeChild($last_node);
                                    }

                                    
                                    $node->appendChild($last_node);

                                    
                                    $last_node = $node;

                                    
                                }

                                
                                
                                if ($last_node->parentNode) { 
                                    $last_node->parentNode->removeChild($last_node);
                                }
                                if (in_array($common_ancestor->tagName, ['table', 'tbody', 'tfoot', 'thead', 'tr'])) {
                                    $this->fosterParent($last_node);
                                
                                } else {
                                    
                                    $common_ancestor->appendChild($last_node);
                                }

                                
                                
                                $clone = $formatting_element->cloneNode();

                                
                                
                                while ($furthest_block->hasChildNodes()) {
                                    $child = $furthest_block->firstChild;
                                    $furthest_block->removeChild($child);
                                    $clone->appendChild($child);
                                }

                                
                                
                                $furthest_block->appendChild($clone);

                                
                                $fe_af_pos = array_search($formatting_element, $this->a_formatting, true);
                                array_splice($this->a_formatting, $fe_af_pos, 1);

                                $af_part1 = array_slice($this->a_formatting, 0, $bookmark - 1);
                                $af_part2 = array_slice($this->a_formatting, $bookmark);
                                $this->a_formatting = array_merge($af_part1, [$clone], $af_part2);

                                
                                $fe_s_pos = array_search($formatting_element, $this->stack, true);
                                array_splice($this->stack, $fe_s_pos, 1);

                                $fb_s_pos = array_search($furthest_block, $this->stack, true);
                                $s_part1 = array_slice($this->stack, 0, $fb_s_pos + 1);
                                $s_part2 = array_slice($this->stack, $fb_s_pos + 1);
                                $this->stack = array_merge($s_part1, [$clone], $s_part2);

                                
                                unset($formatting_element, $fe_af_pos, $fe_s_pos, $furthest_block);
                            }
                        break;

                        case 'applet': case 'button': case 'marquee': case 'object':
                            
                            if ($this->elementInScope($token['name'])) {
                                $this->generateImpliedEndTags();

                                
                                

                                
                                do {
                                    $node = array_pop($this->stack);
                                } while ($node->tagName !== $token['name']);

                                
                                $keys = array_keys($this->a_formatting, self::MARKER, true);
                                $marker = end($keys);

                                for ($n = count($this->a_formatting) - 1; $n > $marker; $n--) {
                                    array_pop($this->a_formatting);
                                }
                            }
                            
                        break;

                        case 'br':
                            
                            $this->emitToken([
                                'name' => 'br',
                                'type' => HTML5_Tokenizer::STARTTAG,
                            ]);
                        break;

                        
                        default:
                            for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                                
                                $node = $this->stack[$n];

                                
                                if ($token['name'] === $node->tagName) {
                                    
                                    $this->generateImpliedEndTags();

                                    
                                    

                                    
                                    
                                    do {
                                        $pop = array_pop($this->stack);
                                    } while ($pop !== $node);
                                    break;
                                } else {
                                    $category = $this->getElementCategory($node);

                                    if ($category !== self::FORMATTING && $category !== self::PHRASING) {
                                        
                                        $this->ignored = true;
                                        break;
                                        
                                    }
                                }
                                
                            }
                        break;
                    }
                    break;
                }
                break;

            case self::IN_CDATA_RCDATA:
                if (
                    $token['type'] === HTML5_Tokenizer::CHARACTER ||
                    $token['type'] === HTML5_Tokenizer::SPACECHARACTER
                ) {
                    $this->insertText($token['data']);
                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    
                    
                    
                    array_pop($this->stack);
                    $this->mode = $this->original_mode;
                    $this->emitToken($token);
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'script') {
                    array_pop($this->stack);
                    $this->mode = $this->original_mode;
                    
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG) {
                    array_pop($this->stack);
                    $this->mode = $this->original_mode;
                }
            break;

            case self::IN_TABLE:
                $clear = ['html', 'table'];

                
                if ($token['type'] === HTML5_Tokenizer::CHARACTER ||
                    $token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->pendingTableCharacters = "";
                    $this->pendingTableCharactersDirty = false;
                    
                    $this->original_mode = $this->mode;
                    
                    $this->mode = self::IN_TABLE_TEXT;
                    $this->emitToken($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);

                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'caption') {
                    
                    $this->clearStackToTableContext($clear);

                    
                    $this->a_formatting[] = self::MARKER;

                    
                    $this->insertElement($token);
                    $this->mode = self::IN_CAPTION;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'colgroup') {
                    
                    $this->clearStackToTableContext($clear);

                    
                    $this->insertElement($token);
                    $this->mode = self::IN_COLUMN_GROUP;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'col') {
                    $this->emitToken([
                        'name' => 'colgroup',
                        'type' => HTML5_Tokenizer::STARTTAG,
                        'attr' => []
                    ]);

                    $this->emitToken($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
                ['tbody', 'tfoot', 'thead'])) {
                    
                    $this->clearStackToTableContext($clear);

                    
                    $this->insertElement($token);
                    $this->mode = self::IN_TABLE_BODY;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                in_array($token['name'], ['td', 'th', 'tr'])) {
                    
                    $this->emitToken([
                        'name' => 'tbody',
                        'type' => HTML5_Tokenizer::STARTTAG,
                        'attr' => []
                    ]);

                    $this->emitToken($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'table') {
                    
                    $this->emitToken([
                        'name' => 'table',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ]);

                    if (!$this->ignored) {
                        $this->emitToken($token);
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'table') {
                    
                    if (!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        $this->ignored = true;
                    } else {
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== 'table');

                        
                        $this->resetInsertionMode();
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
                ['body', 'caption', 'col', 'colgroup', 'html', 'tbody', 'td',
                'tfoot', 'th', 'thead', 'tr'])) {
                    

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                ($token['name'] === 'style' || $token['name'] === 'script')) {
                    $this->processWithRulesFor($token, self::IN_HEAD);

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'input' &&
                
                
                ($type = $this->getAttr($token, 'type')) && strtolower($type) === 'hidden') {
                    
                    
                    
                    $this->insertElement($token);
                    array_pop($this->stack);
                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    
                    if (end($this->stack)->tagName !== 'html') {
                        
                        
                    }
                    
                
                } else {
                    

                    $old = $this->foster_parent;
                    $this->foster_parent = true;
                    $this->processWithRulesFor($token, self::IN_BODY);
                    $this->foster_parent = $old;
                }
            break;

            case self::IN_TABLE_TEXT:
                
                if ($token['type'] === HTML5_Tokenizer::CHARACTER) {
                    
                    $this->pendingTableCharacters .= $token['data'];
                    $this->pendingTableCharactersDirty = true;
                } elseif ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    $this->pendingTableCharacters .= $token['data'];
                
                } else {
                    if ($this->pendingTableCharacters !== '' && is_string($this->pendingTableCharacters)) {
                        
                        if ($this->pendingTableCharactersDirty) {
                            
                            
                            $old = $this->foster_parent;
                            $this->foster_parent = true;
                            $text_token = [
                                'type' => HTML5_Tokenizer::CHARACTER,
                                'data' => $this->pendingTableCharacters,
                            ];
                            $this->processWithRulesFor($text_token, self::IN_BODY);
                            $this->foster_parent = $old;

                        
                        } else {
                            $this->insertText($this->pendingTableCharacters);
                        }
                        $this->pendingTableCharacters = null;
                        $this->pendingTableCharactersNull = null;
                    }

                    
                    $this->mode = $this->original_mode;
                    $this->emitToken($token);
                }
            break;

            case self::IN_CAPTION:
                
                if ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'caption') {
                    
                    if (!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        $this->ignored = true;
                        

                    
                    } else {
                        
                        $this->generateImpliedEndTags();

                        
                        

                        
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== 'caption');

                        
                        $this->clearTheActiveFormattingElementsUpToTheLastMarker();

                        
                        $this->mode = self::IN_TABLE;
                    }

                
                } elseif (($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
                ['caption', 'col', 'colgroup', 'tbody', 'td', 'tfoot', 'th',
                'thead', 'tr'])) || ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'table')) {
                    
                    $this->emitToken([
                        'name' => 'caption',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ]);

                    if (!$this->ignored) {
                        $this->emitToken($token);
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
                ['body', 'col', 'colgroup', 'html', 'tbody', 'tfoot', 'th',
                'thead', 'tr'])) {
                    
                    $this->ignored = true;
                } else {
                    
                    $this->processWithRulesFor($token, self::IN_BODY);
                }
            break;

            case self::IN_COLUMN_GROUP:
                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->insertText($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);
                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'col') {
                    
                    $this->insertElement($token);
                    array_pop($this->stack);
                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'colgroup') {
                    
                    if (end($this->stack)->tagName === 'html') {
                        $this->ignored = true;

                    
                    } else {
                        array_pop($this->stack);
                        $this->mode = self::IN_TABLE;
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'col') {
                    
                    $this->ignored = true;

                
                
                } elseif ($token['type'] === HTML5_Tokenizer::EOF && end($this->stack)->tagName === 'html') {
                    

                
                } else {
                    
                    $this->emitToken([
                        'name' => 'colgroup',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ]);

                    if (!$this->ignored) {
                        $this->emitToken($token);
                    }
                }
            break;

            case self::IN_TABLE_BODY:
                $clear = ['tbody', 'tfoot', 'thead', 'html'];

                
                if ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'tr') {
                    
                    $this->clearStackToTableContext($clear);

                    
                    $this->insertElement($token);
                    $this->mode = self::IN_ROW;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                ($token['name'] === 'th' ||    $token['name'] === 'td')) {
                    
                    $this->emitToken([
                        'name' => 'tr',
                        'type' => HTML5_Tokenizer::STARTTAG,
                        'attr' => []
                    ]);

                    $this->emitToken($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                in_array($token['name'], ['tbody', 'tfoot', 'thead'])) {
                    
                    if (!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        
                        $this->ignored = true;

                    
                    } else {
                        
                        $this->clearStackToTableContext($clear);

                        
                        array_pop($this->stack);
                        $this->mode = self::IN_TABLE;
                    }

                
                } elseif (($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
                ['caption', 'col', 'colgroup', 'tbody', 'tfoot', 'thead'])) ||
                ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'table')) {
                    
                    if (!$this->elementInScope(['tbody', 'thead', 'tfoot'], self::SCOPE_TABLE)) {
                        
                        $this->ignored = true;

                    
                    } else {
                        
                        $this->clearStackToTableContext($clear);

                        
                        $this->emitToken([
                            'name' => end($this->stack)->tagName,
                            'type' => HTML5_Tokenizer::ENDTAG
                        ]);

                        $this->emitToken($token);
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
                ['body', 'caption', 'col', 'colgroup', 'html', 'td', 'th', 'tr'])) {
                    
                    $this->ignored = true;

                
                } else {
                    
                    $this->processWithRulesFor($token, self::IN_TABLE);
                }
            break;

            case self::IN_ROW:
                $clear = ['tr', 'html'];

                
                if ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                ($token['name'] === 'th' || $token['name'] === 'td')) {
                    
                    $this->clearStackToTableContext($clear);

                    
                    $this->insertElement($token);
                    $this->mode = self::IN_CELL;

                    
                    $this->a_formatting[] = self::MARKER;

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'tr') {
                    
                    if (!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        
                        $this->ignored = true;
                    } else {
                        
                        $this->clearStackToTableContext($clear);

                        
                        array_pop($this->stack);
                        $this->mode = self::IN_TABLE_BODY;
                    }

                
                } elseif (($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
                ['caption', 'col', 'colgroup', 'tbody', 'tfoot', 'thead', 'tr'])) ||
                ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'table')) {
                    
                    $this->emitToken([
                        'name' => 'tr',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ]);
                    if (!$this->ignored) {
                        $this->emitToken($token);
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                in_array($token['name'], ['tbody', 'tfoot', 'thead'])) {
                    
                    if (!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        $this->ignored = true;

                    
                    } else {
                        
                        $this->emitToken([
                            'name' => 'tr',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ]);

                        $this->emitToken($token);
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
                ['body', 'caption', 'col', 'colgroup', 'html', 'td', 'th'])) {
                    
                    $this->ignored = true;

                
                } else {
                    
                    $this->processWithRulesFor($token, self::IN_TABLE);
                }
            break;

            case self::IN_CELL:
                
                if ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                ($token['name'] === 'td' || $token['name'] === 'th')) {
                    
                    if (!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        $this->ignored = true;

                    
                    } else {
                        
                        $this->generateImpliedEndTags([$token['name']]);

                        
                        

                        
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== $token['name']);

                        
                        $this->clearTheActiveFormattingElementsUpToTheLastMarker();

                        
                        $this->mode = self::IN_ROW;
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && in_array($token['name'],
                ['caption', 'col', 'colgroup', 'tbody', 'td', 'tfoot', 'th',
                'thead', 'tr'])) {
                    
                    if (!$this->elementInScope(['td', 'th'], self::SCOPE_TABLE)) {
                        
                        $this->ignored = true;

                    
                    } else {
                        $this->closeCell();
                        $this->emitToken($token);
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
                ['body', 'caption', 'col', 'colgroup', 'html'])) {
                    
                    $this->ignored = true;

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && in_array($token['name'],
                ['table', 'tbody', 'tfoot', 'thead', 'tr'])) {
                    
                    if (!$this->elementInScope(['td', 'th'], self::SCOPE_TABLE)) {
                        
                        $this->ignored = true;

                    
                    } else {
                        $this->closeCell();
                        $this->emitToken($token);
                    }

                
                } else {
                    
                    $this->processWithRulesFor($token, self::IN_BODY);
                }
            break;

            case self::IN_SELECT:
                

                
                if (
                    $token['type'] === HTML5_Tokenizer::CHARACTER ||
                    $token['type'] === HTML5_Tokenizer::SPACECHARACTER
                ) {
                    
                    $this->insertText($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);

                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'option') {
                    
                    if (end($this->stack)->tagName === 'option') {
                        $this->emitToken([
                            'name' => 'option',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ]);
                    }

                    
                    $this->insertElement($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'optgroup') {
                    
                    if (end($this->stack)->tagName === 'option') {
                        $this->emitToken([
                            'name' => 'option',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ]);
                    }

                    
                    if (end($this->stack)->tagName === 'optgroup') {
                        $this->emitToken([
                            'name' => 'optgroup',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ]);
                    }

                    
                    $this->insertElement($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'optgroup') {
                    
                    $elements_in_stack = count($this->stack);

                    if ($this->stack[$elements_in_stack - 1]->tagName === 'option' &&
                    $this->stack[$elements_in_stack - 2]->tagName === 'optgroup') {
                        $this->emitToken([
                            'name' => 'option',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ]);
                    }

                    
                    if (end($this->stack)->tagName === 'optgroup') {
                        array_pop($this->stack);
                    } else {
                        
                        $this->ignored = true;
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'option') {
                    
                    if (end($this->stack)->tagName === 'option') {
                        array_pop($this->stack);
                    } else {
                        
                        $this->ignored = true;
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'select') {
                    
                    if (!$this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        $this->ignored = true;
                        

                    
                    } else {
                        
                        do {
                            $node = array_pop($this->stack);
                        } while ($node->tagName !== 'select');

                        
                        $this->resetInsertionMode();
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'select') {
                    
                    $this->emitToken([
                        'name' => 'select',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ]);

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                ($token['name'] === 'input' || $token['name'] === 'keygen' ||  $token['name'] === 'textarea')) {
                    
                    $this->emitToken([
                        'name' => 'select',
                        'type' => HTML5_Tokenizer::ENDTAG
                    ]);
                    $this->emitToken($token);

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'script') {
                    $this->processWithRulesFor($token, self::IN_HEAD);

                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    
                    

                
                } else {
                    
                    $this->ignored = true;
                }
            break;

            case self::IN_SELECT_IN_TABLE:

                if ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                in_array($token['name'], ['caption', 'table', 'tbody',
                'tfoot', 'thead', 'tr', 'td', 'th'])) {
                    
                    $this->emitToken([
                        'name' => 'select',
                        'type' => HTML5_Tokenizer::ENDTAG,
                    ]);
                    $this->emitToken($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                in_array($token['name'], ['caption', 'table', 'tbody', 'tfoot', 'thead', 'tr', 'td', 'th']))  {
                    
                    

                    
                    if ($this->elementInScope($token['name'], self::SCOPE_TABLE)) {
                        $this->emitToken([
                            'name' => 'select',
                            'type' => HTML5_Tokenizer::ENDTAG
                        ]);

                        $this->emitToken($token);
                    } else {
                        $this->ignored = true;
                    }
                } else {
                    $this->processWithRulesFor($token, self::IN_SELECT);
                }
            break;

            case self::IN_FOREIGN_CONTENT:
                if ($token['type'] === HTML5_Tokenizer::CHARACTER ||
                $token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    $this->insertText($token['data']);
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    $this->insertComment($token['data']);
                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'script' && end($this->stack)->tagName === 'script' &&
                
                end($this->stack)->namespaceURI === self::NS_SVG) {
                    array_pop($this->stack);
                    
                } elseif (
                    ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                        ((
                            $token['name'] !== 'mglyph' &&
                            $token['name'] !== 'malignmark' &&
                            
                            end($this->stack)->namespaceURI === self::NS_MATHML &&
                            in_array(end($this->stack)->tagName, ['mi', 'mo', 'mn', 'ms', 'mtext'])
                        ) ||
                        (
                            $token['name'] === 'svg' &&
                            
                            end($this->stack)->namespaceURI === self::NS_MATHML &&
                            end($this->stack)->tagName === 'annotation-xml'
                        ) ||
                        (
                            
                            end($this->stack)->namespaceURI === self::NS_SVG &&
                            in_array(end($this->stack)->tagName, ['foreignObject', 'desc', 'title'])
                        ) ||
                        (
                            
                            end($this->stack)->namespaceURI === self::NS_HTML
                        ))
                    ) || $token['type'] === HTML5_Tokenizer::ENDTAG
                ) {
                    $this->processWithRulesFor($token, $this->secondary_mode);
                    
                    if ($this->mode === self::IN_FOREIGN_CONTENT) {
                        $found = false;
                        
                        for ($i = count($this->stack) - 1; $i >= 0; $i--) {
                            
                            $node = $this->stack[$i];
                            if ($node->namespaceURI !== self::NS_HTML) {
                                $found = true;
                                break;
                            } elseif (in_array($node->tagName, ['table', 'html',
                            'applet', 'caption', 'td', 'th', 'button', 'marquee',
                            'object']) || ($node->tagName === 'foreignObject' &&
                            $node->namespaceURI === self::NS_SVG)) {
                                break;
                            }
                        }
                        if (!$found) {
                            $this->mode = $this->secondary_mode;
                        }
                    }
                } elseif ($token['type'] === HTML5_Tokenizer::EOF || (
                $token['type'] === HTML5_Tokenizer::STARTTAG &&
                (in_array($token['name'], ['b', "big", "blockquote", "body", "br",
                "center", "code", "dc", "dd", "div", "dl", "ds", "dt", "em", "embed", "h1", "h2",
                "h3", "h4", "h5", "h6", "head", "hr", "i", "img", "li", "listing",
                "menu", "meta", "nobr", "ol", "p", "pre", "ruby", "s",  "small",
                "span", "strong", "strike",  "sub", "sup", "table", "tt", "u", "ul",
                "var"]) || ($token['name'] === 'font' && ($this->getAttr($token, 'color') ||
                $this->getAttr($token, 'face') || $this->getAttr($token, 'size')))))) {
                    
                    do {
                        $node = array_pop($this->stack);
                        
                    } while ($node->namespaceURI !== self::NS_HTML);
                    $this->stack[] = $node;
                    $this->mode = $this->secondary_mode;
                    $this->emitToken($token);
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG) {
                    static $svg_lookup = [
                        'altglyph' => 'altGlyph',
                        'altglyphdef' => 'altGlyphDef',
                        'altglyphitem' => 'altGlyphItem',
                        'animatecolor' => 'animateColor',
                        'animatemotion' => 'animateMotion',
                        'animatetransform' => 'animateTransform',
                        'clippath' => 'clipPath',
                        'feblend' => 'feBlend',
                        'fecolormatrix' => 'feColorMatrix',
                        'fecomponenttransfer' => 'feComponentTransfer',
                        'fecomposite' => 'feComposite',
                        'feconvolvematrix' => 'feConvolveMatrix',
                        'fediffuselighting' => 'feDiffuseLighting',
                        'fedisplacementmap' => 'feDisplacementMap',
                        'fedistantlight' => 'feDistantLight',
                        'feflood' => 'feFlood',
                        'fefunca' => 'feFuncA',
                        'fefuncb' => 'feFuncB',
                        'fefuncg' => 'feFuncG',
                        'fefuncr' => 'feFuncR',
                        'fegaussianblur' => 'feGaussianBlur',
                        'feimage' => 'feImage',
                        'femerge' => 'feMerge',
                        'femergenode' => 'feMergeNode',
                        'femorphology' => 'feMorphology',
                        'feoffset' => 'feOffset',
                        'fepointlight' => 'fePointLight',
                        'fespecularlighting' => 'feSpecularLighting',
                        'fespotlight' => 'feSpotLight',
                        'fetile' => 'feTile',
                        'feturbulence' => 'feTurbulence',
                        'foreignobject' => 'foreignObject',
                        'glyphref' => 'glyphRef',
                        'lineargradient' => 'linearGradient',
                        'radialgradient' => 'radialGradient',
                        'textpath' => 'textPath',
                    ];
                    
                    $current = end($this->stack);
                    if ($current->namespaceURI === self::NS_MATHML) {
                        $token = $this->adjustMathMLAttributes($token);
                    }
                    if ($current->namespaceURI === self::NS_SVG &&
                    isset($svg_lookup[$token['name']])) {
                        $token['name'] = $svg_lookup[$token['name']];
                    }
                    if ($current->namespaceURI === self::NS_SVG) {
                        $token = $this->adjustSVGAttributes($token);
                    }
                    $token = $this->adjustForeignAttributes($token);
                    $this->insertForeignElement($token, $current->namespaceURI);
                    if (isset($token['self-closing'])) {
                        array_pop($this->stack);
                        
                    }
                }
            break;

            case self::AFTER_BODY:
                

                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    
                    $comment = $this->dom->createComment($token['data']);
                    $this->stack[0]->appendChild($comment);

                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG && $token['name'] === 'html') {
                    
                    $this->ignored = true;
                    

                    $this->mode = self::AFTER_AFTER_BODY;

                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    

                
                } else {
                    
                    $this->mode = self::IN_BODY;
                    $this->emitToken($token);
                }
            break;

            case self::IN_FRAMESET:
                

                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->insertText($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);

                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'frameset') {
                    $this->insertElement($token);

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'frameset') {
                    
                    if (end($this->stack)->tagName === 'html') {
                        $this->ignored = true;
                        

                    } else {
                        
                        array_pop($this->stack);

                        
                        $this->mode = self::AFTER_FRAMESET;
                    }

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'frame') {
                    
                    $this->insertElement($token);

                    
                    array_pop($this->stack);

                    

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'noframes') {
                    
                    $this->processwithRulesFor($token, self::IN_HEAD);

                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    
                    
                
                } else {
                    
                    $this->ignored = true;
                }
            break;

            case self::AFTER_FRAMESET:
                

                
                if ($token['type'] === HTML5_Tokenizer::SPACECHARACTER) {
                    
                    $this->insertText($token['data']);

                
                } elseif ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    $this->insertComment($token['data']);

                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE) {
                    

                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html') {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::ENDTAG &&
                $token['name'] === 'html') {
                    $this->mode = self::AFTER_AFTER_FRAMESET;

                
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG &&
                $token['name'] === 'noframes') {
                    $this->processWithRulesFor($token, self::IN_HEAD);

                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    

                
                } else {
                    
                    $this->ignored = true;
                }
            break;

            case self::AFTER_AFTER_BODY:
                
                if ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    
                    $comment = $this->dom->createComment($token['data']);
                    $this->dom->appendChild($comment);

                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE ||
                $token['type'] === HTML5_Tokenizer::SPACECHARACTER ||
                ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html')) {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    
                } else {
                    
                    $this->mode = self::IN_BODY;
                    $this->emitToken($token);
                }
            break;

            case self::AFTER_AFTER_FRAMESET:
                
                if ($token['type'] === HTML5_Tokenizer::COMMENT) {
                    
                    
                    $comment = $this->dom->createComment($token['data']);
                    $this->dom->appendChild($comment);
                } elseif ($token['type'] === HTML5_Tokenizer::DOCTYPE ||
                $token['type'] === HTML5_Tokenizer::SPACECHARACTER ||
                ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'html')) {
                    $this->processWithRulesFor($token, self::IN_BODY);

                
                } elseif ($token['type'] === HTML5_Tokenizer::EOF) {
                    
                } elseif ($token['type'] === HTML5_Tokenizer::STARTTAG && $token['name'] === 'nofrmaes') {
                    $this->processWithRulesFor($token, self::IN_HEAD);
                } else {
                    
                }
            break;
        }
    }

    private function insertElement($token, $append = true) {
        $el = $this->dom->createElementNS(self::NS_HTML, $token['name']);

        if (!empty($token['attr'])) {
            foreach ($token['attr'] as $attr) {
                if (!$el->hasAttribute($attr['name']) && preg_match("/^[a-zA-Z_:]/", $attr['name'])) {
                    $el->setAttribute($attr['name'], $attr['value']);
                }
            }
        }
        if ($append) {
            $this->appendToRealParent($el);
            $this->stack[] = $el;
        }

        return $el;
    }

    
    private function insertText($data) {
        if ($data === '') {
            return;
        }
        if ($this->ignore_lf_token) {
            if ($data[0] === "\n") {
                $data = substr($data, 1);
                if ($data === false) {
                    return;
                }
            }
        }
        $text = $this->dom->createTextNode($data);
        $this->appendToRealParent($text);
    }

    
    private function insertComment($data) {
        $comment = $this->dom->createComment($data);
        $this->appendToRealParent($comment);
    }

    
    private function appendToRealParent($node) {
        
        
        if (
            !$this->foster_parent ||
            !in_array(
                end($this->stack)->tagName,
                ['table', 'tbody', 'tfoot', 'thead', 'tr']
            )
        ) {
            end($this->stack)->appendChild($node);
        } else {
            $this->fosterParent($node);
        }
    }

    
    private function elementInScope($el, $scope = self::SCOPE) {
        if (is_array($el)) {
            foreach($el as $element) {
                if ($this->elementInScope($element, $scope)) {
                    return true;
                }
            }

            return false;
        }

        $leng = count($this->stack);

        for ($n = 0; $n < $leng; $n++) {
            
            $node = $this->stack[$leng - 1 - $n];

            if ($node->tagName === $el) {
                
                return true;

                
                
                

            
            } elseif ($node->tagName === 'table' || $node->tagName === 'html') {
                return false;

            
            } elseif ($scope !== self::SCOPE_TABLE &&
            (in_array($node->tagName, ['applet', 'caption', 'td',
                'th', 'button', 'marquee', 'object']) ||
                $node->tagName === 'foreignObject' && $node->namespaceURI === self::NS_SVG)) {
                return false;


            
            } elseif ($scope === self::SCOPE_LISTITEM && in_array($node->tagName, ['ol', 'ul'])) {
                return false;
            }

            
        }

        
        return null;
    }

    
    private function reconstructActiveFormattingElements() {
        
        $formatting_elements = count($this->a_formatting);

        if ($formatting_elements === 0) {
            return false;
        }

        
        $entry = end($this->a_formatting);

        
        if ($entry === self::MARKER || in_array($entry, $this->stack, true)) {
            return false;
        }

        for ($a = $formatting_elements - 1; $a >= 0; true) {
            
            if ($a === 0) {
                $step_seven = false;
                break;
            }

            
            $a--;
            $entry = $this->a_formatting[$a];

            
            if ($entry === self::MARKER || in_array($entry, $this->stack, true)) {
                break;
            }
        }

        while (true) {
            
            if (isset($step_seven) && $step_seven === true) {
                $a++;
                $entry = $this->a_formatting[$a];
            }

            
            $clone = $entry->cloneNode();

            
            $this->appendToRealParent($clone);
            $this->stack[] = $clone;

            
            $this->a_formatting[$a] = $clone;

            
            if (end($this->a_formatting) !== $clone) {
                $step_seven = true;
            } else {
                break;
            }
        }

        
        return true;
    }

    
    private function clearTheActiveFormattingElementsUpToTheLastMarker() {
        

        while (true) {
            
            $entry = end($this->a_formatting);

            
            array_pop($this->a_formatting);

            
            if ($entry === self::MARKER) {
                break;
            }
        }
    }

    
    private function generateImpliedEndTags($exclude = []) {
        
        $node = end($this->stack);
        $elements = array_diff(['dc', 'dd', 'ds', 'dt', 'li', 'p', 'td', 'th', 'tr'], $exclude);

        while (in_array(end($this->stack)->tagName, $elements)) {
            array_pop($this->stack);
        }
    }

    
    private function getElementCategory($node) {
        if (!is_object($node)) {
            debug_print_backtrace();
        }
        $name = $node->tagName;
        if (in_array($name, $this->special)) {
            return self::SPECIAL;
        } elseif (in_array($name, $this->scoping)) {
            return self::SCOPING;
        } elseif (in_array($name, $this->formatting)) {
            return self::FORMATTING;
        } else {
            return self::PHRASING;
        }
    }

    
    private function clearStackToTableContext($elements) {
        
        while (true) {
            $name = end($this->stack)->tagName;

            if (in_array($name, $elements)) {
                break;
            } else {
                array_pop($this->stack);
            }
        }
    }

    
    private function resetInsertionMode($context = null) {
        
        $last = false;
        $leng = count($this->stack);

        for ($n = $leng - 1; $n >= 0; $n--) {
            
            $node = $this->stack[$n];

            
            if ($this->stack[0]->isSameNode($node)) {
                $last = true;
                $node = $context;
            }

            
            if ($node->tagName === 'select') {
                $this->mode = self::IN_SELECT;
                break;

            
            } elseif ($node->tagName === 'td' || $node->nodeName === 'th') {
                $this->mode = self::IN_CELL;
                break;

            
            } elseif ($node->tagName === 'tr') {
                $this->mode = self::IN_ROW;
                break;

            
            } elseif (in_array($node->tagName, ['tbody', 'thead', 'tfoot'])) {
                $this->mode = self::IN_TABLE_BODY;
                break;

            
            } elseif ($node->tagName === 'caption') {
                $this->mode = self::IN_CAPTION;
                break;

            
            } elseif ($node->tagName === 'colgroup') {
                $this->mode = self::IN_COLUMN_GROUP;
                break;

            
            } elseif ($node->tagName === 'table') {
                $this->mode = self::IN_TABLE;
                break;

            
            } elseif ($node->namespaceURI === self::NS_SVG ||
            $node->namespaceURI === self::NS_MATHML) {
                $this->mode = self::IN_FOREIGN_CONTENT;
                $this->secondary_mode = self::IN_BODY;
                break;

            
            } elseif ($node->tagName === 'head') {
                $this->mode = self::IN_BODY;
                break;

            
            } elseif ($node->tagName === 'body') {
                $this->mode = self::IN_BODY;
                break;

            
            } elseif ($node->tagName === 'frameset') {
                $this->mode = self::IN_FRAMESET;
                break;

            
            } elseif ($node->tagName === 'html') {
                $this->mode = ($this->head_pointer === null)
                    ? self::BEFORE_HEAD
                    : self::AFTER_HEAD;

                break;

            
            } elseif ($last) {
                $this->mode = self::IN_BODY;
                break;
            }
        }
    }

    
    private function closeCell() {
        
        foreach (['td', 'th'] as $cell) {
            if ($this->elementInScope($cell, self::SCOPE_TABLE)) {
                $this->emitToken([
                    'name' => $cell,
                    'type' => HTML5_Tokenizer::ENDTAG
                ]);

                break;
            }
        }
    }

    
    private function processWithRulesFor($token, $mode) {
        
        $this->emitToken($token, $mode);
    }

    
    private function insertCDATAElement($token) {
        $this->insertElement($token);
        $this->original_mode = $this->mode;
        $this->mode = self::IN_CDATA_RCDATA;
        $this->content_model = HTML5_Tokenizer::CDATA;
    }

    
    private function insertRCDATAElement($token) {
        $this->insertElement($token);
        $this->original_mode = $this->mode;
        $this->mode = self::IN_CDATA_RCDATA;
        $this->content_model = HTML5_Tokenizer::RCDATA;
    }

    
    private function getAttr($token, $key) {
        if (!isset($token['attr'])) {
            return false;
        }
        $ret = false;
        foreach ($token['attr'] as $keypair) {
            if ($keypair['name'] === $key) {
                $ret = $keypair['value'];
            }
        }
        return $ret;
    }

    
    private function getCurrentTable() {
        
        for ($i = count($this->stack) - 1; $i >= 0; $i--) {
            if ($this->stack[$i]->tagName === 'table') {
                return $this->stack[$i];
            }
        }
        return $this->stack[0];
    }

    
    private function getFosterParent() {
        
        for ($n = count($this->stack) - 1; $n >= 0; $n--) {
            if ($this->stack[$n]->tagName === 'table') {
                $table = $this->stack[$n];
                break;
            }
        }

        if (isset($table) && $table->parentNode !== null) {
            return $table->parentNode;

        } elseif (!isset($table)) {
            return $this->stack[0];

        } elseif (isset($table) && ($table->parentNode === null ||
        $table->parentNode->nodeType !== XML_ELEMENT_NODE)) {
            return $this->stack[$n - 1];
        }

        return null;
    }

    
    public function fosterParent($node) {
        $foster_parent = $this->getFosterParent();
        $table = $this->getCurrentTable(); 
        
        
        if ($table->tagName === 'table' && $table->parentNode->isSameNode($foster_parent)) {
            $foster_parent->insertBefore($node, $table);
        } else {
            $foster_parent->appendChild($node);
        }
    }

    
    private function printStack() {
        $names = [];
        foreach ($this->stack as $i => $element) {
            $names[] = $element->tagName;
        }
        echo "  -> stack [" . implode(', ', $names) . "]\n";
    }

    
    private function printActiveFormattingElements() {
        if (!$this->a_formatting) {
            return;
        }
        $names = [];
        foreach ($this->a_formatting as $node) {
            if ($node === self::MARKER) {
                $names[] = 'MARKER';
            } else {
                $names[] = $node->tagName;
            }
        }
        echo "  -> active formatting [" . implode(', ', $names) . "]\n";
    }

    
    public function currentTableIsTainted() {
        return !empty($this->getCurrentTable()->tainted);
    }

    
    public function setupContext($context = null) {
        $this->fragment = true;
        if ($context) {
            $context = $this->dom->createElementNS(self::NS_HTML, $context);
            
            switch ($context->tagName) {
                case 'title': case 'textarea':
                    $this->content_model = HTML5_Tokenizer::RCDATA;
                    break;
                case 'style': case 'script': case 'xmp': case 'iframe':
                case 'noembed': case 'noframes':
                    $this->content_model = HTML5_Tokenizer::CDATA;
                    break;
                case 'noscript':
                    
                    $this->content_model = HTML5_Tokenizer::CDATA;
                    break;
                case 'plaintext':
                    $this->content_model = HTML5_Tokenizer::PLAINTEXT;
                    break;
            }
            
            $root = $this->dom->createElementNS(self::NS_HTML, 'html');
            $this->root = $root;
            
            $this->dom->appendChild($root);
            
            $this->stack = [$root];
            
            $this->resetInsertionMode($context);
            
            $node = $context;
            do {
                if ($node->tagName === 'form') {
                    $this->form_pointer = $node;
                    break;
                }
            } while ($node = $node->parentNode);
        }
    }

    
    public function adjustMathMLAttributes($token) {
        foreach ($token['attr'] as &$kp) {
            if ($kp['name'] === 'definitionurl') {
                $kp['name'] = 'definitionURL';
            }
        }
        return $token;
    }

    
    public function adjustSVGAttributes($token) {
        static $lookup = [
            'attributename' => 'attributeName',
            'attributetype' => 'attributeType',
            'basefrequency' => 'baseFrequency',
            'baseprofile' => 'baseProfile',
            'calcmode' => 'calcMode',
            'clippathunits' => 'clipPathUnits',
            'contentscripttype' => 'contentScriptType',
            'contentstyletype' => 'contentStyleType',
            'diffuseconstant' => 'diffuseConstant',
            'edgemode' => 'edgeMode',
            'externalresourcesrequired' => 'externalResourcesRequired',
            'filterres' => 'filterRes',
            'filterunits' => 'filterUnits',
            'glyphref' => 'glyphRef',
            'gradienttransform' => 'gradientTransform',
            'gradientunits' => 'gradientUnits',
            'kernelmatrix' => 'kernelMatrix',
            'kernelunitlength' => 'kernelUnitLength',
            'keypoints' => 'keyPoints',
            'keysplines' => 'keySplines',
            'keytimes' => 'keyTimes',
            'lengthadjust' => 'lengthAdjust',
            'limitingconeangle' => 'limitingConeAngle',
            'markerheight' => 'markerHeight',
            'markerunits' => 'markerUnits',
            'markerwidth' => 'markerWidth',
            'maskcontentunits' => 'maskContentUnits',
            'maskunits' => 'maskUnits',
            'numoctaves' => 'numOctaves',
            'pathlength' => 'pathLength',
            'patterncontentunits' => 'patternContentUnits',
            'patterntransform' => 'patternTransform',
            'patternunits' => 'patternUnits',
            'pointsatx' => 'pointsAtX',
            'pointsaty' => 'pointsAtY',
            'pointsatz' => 'pointsAtZ',
            'preservealpha' => 'preserveAlpha',
            'preserveaspectratio' => 'preserveAspectRatio',
            'primitiveunits' => 'primitiveUnits',
            'refx' => 'refX',
            'refy' => 'refY',
            'repeatcount' => 'repeatCount',
            'repeatdur' => 'repeatDur',
            'requiredextensions' => 'requiredExtensions',
            'requiredfeatures' => 'requiredFeatures',
            'specularconstant' => 'specularConstant',
            'specularexponent' => 'specularExponent',
            'spreadmethod' => 'spreadMethod',
            'startoffset' => 'startOffset',
            'stddeviation' => 'stdDeviation',
            'stitchtiles' => 'stitchTiles',
            'surfacescale' => 'surfaceScale',
            'systemlanguage' => 'systemLanguage',
            'tablevalues' => 'tableValues',
            'targetx' => 'targetX',
            'targety' => 'targetY',
            'textlength' => 'textLength',
            'viewbox' => 'viewBox',
            'viewtarget' => 'viewTarget',
            'xchannelselector' => 'xChannelSelector',
            'ychannelselector' => 'yChannelSelector',
            'zoomandpan' => 'zoomAndPan',
        ];
        foreach ($token['attr'] as &$kp) {
            if (isset($lookup[$kp['name']])) {
                $kp['name'] = $lookup[$kp['name']];
            }
        }
        return $token;
    }

    
    public function adjustForeignAttributes($token) {
        static $lookup = [
            'xlink:actuate' => ['xlink', 'actuate', self::NS_XLINK],
            'xlink:arcrole' => ['xlink', 'arcrole', self::NS_XLINK],
            'xlink:href' => ['xlink', 'href', self::NS_XLINK],
            'xlink:role' => ['xlink', 'role', self::NS_XLINK],
            'xlink:show' => ['xlink', 'show', self::NS_XLINK],
            'xlink:title' => ['xlink', 'title', self::NS_XLINK],
            'xlink:type' => ['xlink', 'type', self::NS_XLINK],
            'xml:base' => ['xml', 'base', self::NS_XML],
            'xml:lang' => ['xml', 'lang', self::NS_XML],
            'xml:space' => ['xml', 'space', self::NS_XML],
            'xmlns' => [null, 'xmlns', self::NS_XMLNS],
            'xmlns:xlink' => ['xmlns', 'xlink', self::NS_XMLNS],
        ];
        foreach ($token['attr'] as &$kp) {
            if (isset($lookup[$kp['name']])) {
                $kp['name'] = $lookup[$kp['name']];
            }
        }
        return $token;
    }

    
    public function insertForeignElement($token, $namespaceURI) {
        $el = $this->dom->createElementNS($namespaceURI, $token['name']);

        if (!empty($token['attr'])) {
            foreach ($token['attr'] as $kp) {
                $attr = $kp['name'];
                if (is_array($attr)) {
                    $ns = $attr[2];
                    $attr = $attr[1];
                } else {
                    $ns = self::NS_HTML;
                }
                if (!$el->hasAttributeNS($ns, $attr)) {
                    
                    if ($ns === self::NS_XLINK) {
                        $el->setAttribute('xlink:'.$attr, $kp['value']);
                    } elseif ($ns === self::NS_HTML) {
                        
                        $el->setAttribute($attr, $kp['value']);
                    } else {
                        $el->setAttributeNS($ns, $attr, $kp['value']);
                    }
                }
            }
        }
        $this->appendToRealParent($el);
        $this->stack[] = $el;
        
        
    }

    
    public function save() {
        $this->dom->normalize();
        if (!$this->fragment) {
            return $this->dom;
        } else {
            if ($this->root) {
                return $this->root->childNodes;
            } else {
                return $this->dom->childNodes;
            }
        }
    }
}

