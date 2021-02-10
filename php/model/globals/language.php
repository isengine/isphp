<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Parents;

class Language extends Parents\Globals {
	
	public $settings = [];
	public $list = [];
	public $code;
	public $lang;
	public $data = [];
	
	public function initialize() {
		
	}
	
}

?>