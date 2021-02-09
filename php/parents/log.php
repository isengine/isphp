<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Objects;

class Log extends Data {
	
	private $name;
	
	public function __construct($name = null) {
		
		if (!$name) {
			$date = new \DateTime();
			$name = Sessions::ipReal() . $date -> format('-Y.m.d-H.i.s.u');
			unset($date);
		}
		
		$this -> name = $name;
		
	}
	
	public function initialize($file) {
		
		if (!$name) {
			$date = new \DateTime();
			$name = $date -> format('Y.m.d-H.i.s.u-') . Sessions::ipReal();
			unset($date);
		}
		
		$this -> name = $name;
		
	}
	
	
	
	
}

?>