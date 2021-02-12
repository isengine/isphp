<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Ip;
use is\Parents;

class User extends Parents\Globals {
	
	public $id; // late as uid
	public $allow; // as []
	public $rights; // as []
	
	public $ip; // now in session
	public $sid; // now in session
	public $token; // now in session
	
	public function init() {
		
		$this -> ip = Ip::real();
		$this -> sid = session_id();
		$this -> token = Prepare::crypt( time() );
		
	}
	
}

?>