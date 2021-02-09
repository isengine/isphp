<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Objects;

abstract class Globals extends Singleton {
	
	abstract public function initialize();
	
	public function get($name) {
		return $this -> $name;
	}
	
	public function compare($name, $value) {
		return $this -> $name === $value;
	}
	
}

?>