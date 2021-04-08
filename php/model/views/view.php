<?php

namespace is\Model\Views;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Match;
use is\Helpers\Paths;
use is\Helpers\Prepare;

use is\Model\Parents\Singleton;

class View extends Singleton {
	
	// общие установки кэша
	
	public function add($name) {
		$n = Prepare::upperFirst($name);
		$ns = __NAMESPACE__ . '\\' . $n . '\\' . $n;
		$this -> data[$name] = new $ns;
	}
	
	public function get($type) {
		return $this -> data[$type];
	}
	
	public function reset($type) {
		$this -> deleteDataKey($type);
	}
	
}

?>