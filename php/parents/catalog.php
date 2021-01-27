<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Objects;

class Catalog extends Collection {
	
	private $name;
	
	public function __construct(Collection &$name) {
		$this -> names = $name -> names;
		$this -> indexes = $name -> indexes;
		$this -> count = &$name -> count;
		$this -> data = &$name -> data;
	}
	
}

?>