<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Url;
use is\Model\Globals;

class Session extends Globals\Session {
	
	public function reset() {
		
		if (session_id()) {
			session_unset();
			session_destroy();
		} else {
			$_SESSION = [];
		}
		
		$cookies = Objects::keys(Sessions::getCookie());
		Sessions::unCookie($cookies);
		
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