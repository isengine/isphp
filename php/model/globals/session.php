<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Ip;
use is\Helpers\Prepare;
use is\Model\Parents;

class Session extends Parents\Globals {
	
	protected $init;
	
	protected $agent;
	protected $referrer;
	protected $origin;
	protected $request;
	
	protected $id;
	protected $token;
	protected $time;
	protected $ip;
	
	protected $cookie;
	
	public function init() {
		
		$time = new \DateTime();
		
		$this -> init = $time -> format('Y.m.d-H.i.s.u');
		
		$this -> agent = $_SERVER['HTTP_USER_AGENT'] ? Prepare::hash($_SERVER['HTTP_USER_AGENT']) : null;
		$this -> referrer = $_SERVER['HTTP_REFERER'];
		$this -> origin = !empty($_SERVER['ORIGIN']) ? $_SERVER['ORIGIN'] : $_SERVER['HTTP_ORIGIN'];
		$this -> request = Prepare::lower($_SERVER['REQUEST_METHOD']);
		
		$this -> id = session_id();
		$this -> ip = Ip::real();
		
		if ($this -> id) {
			if ($_SESSION['token']) {
				$this -> token = $_SESSION['token'];
			} else {
				
				$token = Prepare::encode(json_encode([
					'id' => $this -> id,
					'ip' => $this -> ip,
					'agent' => $this -> agent,
					'time' => time()
				]));
				
				$this -> token = $token;
				$_SESSION['token'] = $token;
				
				unset($token);
				
			}
		}
		
	}
	
}

?>