<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Sessions;
use is\Helpers\Local;
use is\Model\Parents\Globals;

class Display extends Globals {
	
	public $buffer;
	public $splitter;
	public $wrapper;
	
	public function init() {
		$this -> buffer = null;
		$this -> data = [];
		$this -> splitter = "\n";
	}
	
	public function addBuffer($data) {
		$this -> buffer .= $data;
	}
	
	public function addDataToBuffer($data) {
		$this -> buffer .= Strings::join($data, $this -> splitter);
	}
	
	public function resetBuffer() {
		$buffer = null;
	}
	
	public function reset() {
		$this -> resetBuffer();
		$this -> resetData();
	}
	
	public function printBuffer() {
		$this -> render($this -> buffer);
	}
	
	public function printData() {
		$this -> render($this -> data);
	}
	
	public function render($data) {
		if (System::typeOf($data, 'iterable')) {
			foreach ($data as $item) {
				echo $item . $this -> splitter;
			}
			unset($item);
		} else {
			echo $data;
		}
	}
	
	public function dump($data, $before = '<pre>', $after = '</pre>') {
		//echo $before, var_export($data, 1), $after;
		echo $before, print_r($data, 1), $after;
	}
	
}

?>