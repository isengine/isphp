<?php

namespace is\Model\Templates;

use is\Model\Parents\Data;
use is\Helpers\Local;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\System;
use is\Helpers\Prepare;
use is\Helpers\Match;
use is\Helpers\Paths;

abstract class Variable extends Data {
	
	public function __construct($data) {
		$this -> setData($data);
	}
	
	abstract public function init();
	
}

?>