<?php

namespace Sabberworm\CSS\Property;

use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Comment\Commentable;

interface AtRule extends Renderable, Commentable {
	
	const BLOCK_RULES = 'media/document/supports/region-style/font-feature-values';
	
	const SET_RULES = 'font-face/counter-style/page/swash/styleset/annotation';
	
	public function atRuleName();
	public function atRuleArgs();
}