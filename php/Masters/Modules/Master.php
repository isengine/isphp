<?php

namespace is\Masters\Modules;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;

use is\Parents\Data;

abstract class Master extends Data {
	
	public $instance; // имя экземпляра модуля
	public $settings; // настройки
	public $path; // путь до папки модуля
	public $custom; // путь до кастомной папки модуля в app
	
	public function __construct(
		$instance,
		$settings,
		$path,
		$custom
	) {

		$this -> instance = $instance;
		$this -> path = $path;
		$this -> custom = $custom;
		
		$this -> settings = $settings;
		//$this -> settings = new Data;
		//$this -> settings -> setData($settings);
		
		//$this -> launch();
		
	}
	
	abstract public function launch();
	
	public function elements($name) {
		
		if ( !System::includes($name, $this -> custom . 'elements', null, $this) ) {
			System::includes($name, $this -> path . 'elements', null, $this);
		}
		
	}
	
}

?>