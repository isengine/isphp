<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;

class Map extends Data {
	
	public $count;
	
	public function count($list) {
		$this -> count = [];
		foreach ($list as $item) {
			$item = $this -> convert($item);
			$item = Objects::unlast($item);
			
			$count = Objects::extract($this -> count, $item);
			$count++;
			
			$this -> count = Objects::inject($this -> count, $item, $count);
		}
		unset($item);
		return $this -> count;
	}
	
	public function build($list, $value = null) {
		$this -> reset();
		foreach ($list as $item) {
			$this -> addMap($item, $value);
		}
		unset($item);
	}
	
	public function getMap($name = null) {
		return $name ? Objects::extract($this -> data, $this -> convert($name)) : $this -> getData();
	}
	
	public function addMap($name, $value = null) {
		$this -> data = Objects::inject($this -> data, $this -> convert($name), $value);
	}
	
	public function removeMap($name) {
		$this -> map = Objects::delete($this -> map, $this -> convert($name));
	}
	
	
	public function convert($name) {
		return Strings::split($name, ':');
	}
	
	public function reset() {
		$this -> data = [];
	}
	
}

?>