<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Model\Components\Path;
use is\Model\Globals\Uri;
use is\Parents\Data;

class Router extends Uri {
	
	public function reload() {
		
		// reload current path
		
		System::reload($this -> path, $this -> code, $this -> data);
		exit;
		
	}	
}

?>