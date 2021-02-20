<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Ip;
use is\Helpers\Prepare;
use is\Parents;

class Session extends Parents\Globals {
	
	protected $init;
	
	protected $agent;
	protected $referrer;
	protected $origin;
	protected $request;
	
	protected $token;
	protected $id;
	protected $ip;
	
	protected $cookie;
	
	public function init() {
		
		$time = new \DateTime();
		
		$this -> init = $time -> format('Y.m.d-H.i.s.u');
		
		$this -> agent = $_SERVER['HTTP_USER_AGENT'];
		$this -> referrer = $_SERVER['HTTP_REFERER'];
		$this -> origin = !empty($_SERVER['ORIGIN']) ? $_SERVER['ORIGIN'] : $_SERVER['HTTP_ORIGIN'];
		$this -> request = Prepare::lower($_SERVER['REQUEST_METHOD']);
		
		$this -> token = Prepare::crypt(time());
		$this -> id = session_id();
		$this -> ip = Ip::real();
		
		if ($this -> id) {
			
			$this -> uid = md5($this -> id . $this -> ip . $this -> agent);
			
			if ($_SESSION['token']) {
				$this -> token = $_SESSION['token'];
			} else {
				$_SESSION['token'] = $this -> token;
			}
			
		}
		
	}
	
}

?>