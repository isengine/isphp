<?php

namespace is\Model\Templates\Variables;

use is\Helpers\Prepare;
use is\Model\Templates\Template;
use is\Model\Templates\Variable;

class Phone extends Variable {
	
	public function init() {
		
		$url = $this -> data[0];
		$class = $this -> data[1] ? ' class="' . $this -> data[1] . '"' : null;
		
		if (!$this -> data[2]) {
			$this -> data[2] = $url;
		}
		
		$template = Template::getInstance();
		$url = Prepare::phone($url, $template -> lang() -> get('lang'));
		
		return '<a href="tel:' . $url . '" alt="' . $this -> data[2] . '"' . $class . '>' . $this -> data[2] . '</a>';
		
	}
	
}

?>