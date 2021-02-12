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
	
	public function init() {
		
		$this -> init = (new \DateTime()) -> format('Y.m.d-H.i.s.u');
		
		$this -> agent = $_SERVER['HTTP_USER_AGENT'];
		$this -> referrer = $_SERVER['HTTP_REFERER'];
		$this -> origin = !empty($_SERVER['ORIGIN']) ? $_SERVER['ORIGIN'] : $_SERVER['HTTP_ORIGIN'];
		$this -> request = $_SERVER['REQUEST_METHOD'];
		
		$this -> token = Prepare::crypt(time());
		$this -> id = session_id();
		$this -> ip = Sessions::ipReal();
		
	}
	
}

?>