<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;

use is\Parents;

class Session extends Parents\Globals {
	
	public $name;
	public $charset;
	public $sender;
	
	public $agent;
	public $referrer;
	public $origin;
	public $request;
	
	public $token;
	public $id;
	public $ip;
	
	public function initialize() {
		
		$this -> name = 'SID';
		$this -> charset = 'UTF-8';
		$this -> sender = 'X-Mailer: PHP/' . phpversion();
		
		$this -> agent = $_SERVER['HTTP_USER_AGENT'];
		$this -> referrer = $_SERVER['HTTP_REFERER'];
		$this -> origin = !empty($_SERVER['ORIGIN']) ? $_SERVER['ORIGIN'] : $_SERVER['HTTP_ORIGIN'];
		$this -> request = $_SERVER['REQUEST_METHOD'];
		
		//$this -> token = crypting(time());
		$this -> id = session_id();
		$this -> ip = Sessions::ipReal();
		
	}
	
}

?>