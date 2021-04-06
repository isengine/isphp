<?php

namespace is\Model\Templates\Variables;

class Mail extends Master {
	
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