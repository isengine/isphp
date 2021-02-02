<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Local as LocalFunctions;

class Local extends Data {
	
	private $path;
	
	public $data = [
		'folder' => null,
		'file' => [],
		'list' => [],
		'content' => null
	];
	
	public function __construct($path = null) {
		$this -> setPath($path);
	}
	
	public function setPath($path = null) {
		$this -> path = new Path($path);
		$this -> data['folder'] = $this -> path -> real;
	}
	
	public function resetPath() {
		$this -> path -> reset();
		$this -> data['folder'] = $this -> path -> real;
	}
	
	public function setFile($path) {
		$this -> data['file'] = $this -> path -> getReal($path);
	}
	
	public function setFileFromList($index = 0) {
		$this -> data['file'] = $this -> data['folder'] . $this -> data['list'][$index];
	}
	
	public function list($parameters = ['return' => 'files']) {
		$this -> data['list'] = LocalFunctions::list($this -> data['folder'], $parameters);
	}
	
	public function reset() {
		$this -> data['list'] = null;
		$this -> data['file'] = null;
	}
	
	public function setContent() {
		$this -> data['content'] = LocalFunctions::openFile($this -> data['file']);
	}
	
}

?>