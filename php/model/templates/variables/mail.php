<?php

namespace is\Model\Templates\Variables;

use is\Model\Templates\Variable;

class Mail extends Variable {
	
	public function init() {
		
		$url = $this -> data[0];
		$class = $this -> data[1] ? ' class="' . $this -> data[1] . '"' : null;
		
		if (!$this -> data[2]) {
			$this -> data[2] = $url;
		}
		
		$subject = $this -> data[3] ? '?subject=' . $this -> data[3] : null;
		
		return '<a href="mailto:' . $url . $subject . '" alt="' . $this -> data[2] . '"' . $class . '>' . $this -> data[2] . '</a>';
		
	}
	
}

?>