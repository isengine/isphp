<?php

namespace is\Model\Templates\Variables;

use is\Model\Templates\Variable;

class Icon extends Variable {
	
	public function init() {
		
		return '<i class="' . $this -> data[0] . '" aria-hidden="true"></i>';
		
	}
	
}

?>