<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Model\Parents;

class Process extends Parents\Globals {
	
	public $method;
	public $set = [
		'parent' => null,
		'name' => null,
		'status' => null,
		'type' => null,
		'path' => null,
		'vendor' => null
	];
	public $hash;
	public $csrf;
	public $check;
	public $time;
	public $data;
	public $source;
	public $close;
	public $errors = [];
	
	public function init() {
		
	}
	
}

?>