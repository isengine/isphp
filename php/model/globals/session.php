<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Prepare;

use is\Model\Globals\Log;

use is\Parents;
use is\Parents\Path;

class Session extends Parents\Globals {
	
	private $init;
	
	private $agent;
	private $referrer;
	private $origin;
	private $request;
	
	private $token;
	private $id;
	private $ip;
	
	private $log;
	private $path;
	
	public $range;
	
	public function initialize() {
		
		$this -> init = (new \DateTime()) -> format('Y.m.d-H.i.s.u');
		
		$this -> agent = $_SERVER['HTTP_USER_AGENT'];
		$this -> referrer = $_SERVER['HTTP_REFERER'];
		$this -> origin = !empty($_SERVER['ORIGIN']) ? $_SERVER['ORIGIN'] : $_SERVER['HTTP_ORIGIN'];
		$this -> request = $_SERVER['REQUEST_METHOD'];
		
		$this -> token = Prepare::crypt(time());
		$this -> id = session_id();
		$this -> ip = Sessions::ipReal();
		
	}
	
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