<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Sessions;
use is\Helpers\Local;
use is\Parents\Globals;

class Display extends Globals {
	
	public $buffer;
	public $splitter;
	
	public function init() {
		$this -> reset();
		$this -> setSplitter();
	}
	
	public function reset() {
		$this -> resetBuffer();
		$this -> resetData();
	}
	
	public function render($data) {
		return System::typeOf($data, 'iterable') ? Strings::join($data, $this -> splitter) : $data;
	}
	
	public function setSplitter($splitter = "\n") {
		$this -> splitter = $splitter;
	}
	
	public function setBuffer($data) {
		$this -> buffer = $this -> render($data);
	}
	
	public function addBuffer($data) {
		$this -> buffer .= $this -> render($data);
	}
	
	public function resetBuffer() {
		$buffer = null;
	}
	
	public function printBuffer() {
		echo $this -> buffer;
	}
	
	public function printData() {
		echo $this -> render($this -> data);
	}
	
	public function dump($data, $before = '<pre>', $after = '</pre>') {
		//echo $before, var_export($data, 1), $after;
		echo $before, print_r($data, 1), $after;
	}
	
}

?>