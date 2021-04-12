<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Model\Parents\Globals;

class Datetime extends Globals {
	
	public $format;
	
	public function init() {
		
		$this -> format = [
			
			'yy' => 'Y',
			'mm' => 'm',
			'dd' => 'd',
			
			'y' => 'y',
			'm' => 'n',
			'd' => 'j',
			
			'hh' => 'H',
			'h' => 'G',
			
			'hour' => 'H',
			'min' => 'i',
			'sec' => 's',
			
			'absolute' => 'U'
			
		];
		
		foreach ($this -> format as $key => $item) {
			$this -> addData($key, $this -> convertFromSystem($item));
		}
		unset($key, $item);
		
	}
	
	public function getDatetime($format, $time) {
		return $this -> convertFromSystem($this -> format[$format], $time);
	}
	
	public function convertFromSystem($format, $time = null) {
		return date($format, System::set($time) ? $time : time());
	}
	
}

?>