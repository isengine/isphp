<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Model\Globals;

class Uri extends Globals\Uri {
	
	public $file;
	public $folder;
	
	public $language;
	
	public $url;
	public $previous;
		
	public $reload;
	public $original;
	
	public function init() {
		parent::init();
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
		$this -> path['array'] = Objects::reset($this -> path['array']);
		$this -> setFile();
		$this -> setPathString();
		$this -> setFolder();
		$this -> setUrl();
	}
	
	public function setPathArray() {
		//$this -> path['array'] = Strings::split($this -> path['string'], '\/');
		$this -> path['array'] = Objects::reset(Strings::split($this -> path['string'], '\/'));
	}
	
	public function setPathString() {
		$this -> path['string'] = !empty($this -> path['array']) ? Strings::join($this -> path['array'], '/') . (!$this -> file ? '/' : null) : null;
		$this -> path['string'] = preg_replace('/^\/+/ui', null, $this -> path['string']);
		$this -> path['string'] = preg_replace('/\/+/ui', '/', $this -> path['string']);
	}
	
	public function setFile() {
		$this -> file = Paths::parseFile( Objects::last($this -> path['array'], 'value') );
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
	
	public function addPathArray($data) {
		$this -> path['array'][] = $data;
	}
	
	public function getPathArray($id = null) {
		return !System::set($id) ? $this -> path['array'] : Objects::n($this -> path['array'], $id, 'value');
	}
	
	public function unPathArray($id = null) {
		$this -> path['array'] = !$id ? Objects::reset( Objects::unfirst($this -> path['array']) ) : Objects::reset( Objects::unn($this -> path['array'], $id) );
	}
	
	public function setUrl() {
		$this -> url = $this -> domain . ($this -> language ? $this -> language . '/' : null) . $this -> path['string'] . $this -> query['string'];
	}
	
}

?>