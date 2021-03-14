<?php

namespace is\Model\Components;

use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Model\Parents\Globals;
use is\Model\Components\Path;

class Error extends Globals {
	
	public $path;
	
	public $code;
	public $reason;
	
	public $prefix;
	public $postfix;
	
	public function init($path = null) {
		$this -> setPath($path);
		$this -> data['Content-Type'] = 'text/html; charset=UTF-8';
	}
	
	public function setPath($path = null) {
		
		//if ($path) {
		//	$path = Paths::convertToUrl($path);
		//	$path = Paths::clearSlashes($path);
		//}
		//$this -> path = !$path || $path === '/' ? '/' : '/' . $path . '/';
		
		$this -> path = Paths::relativeUrl($path);
		
	}
	
	public function setError($code = null) {
		
		//$this -> data['Error-Сode'] = $this -> code;
		//$this -> data['Error-Reason'] = $this -> reason;
		
		if (!$code) {
			$this -> code = $code;
		}
		
		if (headers_sent()) {
			return;
		}
		
		Sessions::setHeader($this -> data);
		Sessions::setHeaderCode($this -> code);
		
	}
	
	public function reload() {
		
		//$this -> data['Error-Сode'] = $this -> code;
		//$this -> data['Error-Reason'] = $this -> reason;
		
		$path = $this -> path . $this -> prefix . $this -> code . $this -> postfix . ($this -> reason ? $this -> reason : null);
		
		Sessions::reload($path, $this -> code, $this -> data);
		exit;
		
	}
	
}

?>