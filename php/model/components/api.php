<?php

namespace is\Model\Components;

use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Model\Parents\Globals;
use is\Model\Components\Path;

class Api extends Globals {
	
	public $path;
	
	public $name;
	
	public $prefix;
	public $postfix;
	
	public function init($path = null) {
		$this -> setPath($path);
	}
	
	public function setPath($path = null) {
		
		if ($path) {
			$path = Paths::convertToUrl($path);
			$path = Paths::clearSlashes($path);
		}
		
		$this -> path = !$path || $path === '/' ? '/' : '/' . $path . '/';
		
	}
	
	public function reload() {
		
		$this -> data['Error-Сode'] = $this -> code;
		$this -> data['Error-Reason'] = $this -> reason;
		
		$path = $this -> path . $this -> prefix . $this -> code . $this -> postfix . ($this -> reason ? $this -> reason : null);
		
		Sessions::reload($path, $this -> code, $this -> data);
		exit;
		
	}
	
}

?>