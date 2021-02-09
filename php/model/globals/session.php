<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Prepare;

use is\Parents;

class Session extends Parents\Globals {
	
	private $init;
	
	private $agent;
	private $referrer;
	private $origin;
	private $request;
	
	private $token;
	private $id;
	private $ip;
	
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
	
	public function get($name = null) {
		return $name ? $this -> $name : get_object_vars($this);
	}
	
	public function compare($name, $value) {
		return $this -> $name === $value;
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
	
}

?>