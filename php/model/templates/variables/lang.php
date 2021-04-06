<?php

namespace is\Model\Templates\Variables;

use is\Helpers\Strings;
use is\Model\Templates\Template;
use is\Model\Templates\Variable;

class Lang extends Variable {
	
	public function init() {
		
		$template = Template::getInstance();
		return $template -> lang() -> get( Strings::join($this -> data, ':') );
		
	}
	
}

?>