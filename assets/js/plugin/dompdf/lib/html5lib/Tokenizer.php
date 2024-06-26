<?php









class HTML5_Tokenizer {
    
    protected $stream;

    
    private $tree;

    
    protected $content_model;

    
    protected $token;

    
    const PCDATA    = 0;
    const RCDATA    = 1;
    const CDATA     = 2;
    const PLAINTEXT = 3;

    
    
    
    const DOCTYPE        = 0;
    const STARTTAG       = 1;
    const ENDTAG         = 2;
    const COMMENT        = 3;
    const CHARACTER      = 4;
    const SPACECHARACTER = 5;
    const EOF            = 6;
    const PARSEERROR     = 7;

    
    const ALPHA       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const UPPER_ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const LOWER_ALPHA = 'abcdefghijklmnopqrstuvwxyz';
    const DIGIT       = '0123456789';
    const HEX         = '0123456789ABCDEFabcdef';
    const WHITESPACE  = "\t\n\x0c ";

    
    public function __construct($data, $builder = null) {
        $this->stream = new HTML5_InputStream($data);
        if (!$builder) {
            $this->tree = new HTML5_TreeBuilder;
        } else {
            $this->tree = $builder;
        }
        $this->content_model = self::PCDATA;
    }

    
    public function parseFragment($context = null) {
        $this->tree->setupContext($context);
        if ($this->tree->content_model) {
            $this->content_model = $this->tree->content_model;
            $this->tree->content_model = null;
        }
        $this->parse();
    }

    
    
    
    public function parse() {
        
        $state = 'data';
        
        $lastFourChars = '';
        
        $escape = false;
        
        while($state !== null) {

            

            switch($state) {
                case 'data':

                    
                    $char = $this->stream->char();
                    $lastFourChars .= $char;
                    if (strlen($lastFourChars) > 4) {
                        $lastFourChars = substr($lastFourChars, -4);
                    }

                    
                    $hyp_cond =
                        !$escape &&
                        (
                            $this->content_model === self::RCDATA ||
                            $this->content_model === self::CDATA
                        );
                    $amp_cond =
                        !$escape &&
                        (
                            $this->content_model === self::PCDATA ||
                            $this->content_model === self::RCDATA
                        );
                    $lt_cond =
                        $this->content_model === self::PCDATA ||
                        (
                            (
                                $this->content_model === self::RCDATA ||
                                $this->content_model === self::CDATA
                             ) &&
                             !$escape
                        );
                    $gt_cond =
                        $escape &&
                        (
                            $this->content_model === self::RCDATA ||
                            $this->content_model === self::CDATA
                        );

                    if ($char === '&' && $amp_cond === true) {
                        
                        $state = 'character reference data';

                    } elseif (
                        $char === '-' &&
                        $hyp_cond === true &&
                        $lastFourChars === ''
                    ) {
                        
                        $escape = false;

                        
                        $this->emitToken([
                            'type' => self::CHARACTER,
                            'data' => '>'
                        ]);
                        

                    } elseif ($char === false) {
                        
                        $state = null;
                        $this->tree->emitToken([
                            'type' => self::EOF
                        ]);

                    } elseif ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        
                        
                        $chars = $this->stream->charsWhile(self::WHITESPACE);
                        $this->emitToken([
                            'type' => self::SPACECHARACTER,
                            'data' => $char . $chars
                        ]);
                        $lastFourChars .= $chars;
                        if (strlen($lastFourChars) > 4) {
                            $lastFourChars = substr($lastFourChars, -4);
                        }
                    } else {
                        

                        $mask = '';
                        if ($hyp_cond === true) {
                            $mask .= '-';
                        }
                        if ($amp_cond === true) {
                            $mask .= '&';
                        }
                        if ($lt_cond === true) {
                            $mask .= '<';
                        }
                        if ($gt_cond === true) {
                            $mask .= '>';
                        }

                        if ($mask === '') {
                            $chars = $this->stream->remainingChars();
                        } else {
                            $chars = $this->stream->charsUntil($mask);
                        }

                        $this->emitToken([
                            'type' => self::CHARACTER,
                            'data' => $char . $chars
                        ]);

                        $lastFourChars .= $chars;
                        if (strlen($lastFourChars) > 4) {
                            $lastFourChars = substr($lastFourChars, -4);
                        }

                        $state = 'data';
                    }
                break;

                case 'character reference data':
                    

                    
                    $entity = $this->consumeCharacterReference();

                    
                    
                    $this->emitToken([
                        'type' => self::CHARACTER,
                        'data' => $entity
                    ]);

                    
                    $state = 'data';
                break;

                case 'tag open':
                    $char = $this->stream->char();

                    switch ($this->content_model) {
                        case self::RCDATA:
                        case self::CDATA:
                            
                            

                            if ($char === '/') {
                                $state = 'close tag open';
                            } else {
                                $this->emitToken([
                                    'type' => self::CHARACTER,
                                    'data' => '<'
                                ]);

                                $this->stream->unget();

                                $state = 'data';
                            }
                        break;

                        case self::PCDATA:
                            
                            

                            if ($char === '!') {
                                
                                $state = 'markup declaration open';

                            } elseif ($char === '/') {
                                
                                $state = 'close tag open';

                            } elseif ('A' <= $char && $char <= 'Z') {
                                
                                $this->token = [
                                    'name'  => strtolower($char),
                                    'type'  => self::STARTTAG,
                                    'attr'  => []
                                ];

                                $state = 'tag name';

                            } elseif ('a' <= $char && $char <= 'z') {
                                
                                $this->token = [
                                    'name'  => $char,
                                    'type'  => self::STARTTAG,
                                    'attr'  => []
                                ];

                                $state = 'tag name';

                            } elseif ($char === '>') {
                                
                                $this->emitToken([
                                    'type' => self::PARSEERROR,
                                    'data' => 'expected-tag-name-but-got-right-bracket'
                                ]);
                                $this->emitToken([
                                    'type' => self::CHARACTER,
                                    'data' => '<>'
                                ]);

                                $state = 'data';

                            } elseif ($char === '?') {
                                
                                $this->emitToken([
                                    'type' => self::PARSEERROR,
                                    'data' => 'expected-tag-name-but-got-question-mark'
                                ]);
                                $this->token = [
                                    'data' => '?',
                                    'type' => self::COMMENT
                                ];
                                $state = 'bogus comment';

                            } else {
                                
                                $this->emitToken([
                                    'type' => self::PARSEERROR,
                                    'data' => 'expected-tag-name'
                                ]);
                                $this->emitToken([
                                    'type' => self::CHARACTER,
                                    'data' => '<'
                                ]);

                                $state = 'data';
                                $this->stream->unget();
                            }
                        break;
                    }
                break;

                case 'close tag open':
                    if (
                        $this->content_model === self::RCDATA ||
                        $this->content_model === self::CDATA
                    ) {
                        
                        $name = strtolower($this->stream->charsWhile(self::ALPHA));
                        $following = $this->stream->char();
                        $this->stream->unget();
                        if (
                            !$this->token ||
                            $this->token['name'] !== $name ||
                            $this->token['name'] === $name && !in_array($following, ["\x09", "\x0A", "\x0C", "\x20", "\x3E", "\x2F", false])
                        ) {
                            
                            

                            
                            
                            $this->emitToken([
                                'type' => self::CHARACTER,
                                'data' => '</' . $name
                            ]);

                            $state = 'data';
                        } else {
                            
                            
                            

                            
                            $this->token = [
                                'name'  => $name,
                                'type'  => self::ENDTAG
                            ];

                            
                            $state = 'tag name';
                        }
                    } elseif ($this->content_model === self::PCDATA) {
                        
                        $char = $this->stream->char();

                        if ('A' <= $char && $char <= 'Z') {
                            
                            $this->token = [
                                'name'  => strtolower($char),
                                'type'  => self::ENDTAG
                            ];

                            $state = 'tag name';

                        } elseif ('a' <= $char && $char <= 'z') {
                            
                            $this->token = [
                                'name'  => $char,
                                'type'  => self::ENDTAG
                            ];

                            $state = 'tag name';

                        } elseif ($char === '>') {
                            
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'expected-closing-tag-but-got-right-bracket'
                            ]);
                            $state = 'data';

                        } elseif ($char === false) {
                            
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'expected-closing-tag-but-got-eof'
                            ]);
                            $this->emitToken([
                                'type' => self::CHARACTER,
                                'data' => '</'
                            ]);

