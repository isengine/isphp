<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Ip;
use is\Model\Parents;

class User extends Parents\Globals {
	
	public $settings;
	public $special;
	public $rights;
	
	public function init() {
		unset($this -> data);
		$this -> data = new Parents\Entry;
	}
	
}

?>