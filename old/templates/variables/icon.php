<?php

namespace is\Model\Templates\Variables;

class Icon extends Master {
	
	public function init() {
		
		return '<i class="' . $this -> data[0] . '" aria-hidden="true"></i>';
		
	}
	
}

?>