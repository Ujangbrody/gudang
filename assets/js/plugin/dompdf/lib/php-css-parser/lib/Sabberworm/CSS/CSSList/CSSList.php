<?php

namespace Sabberworm\CSS\CSSList;

use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\Value;


abstract class CSSList implements Renderable, Commentable {

	protected $aComments;
	protected $aContents;
	protected $iLineNo;

	public function __construct($iLineNo = 0) {
		$this->aComments = array();
		$this->aContents = array();
		$this->iLineNo = $iLineNo;
	}

	public static function parseList(ParserState $oParserState, CSSList $oList) {
		$bIsRoot = $oList instanceof Document;
		if(is_string($oParserState)) {
			$oParserState = new ParserState($oParserState);
		}
		$bLenientParsing = $oParserState->getSettings()->bLenientParsing;
		while(!$oParserState->isEnd()) {
			$comments = $oParserState->consumeWhiteSpace();
			$oListItem = null;
			if($bLenientParsing) {
				try {
					$oListItem = self::parseListItem($oParserState, $oList);
				} catch (UnexpectedTokenException $e) {
					$oListItem = false;
				}
			} else {
				$oListItem = self::parseListItem($oParserState, $oList);
			}
			if($oListItem === null) {
				
				return;
			}
			if($oListItem) {
				$oListItem->setComments($comments);
				$oList->append($oListItem);
			}
			$oParserState->consumeWhiteSpace();
		}
		if(!$bIsRoot && !$bLenientParsing) {
			throw new SourceException("Unexpected end of document", $oParserState->currentLine());
		}
	}

	private static function parseListItem(ParserState $oParserState, CSSList $oList) {
		$bIsRoot = $oList instanceof Document;
		if ($oParserState->comes('@')) {
			$oAtRule = self::parseAtRule($oParserState);
			if($oAtRule instanceof Charset) {
				if(!$bIsRoot) {
					throw new UnexpectedTokenException('@charset may only occur in root document', '', 'custom', $oParserState->currentLine());
				}
				if(count($oList->getContents()) > 0) {
					throw new UnexpectedTokenException('@charset must be the first parseable token in a document', '', 'custom', $oParserState->currentLine());
				}
				$oParserState->setCharset($oAtRule->getCharset()->getString());
			}
			return $oAtRule;
		} else if ($oParserState->comes('}')) {
			$oParserState->consume('}');
			if ($bIsRoot) {
				if ($oParserState->getSettings()->bLenientParsing) {
					while ($oParserState->comes('}')) $oParserState->consume('}');
					return DeclarationBlock::parse($oParserState);
				} else {
					throw new SourceException("Unopened {", $oParserState->currentLine());
				}
			} else {
				return null;
			}
		} else {
			return DeclarationBlock::parse($oParserState);
		}
	}

