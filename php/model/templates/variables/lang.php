<?php

namespace is\Model\Templates\Variables;

use is\Helpers\Strings;
use is\Model\Templates\Template;

class Lang extends Master {
	
	public function init() {
		
		$template = Template::getInstance();
		return $template -> lang() -> get( Strings::join($this -> data, ':') );
		
	}
	
}

?>