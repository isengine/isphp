<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Parents;

class Template extends Parents\Globals {
	
	public $name;
	public $created;
	public $modified;
	public $place;
	public $section;
	public $page;
	public $path;
	public $settings;
	public $device;
	public $script;
	public $list;
	
	public function init() {
		
		$this -> name = null;
		$this -> created = null;
		$this -> modified = null;
		$this -> place = null;
		$this -> section = null;
		$this -> page = [];
		$this -> path = [
			'init' => null,
			'page' => null
		];
		$this -> settings = [
			'libraries' => null,
			'options' => null,
			'assets' => null,
			'special' => null
		];
		$this -> device = null;
		$this -> script = null;
		$this -> list = [
			'folders' => null,
			'router' => null,
			'structure' => null
		];
		
	}
	
}

?>