	private static function parseAtRule(ParserState $oParserState) {
		$oParserState->consume('@');
		$sIdentifier = $oParserState->parseIdentifier();
		$iIdentifierLineNum = $oParserState->currentLine();
		$oParserState->consumeWhiteSpace();
		if ($sIdentifier === 'import') {
			$oLocation = URL::parse($oParserState);
			$oParserState->consumeWhiteSpace();
			$sMediaQuery = null;
			if (!$oParserState->comes(';')) {
				$sMediaQuery = $oParserState->consumeUntil(';');
			}
			$oParserState->consume(';');
			return new Import($oLocation, $sMediaQuery, $iIdentifierLineNum);
		} else if ($sIdentifier === 'charset') {
			$sCharset = CSSString::parse($oParserState);
			$oParserState->consumeWhiteSpace();
			$oParserState->consume(';');
			return new Charset($sCharset, $iIdentifierLineNum);
		} else if (self::identifierIs($sIdentifier, 'keyframes')) {
			$oResult = new KeyFrame($iIdentifierLineNum);
			$oResult->setVendorKeyFrame($sIdentifier);
			$oResult->setAnimationName(trim($oParserState->consumeUntil('{', false, true)));
			CSSList::parseList($oParserState, $oResult);
			return $oResult;
		} else if ($sIdentifier === 'namespace') {
			$sPrefix = null;
			$mUrl = Value::parsePrimitiveValue($oParserState);
			if (!$oParserState->comes(';')) {
				$sPrefix = $mUrl;
				$mUrl = Value::parsePrimitiveValue($oParserState);
			}
			$oParserState->consume(';');
			if ($sPrefix !== null && !is_string($sPrefix)) {
				throw new UnexpectedTokenException('Wrong namespace prefix', $sPrefix, 'custom', $iIdentifierLineNum);
			}
			if (!($mUrl instanceof CSSString || $mUrl instanceof URL)) {
				throw new UnexpectedTokenException('Wrong namespace url of invalid type', $mUrl, 'custom', $iIdentifierLineNum);
			}
			return new CSSNamespace($mUrl, $sPrefix, $iIdentifierLineNum);
		} else {
			
			$sArgs = trim($oParserState->consumeUntil('{', false, true));
			if (substr_count($sArgs, "(") != substr_count($sArgs, ")")) {
				if($oParserState->getSettings()->bLenientParsing) {
					return NULL;
				} else {
					throw new SourceException("Unmatched brace count in media query", $oParserState->currentLine());
				}
			}
			$bUseRuleSet = true;
			foreach(explode('/', AtRule::BLOCK_RULES) as $sBlockRuleName) {
				if(self::identifierIs($sIdentifier, $sBlockRuleName)) {
					$bUseRuleSet = false;
					break;
				}
			}
			if($bUseRuleSet) {
				$oAtRule = new AtRuleSet($sIdentifier, $sArgs, $iIdentifierLineNum);
				RuleSet::parseRuleSet($oParserState, $oAtRule);
			} else {
				$oAtRule = new AtRuleBlockList($sIdentifier, $sArgs, $iIdentifierLineNum);
				CSSList::parseList($oParserState, $oAtRule);
			}
			return $oAtRule;
		}
	}

		
	private static function identifierIs($sIdentifier, $sMatch) {
		return (strcasecmp($sIdentifier, $sMatch) === 0)
			?: preg_match("/^(-\\w+-)?$sMatch$/i", $sIdentifier) === 1;
	}


	
	public function getLineNo() {
		return $this->iLineNo;
	}

	
	public function prepend($oItem) {
		array_unshift($this->aContents, $oItem);
	}

	
	public function append($oItem) {
		$this->aContents[] = $oItem;
	}

	
	public function splice($iOffset, $iLength = null, $mReplacement = null) {
		array_splice($this->aContents, $iOffset, $iLength, $mReplacement);
	}

	
	public function remove($oItemToRemove) {
		$iKey = array_search($oItemToRemove, $this->aContents, true);
		if ($iKey !== false) {
			unset($this->aContents[$iKey]);
			return true;
		}
		return false;
	}

	
	public function replace($oOldItem, $oNewItem) {
		$iKey = array_search($oOldItem, $this->aContents, true);
		if ($iKey !== false) {
			array_splice($this->aContents, $iKey, 1, $oNewItem);
			return true;
		}
		return false;
	}

	
	public function setContents(array $aContents) {
		$this->aContents = array();
		foreach ($aContents as $content) {
			$this->append($content);
		}
	}

	
	public function removeDeclarationBlockBySelector($mSelector, $bRemoveAll = false) {
		if ($mSelector instanceof DeclarationBlock) {
			$mSelector = $mSelector->getSelectors();
		}
		if (!is_array($mSelector)) {
			$mSelector = explode(',', $mSelector);
		}
		foreach ($mSelector as $iKey => &$mSel) {
			if (!($mSel instanceof Selector)) {
				$mSel = new Selector($mSel);
			}
		}
		foreach ($this->aContents as $iKey => $mItem) {
			if (!($mItem instanceof DeclarationBlock)) {
				continue;
			}
			if ($mItem->getSelectors() == $mSelector) {
				unset($this->aContents[$iKey]);
				if (!$bRemoveAll) {
					return;
				}
			}
		}
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult = '';
		$bIsFirst = true;
		$oNextLevel = $oOutputFormat;
		if(!$this->isRootList()) {
			$oNextLevel = $oOutputFormat->nextLevel();
		}
		foreach ($this->aContents as $oContent) {
			$sRendered = $oOutputFormat->safely(function() use ($oNextLevel, $oContent) {
				return $oContent->render($oNextLevel);
			});
			if($sRendered === null) {
				continue;
			}
			if($bIsFirst) {
				$bIsFirst = false;
				$sResult .= $oNextLevel->spaceBeforeBlocks();
			} else {
				$sResult .= $oNextLevel->spaceBetweenBlocks();
			}
			$sResult .= $sRendered;
		}

		if(!$bIsFirst) {
			
			$sResult .= $oOutputFormat->spaceAfterBlocks();
		}

		return $sResult;
	}
	
	
	public abstract function isRootList();

	public function getContents() {
		return $this->aContents;
	}

	
	public function addComments(array $aComments) {
		$this->aComments = array_merge($this->aComments, $aComments);
	}

	
	public function getComments() {
		return $this->aComments;
	}

	
	public function setComments(array $aComments) {
		$this->aComments = $aComments;
	}

}
