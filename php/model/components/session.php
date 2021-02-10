<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Prepare;
use is\Model\Components\Path;
use is\Model\Components\Log;
use is\Model\Globals;
use is\Parents;

class Session extends Globals\Session {
	
	public function block($type) {
		
		$in_range = null;
		
		if (System::typeOf($this -> range, 'iterable')) {
			$in_range = Sessions::ipRange($this -> ip, $this -> range);
		} else {
			return null;
		}
		
		if (
			($type === 'blacklist' && $in_range) ||
			($type === 'whitelist' && !$in_range)
		) {
			return true;
		}
		
		return null;
		
	}
	
	public function log() {
		$this -> log = Log::getInstance();
		$this -> log -> initialize();
	}
	
	public function logName($name) {
		$this -> log -> setName($name);
	}
	
	public function logPath($path) {
		$this -> log -> setPath($path);
	}
	
	public function logAdd($data) {
		$this -> log -> addData($data);
	}
	
	public function logGet() {
		return $this -> log -> getData();
	}
	
	public function path($path) {
		$opath = new Path();
		$path = $opath -> convertToUrl($path);
		$path = $opath -> convertSlashes($path);
		unset($opath);
		$this -> path = '/' . $path . '/';
	}
	
	public function close($code = null) {
		
		if (!empty($this -> log)) {
			$this -> log -> summary();
			$this -> log -> close();
		}
		
		if ($path) {
			$this -> path = $path;
		}
		
		System::refresh($this -> path, $code, ['Content-Type' => 'text/html; charset=UTF-8']);
		exit;
		
	}
	
}

?>