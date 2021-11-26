<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Ip;
use is\Helpers\Prepare;
use is\Parents\Globals;

class Session extends Globals {
	
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
	
	public function reinit() {
		session_regenerate_id(true);
		$_SESSION['token'] = null;
		$this -> init();
		Sessions::setCookie('session', $this -> token);
		//Sessions::setCookie('session', $_SESSION['token']);
		//Sessions::setCookie('session', $session -> getSession('token'));
	}
	
	public function open() {
		session_start();
	}
	
	public function close() {
		
		if (session_id()) {
			session_unset();
			session_destroy();
		} else {
			$_SESSION = [];
		}
		
		$cookies = Objects::keys(Sessions::getCookie());
		Sessions::unCookie($cookies);
		
	}
	
	public function setCsrf() {
		$_SESSION['csrf-match'] = $_SESSION['csrf-token'];
		$_SESSION['csrf-token'] = Prepare::hash(time());
		if (!$_SESSION['csrf-match']) {
			$_SESSION['csrf-match'] = $_SESSION['csrf-token'];
		}
		Sessions::setHeader(['X-CSRF-Token' => $_SESSION['csrf-token']]);
	}
	
	public function getCsrf() {
		return $_SESSION['csrf-token'];
	}
	
	public function matchCsrf($match) {
		return $_SESSION['csrf-match'] === $match;
	}
	
	public function getSession($name) {
		return $this -> $name;
	}
	
	public function getValue($name) {
		return $_SESSION[$name];
	}
	
	public function setValue($name, $data) {
		$_SESSION[$name] = $data;
	}
	
}


?>