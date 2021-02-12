<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Url;
use is\Model\Globals;

class Uri extends Globals\Uri {
	
	public function setPathArray() {
		$this -> path['array'] = Strings::split($this -> path['string'], '\/', true);
	}
	
	public function setPathString() {
		$this -> path['string'] = !empty($this -> path['array']) ? Strings::join($this -> path['array'], '/') : null;
		if (!$this -> file['extension']) {
			 $this -> path['string'] .= '/';
			 $this -> file = [];
		}
	}
	
	public function setUrl() {
		$this -> url = $this -> domain . $this -> path['string'] . $this -> query['string'];
	}
	
}

?>