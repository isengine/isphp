<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Local as LocalFunctions;
use is\Parents\Data;

class Local extends Data {
	
	private $path;
	public $parameters = ['return' => 'files'];
	
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
		$this -> resetList();
		$this -> data['folder'] = $this -> path -> real;
	}
	
	public function setFile($path) {
		$this -> data['file'] = $this -> path -> getReal($path);
	}
	
	public function setFileFromList($index = 0) {
		$this -> data['file'] = $this -> data['folder'] . $this -> data['list'][$index];
	}
	
	public function setList($parameters = null) {
		$this -> data['list'] = LocalFunctions::list($this -> data['folder'], $parameters ? $parameters : $this -> parameters);
	}
	
	public function resetList() {
		$this -> data['list'] = null;
		$this -> data['file'] = null;
	}
	
	public function setContent() {
		$this -> data['content'] = LocalFunctions::openFile($this -> data['file']);
	}
	
	public function getContent() {
		return $this -> data['content'];
	}
	
	public function getFile() {
		return $this -> data['file'];
	}
	
	public function getFileFromList($index = 0) {
		return $this -> data['folder'] . $this -> data['list'][$index];
	}
	
	public function getList() {
		return $this -> data['list'];
	}
	
	public function getListReal() {
		
		$result = [];
		foreach ($this -> data['list'] as $item) {
			$result[] = $this -> data['folder'] . $item;
		}
		unset($item);
		return $result;
		
	}
	
	public function getListUrl() {
		
		$result = [];
		
		$folder = new Path($this -> data['folder']);
		
		foreach ($this -> data['list'] as $item) {
			$result[] = $folder -> url . str_replace(DS, '/', $item);
		}
		unset($item);
		unset($folder);
		
		return $result;
		
	}
	
}

?>