                            $this->stream->unget();
                            $state = 'data';

                        } else {
                            
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'expected-closing-tag-but-got-char'
                            ]);
                            $this->token = [
                                'data' => $char,
                                'type' => self::COMMENT
                            ];
                            $state = 'bogus comment';
                        }
                    }
                break;

                case 'tag name':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'before attribute name';

                    } elseif ($char === '/') {
                        
                        $state = 'self-closing start tag';

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ('A' <= $char && $char <= 'Z') {
                        
                        $chars = $this->stream->charsWhile(self::UPPER_ALPHA);

                        $this->token['name'] .= strtolower($char . $chars);
                        $state = 'tag name';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-tag-name'
                        ]);

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $chars = $this->stream->charsUntil("\t\n\x0C />" . self::UPPER_ALPHA);

                        $this->token['name'] .= $char . $chars;
                        $state = 'tag name';
                    }
                break;

                case 'before attribute name':
                    
                    $char = $this->stream->char();

                    
                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'before attribute name';

                    } elseif ($char === '/') {
                        
                        $state = 'self-closing start tag';

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ('A' <= $char && $char <= 'Z') {
                        
                        $this->token['attr'][] = [
                            'name'  => strtolower($char),
                            'value' => ''
                        ];

                        $state = 'attribute name';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'expected-attribute-name-but-got-eof'
                        ]);

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        if ($char === '"' || $char === "'" || $char === '<' || $char === '=') {
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'invalid-character-in-attribute-name'
                            ]);
                        }

                        
                        $this->token['attr'][] = [
                            'name'  => $char,
                            'value' => ''
                        ];

                        $state = 'attribute name';
                    }
                break;

                case 'attribute name':
                    
                    $char = $this->stream->char();

                    
                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'after attribute name';

                    } elseif ($char === '/') {
                        
                        $state = 'self-closing start tag';

                    } elseif ($char === '=') {
                        
                        $state = 'before attribute value';

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ('A' <= $char && $char <= 'Z') {
                        
                        $chars = $this->stream->charsWhile(self::UPPER_ALPHA);

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['name'] .= strtolower($char . $chars);

                        $state = 'attribute name';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-name'
                        ]);

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        if ($char === '"' || $char === "'" || $char === '<') {
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'invalid-character-in-attribute-name'
                            ]);
                        }

                        
                        $chars = $this->stream->charsUntil("\t\n\x0C /=>\"'" . self::UPPER_ALPHA);

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['name'] .= $char . $chars;

                        $state = 'attribute name';
                    }

                    
                    
                break;

                case 'after attribute name':
                    
                    $char = $this->stream->char();

                    
                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'after attribute name';

                    } elseif ($char === '/') {
                        
                        $state = 'self-closing start tag';

                    } elseif ($char === '=') {
                        
                        $state = 'before attribute value';

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ('A' <= $char && $char <= 'Z') {
                        
                        $this->token['attr'][] = [
                            'name'  => strtolower($char),
                            'value' => ''
                        ];

                        $state = 'attribute name';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'expected-end-of-tag-but-got-eof'
                        ]);

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        if ($char === '"' || $char === "'" || $char === "<") {
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'invalid-character-after-attribute-name'
                            ]);
                        }

                        
                        $this->token['attr'][] = [
                            'name'  => $char,
                            'value' => ''
                        ];

                        $state = 'attribute name';
                    }
                break;

                case 'before attribute value':
                    
                    $char = $this->stream->char();

                    
                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'before attribute value';

                    } elseif ($char === '"') {
                        
                        $state = 'attribute value (double-quoted)';

                    } elseif ($char === '&') {
                        
                        $this->stream->unget();
                        $state = 'attribute value (unquoted)';

                    } elseif ($char === '\'') {
                        
                        $state = 'attribute value (single-quoted)';

                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'expected-attribute-value-but-got-right-bracket'
                        ]);
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'expected-attribute-value-but-got-eof'
                        ]);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        if ($char === '=' || $char === '<') {
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'equals-in-unquoted-attribute-value'
                            ]);
                        }

                        
                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char;

                        $state = 'attribute value (unquoted)';
                    }
                break;

                case 'attribute value (double-quoted)':
                    
                    $char = $this->stream->char();

                    if ($char === '"') {
                        
                        $state = 'after attribute value (quoted)';

                    } elseif ($char === '&') {
                        
                        $this->characterReferenceInAttributeValue('"');

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-value-double-quote'
                        ]);

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $chars = $this->stream->charsUntil('"&');

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char . $chars;

                        $state = 'attribute value (double-quoted)';
                    }
                break;

                case 'attribute value (single-quoted)':
                    
                    $char = $this->stream->char();

                    if ($char === "'") {
                        
                        $state = 'after attribute value (quoted)';

                    } elseif ($char === '&') {
                        
                        $this->characterReferenceInAttributeValue("'");

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-value-single-quote'
                        ]);

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $chars = $this->stream->charsUntil("'&");

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char . $chars;

                        $state = 'attribute value (single-quoted)';
                    }
                break;

                case 'attribute value (unquoted)':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'before attribute name';

                    } elseif ($char === '&') {
                        
                        $this->characterReferenceInAttributeValue('>');

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-attribute-value-no-quotes'
                        ]);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        if ($char === '"' || $char === "'" || $char === '=' || $char == '<') {
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'unexpected-character-in-unquoted-attribute-value'
                            ]);
                        }

                        
                        $chars = $this->stream->charsUntil("\t\n\x0c &>\"'=");

                        $last = count($this->token['attr']) - 1;
                        $this->token['attr'][$last]['value'] .= $char . $chars;

                        $state = 'attribute value (unquoted)';
                    }
                break;

                case 'after attribute value (quoted)':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'before attribute name';

                    } elseif ($char === '/') {
                        
                        $state = 'self-closing start tag';

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-EOF-after-attribute-value'
                        ]);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-character-after-attribute-value'
                        ]);
                        $this->stream->unget();
                        $state = 'before attribute name';
                    }
                break;

                case 'self-closing start tag':
                    
                    $char = $this->stream->char();

                    if ($char === '>') {
                        
                        
                        $this->token['self-closing'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-eof-after-self-closing'
                        ]);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-character-after-self-closing'
                        ]);
                        $this->stream->unget();
                        $state = 'before attribute name';
                    }
                break;

                case 'bogus comment':
                    
                    
                    $this->token['data'] .= (string) $this->stream->charsUntil('>');
                    $this->stream->char();

                    $this->emitToken($this->token);

                    
                    $state = 'data';
                break;

                case 'markup declaration open':
                    
                    $hyphens = $this->stream->charsWhile('-', 2);
                    if ($hyphens === '-') {
                        $this->stream->unget();
                    }
                    if ($hyphens !== '--') {
                        $alpha = $this->stream->charsWhile(self::ALPHA, 7);
                    }

                    
                    if ($hyphens === '--') {
                        $state = 'comment start';
                        $this->token = [
                            'data' => '',
                            'type' => self::COMMENT
                        ];

                    
                    } elseif (strtoupper($alpha) === 'DOCTYPE') {
                        $state = 'DOCTYPE';

                    
                    

                    
                    } else {
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'expected-dashes-or-doctype'
                        ]);
                        $this->token = [
                            'data' => (string) $alpha,
                            'type' => self::COMMENT
                        ];
                        $state = 'bogus comment';
                    }
                break;

                case 'comment start':
                    
                    $char = $this->stream->char();

                    if ($char === '-') {
                        
                        $state = 'comment start dash';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'incorrect-comment'
                        ]);
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment'
                        ]);
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->token['data'] .= $char;
                        $state = 'comment';
                    }
                break;

                case 'comment start dash':
                    
                    $char = $this->stream->char();
                    if ($char === '-') {
                        
                        $state = 'comment end';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'incorrect-comment'
                        ]);
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment'
                        ]);
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        $this->token['data'] .= '-' . $char;
                        $state = 'comment';
                    }
                break;

                case 'comment':
                    
                    $char = $this->stream->char();

                    if ($char === '-') {
                        
                        $state = 'comment end dash';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment'
                        ]);
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $chars = $this->stream->charsUntil('-');

                        $this->token['data'] .= $char . $chars;
                    }
                break;

                case 'comment end dash':
                    
                    $char = $this->stream->char();

                    if ($char === '-') {
                        
                        $state = 'comment end';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment-end-dash'
                        ]);
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $this->token['data'] .= '-'.$char;
                        $state = 'comment';
                    }
                break;

                case 'comment end':
                    
                    $char = $this->stream->char();

                    if ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === '-') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-dash-after-double-dash-in-comment'
                        ]);
                        $this->token['data'] .= '-';

                    } elseif ($char === "\t" || $char === "\n" || $char === "\x0a" || $char === ' ') {
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-space-after-double-dash-in-comment'
                        ]);
                        $this->token['data'] .= '--' . $char;
                        $state = 'comment end space';

                    } elseif ($char === '!') {
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-bang-after-double-dash-in-comment'
                        ]);
                        $state = 'comment end bang';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment-double-dash'
                        ]);
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-comment'
                        ]);
                        $this->token['data'] .= '--'.$char;
                        $state = 'comment';
                    }
                break;

                case 'comment end bang':
                    $char = $this->stream->char();
                    if ($char === '>') {
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === "-") {
                        $this->token['data'] .= '--!';
                        $state = 'comment end dash';
                    } elseif ($char === false) {
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-comment-end-bang'
                        ]);
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        $this->token['data'] .= '--!' . $char;
                        $state = 'comment';
                    }
                break;

                case 'comment end space':
                    $char = $this->stream->char();
                    if ($char === '>') {
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === '-') {
                        $state = 'comment end dash';
                    } elseif ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        $this->token['data'] .= $char;
                    } elseif ($char === false) {
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-eof-in-comment-end-space',
                        ]);
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        $this->token['data'] .= $char;
                        $state = 'comment';
                    }
                break;

                case 'DOCTYPE':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'before DOCTYPE name';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'need-space-after-doctype-but-got-eof'
                        ]);
                        $this->emitToken([
                            'name' => '',
                            'type' => self::DOCTYPE,
                            'force-quirks' => true,
                            'error' => true
                        ]);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'need-space-after-doctype'
                        ]);
                        $this->stream->unget();
                        $state = 'before DOCTYPE name';
                    }
                break;

                case 'before DOCTYPE name':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        

                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'expected-doctype-name-but-got-right-bracket'
                        ]);
                        $this->emitToken([
                            'name' => '',
                            'type' => self::DOCTYPE,
                            'force-quirks' => true,
                            'error' => true
                        ]);

                        $state = 'data';

                    } elseif ('A' <= $char && $char <= 'Z') {
                        
                        $this->token = [
                            'name' => strtolower($char),
                            'type' => self::DOCTYPE,
                            'error' => true
                        ];

                        $state = 'DOCTYPE name';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'expected-doctype-name-but-got-eof'
                        ]);
                        $this->emitToken([
                            'name' => '',
                            'type' => self::DOCTYPE,
                            'force-quirks' => true,
                            'error' => true
                        ]);

                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $this->token = [
                            'name' => $char,
                            'type' => self::DOCTYPE,
                            'error' => true
                        ];

                        $state = 'DOCTYPE name';
                    }
                break;

                case 'DOCTYPE name':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                        $state = 'after DOCTYPE name';

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ('A' <= $char && $char <= 'Z') {
                        
                        $this->token['name'] .= strtolower($char);

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype-name'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                        $this->token['name'] .= $char;
                    }

                    
                    
                    
                    $this->token['error'] = ($this->token['name'] === 'HTML')
                        ? false
                        : true;
                break;

                case 'after DOCTYPE name':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        

                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        

                        $nextSix = strtoupper($char . $this->stream->charsWhile(self::ALPHA, 5));
                        if ($nextSix === 'PUBLIC') {
                            
                            $state = 'before DOCTYPE public identifier';

                        } elseif ($nextSix === 'SYSTEM') {
                            
                            $state = 'before DOCTYPE system identifier';

                        } else {
                            
                            $this->emitToken([
                                'type' => self::PARSEERROR,
                                'data' => 'expected-space-or-right-bracket-in-doctype'
                            ]);
                            $this->token['force-quirks'] = true;
                            $this->token['error'] = true;
                            $state = 'bogus DOCTYPE';
                        }
                    }
                break;

                case 'before DOCTYPE public identifier':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                    } elseif ($char === '"') {
                        
                        $this->token['public'] = '';
                        $state = 'DOCTYPE public identifier (double-quoted)';
                    } elseif ($char === "'") {
                        
                        $this->token['public'] = '';
                        $state = 'DOCTYPE public identifier (single-quoted)';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'DOCTYPE public identifier (double-quoted)':
                    
                    $char = $this->stream->char();

                    if ($char === '"') {
                        
                        $state = 'after DOCTYPE public identifier';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->token['public'] .= $char;
                    }
                break;

                case 'DOCTYPE public identifier (single-quoted)':
                    
                    $char = $this->stream->char();

                    if ($char === "'") {
                        
                        $state = 'after DOCTYPE public identifier';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->token['public'] .= $char;
                    }
                break;

                case 'after DOCTYPE public identifier':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                    } elseif ($char === '"') {
                        
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (double-quoted)';
                    } elseif ($char === "'") {
                        
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (single-quoted)';
                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'before DOCTYPE system identifier':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                    } elseif ($char === '"') {
                        
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (double-quoted)';
                    } elseif ($char === "'") {
                        
                        $this->token['system'] = '';
                        $state = 'DOCTYPE system identifier (single-quoted)';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'DOCTYPE system identifier (double-quoted)':
                    
                    $char = $this->stream->char();

                    if ($char === '"') {
                        
                        $state = 'after DOCTYPE system identifier';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->token['system'] .= $char;
                    }
                break;

                case 'DOCTYPE system identifier (single-quoted)':
                    
                    $char = $this->stream->char();

                    if ($char === "'") {
                        
                        $state = 'after DOCTYPE system identifier';
                    } elseif ($char === '>') {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-end-of-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->token['system'] .= $char;
                    }
                break;

                case 'after DOCTYPE system identifier':
                    
                    $char = $this->stream->char();

                    if ($char === "\t" || $char === "\n" || $char === "\x0c" || $char === ' ') {
                        
                    } elseif ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';
                    } elseif ($char === false) {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'eof-in-doctype'
                        ]);
                        $this->token['force-quirks'] = true;
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';
                    } else {
                        
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'unexpected-char-in-doctype'
                        ]);
                        $state = 'bogus DOCTYPE';
                    }
                break;

                case 'bogus DOCTYPE':
                    
                    $char = $this->stream->char();

                    if ($char === '>') {
                        
                        $this->emitToken($this->token);
                        $state = 'data';

                    } elseif ($char === false) {
                        
                        $this->emitToken($this->token);
                        $this->stream->unget();
                        $state = 'data';

                    } else {
                        
                    }
                break;

                
            }
        }
    }

    
    public function save() {
        return $this->tree->save();
    }

    
    public function getTree()
    {
        return $this->tree;
    }


    
    public function stream() {
        return $this->stream;
    }

    
    private function consumeCharacterReference($allowed = false, $inattr = false) {
        
        
        

        
        $chars = $this->stream->char();

        

        if (
            $chars[0] === "\x09" ||
            $chars[0] === "\x0A" ||
            $chars[0] === "\x0C" ||
            $chars[0] === "\x20" ||
            $chars[0] === '<' ||
            $chars[0] === '&' ||
            $chars === false ||
            $chars[0] === $allowed
        ) {
            
            
            $this->stream->unget();
            return '&';
        } elseif ($chars[0] === '#') {
            
            
            
            $chars .= $this->stream->char();
            if (isset($chars[1]) && ($chars[1] === 'x' || $chars[1] === 'X')) {
                
                
                
                
                $char_class = self::HEX;
                
                $hex = true;
            } else {
                
                
                $chars = $chars[0];
                $this->stream->unget();
                
                $char_class = self::DIGIT;
                
                $hex = false;
            }

            
            $consumed = $this->stream->charsWhile($char_class);
            if ($consumed === '' || $consumed === false) {
                
                $this->emitToken([
                    'type' => self::PARSEERROR,
                    'data' => 'expected-numeric-entity'
                ]);
                return '&' . $chars;
            } else {
                
                if ($this->stream->char() !== ';') {
                    $this->stream->unget();
                    $this->emitToken([
                        'type' => self::PARSEERROR,
                        'data' => 'numeric-entity-without-semicolon'
                    ]);
                }

                
                $codepoint = $hex ? hexdec($consumed) : (int) $consumed;

                
                $new_codepoint = HTML5_Data::getRealCodepoint($codepoint);
                if ($new_codepoint) {
                    $this->emitToken([
                        'type' => self::PARSEERROR,
                        'data' => 'illegal-windows-1252-entity'
                    ]);
                    return HTML5_Data::utf8chr($new_codepoint);
                } else {
                    
                    if ($codepoint > 0x10FFFF) {
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'overlong-character-entity' 
                        ]);
                        return "\xEF\xBF\xBD";
                    }
                    
                    
                    if (
                        $codepoint >= 0x0000 && $codepoint <= 0x0008 ||
                        $codepoint === 0x000B ||
                        $codepoint >= 0x000E && $codepoint <= 0x001F ||
                        $codepoint >= 0x007F && $codepoint <= 0x009F ||
                        $codepoint >= 0xD800 && $codepoint <= 0xDFFF ||
                        $codepoint >= 0xFDD0 && $codepoint <= 0xFDEF ||
                        ($codepoint & 0xFFFE) === 0xFFFE ||
                        $codepoint == 0x10FFFF || $codepoint == 0x10FFFE
                    ) {
                        $this->emitToken([
                            'type' => self::PARSEERROR,
                            'data' => 'illegal-codepoint-for-numeric-entity'
                        ]);
                    }
                    return HTML5_Data::utf8chr($codepoint);
                }
            }
        } else {
            

            
            
            

            $refs = HTML5_Data::getNamedCharacterReferences();

            
            
            
            $codepoint = false;
            $char = $chars;
            while ($char !== false && isset($refs[$char])) {
                $refs = $refs[$char];
                if (isset($refs['codepoint'])) {
                    $id = $chars;
                    $codepoint = $refs['codepoint'];
                }
                $chars .= $char = $this->stream->char();
            }

            
            
            
            
            $this->stream->unget();
            if ($char !== false) {
                $chars = substr($chars, 0, -1);
            }

            
            if (!$codepoint) {
                $this->emitToken([
                    'type' => self::PARSEERROR,
                    'data' => 'expected-named-entity'
                ]);
                return '&' . $chars;
            }

            
            $semicolon = true;
            if (substr($id, -1) !== ';') {
                $this->emitToken([
                    'type' => self::PARSEERROR,
                    'data' => 'named-entity-without-semicolon'
                ]);
                $semicolon = false;
            }

            
            if ($inattr && !$semicolon) {
                
                if (strlen($chars) > strlen($id)) {
                    $next = substr($chars, strlen($id), 1);
                } else {
                    $next = $this->stream->char();
                    $this->stream->unget();
                }
                if (
                    '0' <= $next && $next <= '9' ||
                    'A' <= $next && $next <= 'Z' ||
                    'a' <= $next && $next <= 'z'
                ) {
                    return '&' . $chars;
                }
            }

            
            return HTML5_Data::utf8chr($codepoint) . substr($chars, strlen($id));
        }
    }

    
    private function characterReferenceInAttributeValue($allowed = false) {
        
        $entity = $this->consumeCharacterReference($allowed, true);

        
        $char = (!$entity)
            ? '&'
            : $entity;

        $last = count($this->token['attr']) - 1;
        $this->token['attr'][$last]['value'] .= $char;

        
    }

    
    protected function emitToken($token, $checkStream = true, $dry = false) {
        if ($checkStream === true) {
            
            while ($this->stream->errors) {
                $this->emitToken(array_shift($this->stream->errors), false);
            }
        }
        if ($token['type'] === self::ENDTAG && !empty($token['attr'])) {
            for ($i = 0; $i < count($token['attr']); $i++) {
                $this->emitToken([
                    'type' => self::PARSEERROR,
                    'data' => 'attributes-in-end-tag'
                ]);
            }
        }
        if ($token['type'] === self::ENDTAG && !empty($token['self-closing'])) {
            $this->emitToken([
                'type' => self::PARSEERROR,
                'data' => 'self-closing-flag-on-end-tag',
            ]);
        }
        if ($token['type'] === self::STARTTAG) {
            
            $hash = [];
            foreach ($token['attr'] as $keypair) {
                if (isset($hash[$keypair['name']])) {
                    $this->emitToken([
                        'type' => self::PARSEERROR,
                        'data' => 'duplicate-attribute',
                    ]);
                } else {
                    $hash[$keypair['name']] = $keypair['value'];
                }
            }
        }

        if ($dry === false) {
            
            $this->tree->emitToken($token);
        }

        if ($dry === false && is_int($this->tree->content_model)) {
            $this->content_model = $this->tree->content_model;
            $this->tree->content_model = null;

        } elseif ($token['type'] === self::ENDTAG) {
            $this->content_model = self::PCDATA;
        }
    }
}

