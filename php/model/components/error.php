<?php

namespace is\Model\Components;

use is\Helpers\Sessions;
use is\Parents\Globals;
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
		
		if ($path) {
			$opath = new Path();
			$path = $opath -> convertToUrl($path);
			$path = $opath -> convertSlashes($path);
			unset($opath);
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