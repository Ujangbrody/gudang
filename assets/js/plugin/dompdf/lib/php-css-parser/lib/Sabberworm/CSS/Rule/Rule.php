<?php

namespace Sabberworm\CSS\Rule;

use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Value;


class Rule implements Renderable, Commentable {

	private $sRule;
	private $mValue;
	private $bIsImportant;
	private $aIeHack;
	protected $iLineNo;
	protected $aComments;

	public function __construct($sRule, $iLineNo = 0) {
		$this->sRule = $sRule;
		$this->mValue = null;
		$this->bIsImportant = false;
		$this->aIeHack = array();
		$this->iLineNo = $iLineNo;
		$this->aComments = array();
	}

	public static function parse(ParserState $oParserState) {
		$aComments = $oParserState->consumeWhiteSpace();
		$oRule = new Rule($oParserState->parseIdentifier(), $oParserState->currentLine());
		$oRule->setComments($aComments);
		$oRule->addComments($oParserState->consumeWhiteSpace());
		$oParserState->consume(':');
		$oValue = Value::parseValue($oParserState, self::listDelimiterForRule($oRule->getRule()));
		$oRule->setValue($oValue);
		if ($oParserState->getSettings()->bLenientParsing) {
			while ($oParserState->comes('\\')) {
				$oParserState->consume('\\');
				$oRule->addIeHack($oParserState->consume());
				$oParserState->consumeWhiteSpace();
			}
		}
		$oParserState->consumeWhiteSpace();
		if ($oParserState->comes('!')) {
			$oParserState->consume('!');
			$oParserState->consumeWhiteSpace();
			$oParserState->consume('important');
			$oRule->setIsImportant(true);
		}
		$oParserState->consumeWhiteSpace();
		while ($oParserState->comes(';')) {
			$oParserState->consume(';');
		}
		$oParserState->consumeWhiteSpace();

		return $oRule;
	}

	private static function listDelimiterForRule($sRule) {
		if (preg_match('/^font($|-)/', $sRule)) {
			return array(',', '/', ' ');
		}
		return array(',', ' ', '/');
	}

	
	public function getLineNo() {
		return $this->iLineNo;
	}

	public function setRule($sRule) {
		$this->sRule = $sRule;
	}

	public function getRule() {
		return $this->sRule;
	}

	public function getValue() {
		return $this->mValue;
	}

	public function setValue($mValue) {
		$this->mValue = $mValue;
	}

	
	public function setValues($aSpaceSeparatedValues) {
		$oSpaceSeparatedList = null;
		if (count($aSpaceSeparatedValues) > 1) {
			$oSpaceSeparatedList = new RuleValueList(' ', $this->iLineNo);
		}
		foreach ($aSpaceSeparatedValues as $aCommaSeparatedValues) {
			$oCommaSeparatedList = null;
			if (count($aCommaSeparatedValues) > 1) {
				$oCommaSeparatedList = new RuleValueList(',', $this->iLineNo);
			}
			foreach ($aCommaSeparatedValues as $mValue) {
				if (!$oSpaceSeparatedList && !$oCommaSeparatedList) {
					$this->mValue = $mValue;
					return $mValue;
				}
				if ($oCommaSeparatedList) {
					$oCommaSeparatedList->addListComponent($mValue);
				} else {
					$oSpaceSeparatedList->addListComponent($mValue);
				}
			}
			if (!$oSpaceSeparatedList) {
				$this->mValue = $oCommaSeparatedList;
				return $oCommaSeparatedList;
			} else {
				$oSpaceSeparatedList->addListComponent($oCommaSeparatedList);
			}
		}
		$this->mValue = $oSpaceSeparatedList;
		return $oSpaceSeparatedList;
	}

	
	public function getValues() {
		if (!$this->mValue instanceof RuleValueList) {
			return array(array($this->mValue));
		}
		if ($this->mValue->getListSeparator() === ',') {
			return array($this->mValue->getListComponents());
		}
		$aResult = array();
		foreach ($this->mValue->getListComponents() as $mValue) {
			if (!$mValue instanceof RuleValueList || $mValue->getListSeparator() !== ',') {
				$aResult[] = array($mValue);
				continue;
			}
			if ($this->mValue->getListSeparator() === ' ' || count($aResult) === 0) {
				$aResult[] = array();
			}
			foreach ($mValue->getListComponents() as $mValue) {
				$aResult[count($aResult) - 1][] = $mValue;
			}
		}
		return $aResult;
	}

	
	public function addValue($mValue, $sType = ' ') {
		if (!is_array($mValue)) {
			$mValue = array($mValue);
		}
		if (!$this->mValue instanceof RuleValueList || $this->mValue->getListSeparator() !== $sType) {
			$mCurrentValue = $this->mValue;
			$this->mValue = new RuleValueList($sType, $this->iLineNo);
			if ($mCurrentValue) {
				$this->mValue->addListComponent($mCurrentValue);
			}
		}
		foreach ($mValue as $mValueItem) {
			$this->mValue->addListComponent($mValueItem);
		}
	}

	public function addIeHack($iModifier) {
		$this->aIeHack[] = $iModifier;
	}

	public function setIeHack(array $aModifiers) {
		$this->aIeHack = $aModifiers;
	}

	public function getIeHack() {
		return $this->aIeHack;
	}

	public function setIsImportant($bIsImportant) {
		$this->bIsImportant = $bIsImportant;
	}

	public function getIsImportant() {
		return $this->bIsImportant;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult = "{$this->sRule}:{$oOutputFormat->spaceAfterRuleName()}";
		if ($this->mValue instanceof Value) { 
			$sResult .= $this->mValue->render($oOutputFormat);
		} else {
			$sResult .= $this->mValue;
		}
		if (!empty($this->aIeHack)) {
			$sResult .= ' \\' . implode('\\', $this->aIeHack);
		}
		if ($this->bIsImportant) {
			$sResult .= ' !important';
		}
		$sResult .= ';';
		return $sResult;
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
