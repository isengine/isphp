<?php

namespace is\Model\Templates;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Match;
use is\Helpers\Paths;
use is\Helpers\Prepare;
use is\Model\Parents\Data;

class View extends Data {
	
	// общие установки кэша
	
	public $path;
	public $cache;
	
	public function __construct($path, $cache) {
		$this -> path = $path;
		$this -> cache = $cache;
	}
	
	public function add($type, $cache = 'skip') {
		$name = __NAMESPACE__ . '\\Views\\' . (Prepare::upperFirst($type));
		$this -> data[$type] = new $name($this -> path, $this -> cache);
		if ($cache !== 'skip') {
			$this -> cache($type, $cache);
		}
	}
	
	public function get($type) {
		return $this -> data[$type];
	}
	
	public function init($type) {
		$this -> data[$type] -> init();
	}
	
	public function cache($type, $cache) {
		$this -> data[$type] -> caching($cache);
	}
	
	public function clear() {
		Local::eraseFolder($this -> cache);
	}
	
	public function includes($name = null, $type = null, $cache = 'skip') {
		$this -> data[$type] -> includes($name, $cache);
		//if (!$from) {
		//	$this -> includePage($name, $cache);
		//} elseif ($from === 'block') {
		//	$this -> includeBlock($name, $cache);
		//} else {
		//	$path = $this -> path . 'html' . DS . $from . DS . $this -> parsePagePath($name) . '.php';
		//	$this -> load($path);
		//}
	}
	
}

?>