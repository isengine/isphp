<?php

namespace is\Model\Templates\Variables;

use is\Helpers\Strings;

class Url extends Master {
	
	public function init() {
		
		$url = $this -> data[0];
		$absolute = Strings::find($url, '//') === 0 ? ' target="_blank"' : null;
		$class = $this -> data[1] ? ' class="' . $this -> data[1] . '"' : null;
		
		return '<a href="' . $url . '" alt="' . $this -> data[2] . '"' . $class . $absolute . '>' . $this -> data[2] . '</a>';
		
	}
	
}

?>