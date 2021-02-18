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
	
	public $file;
	public $folder;
	
	public $route;
	
	public $url;
	public $previous;
		
	public $reload;
	public $original;

	public function setInit() {
		$this -> setFile();
		$this -> setFolder();
		$this -> setUrl();
		$this -> original = $this -> url;
	}
	
	public function setFromString() {
		$this -> setPathArray();
		$this -> setFile();
		$this -> setFolder();
		$this -> setUrl();
	}
	
	public function setFromArray() {
		$this -> path['array'] = Objects::combine($this -> path['array']);
		$this -> setFile();
		$this -> setPathString();
		$this -> setFolder();
		$this -> setUrl();
	}
	
	public function setPathArray() {
		$this -> path['array'] = Strings::split($this -> path['string'], '\/', true);
	}
	
	public function setPathString() {
		$this -> path['string'] = !empty($this -> path['array']) ? Strings::join($this -> path['array'], '/') . (!$this -> file ? '/' : null) : null;
		$this -> path['string'] = preg_replace('/^\/+/ui', null, $this -> path['string']);
		$this -> path['string'] = preg_replace('/\/+/ui', '/', $this -> path['string']);
	}
	
	public function setFile() {
		$this -> file = Url::parseFile( Objects::last($this -> path['array'], 'value') );
		if (!$this -> file['extension']) {
			$this -> file = [];
		}
	}
	
	public function setFolder() {
		$this -> folder = Strings::find($this -> path['string'], '/', -1);
		if (!$this -> path['array'] && !$this -> file) {
			$this -> folder = true;
		}
	}
	
	public function setRoute() {
		$this -> route = $this -> path['array'];
	}
	
	public function addRoute($data) {
		$this -> route[] = $data;
	}
	
	public function addPathArray($data) {
		$this -> path['array'][] = $data;
	}
	
	public function setUrl() {
		$this -> url = $this -> domain . $this -> path['base'] . $this -> path['string'] . $this -> query['string'];
	}
	
}

?>