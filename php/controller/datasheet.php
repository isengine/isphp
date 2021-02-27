<?php

namespace is\Controller;

use is\Helpers\Sessions;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\Prepare;
use is\Parents\Singleton;
use is\Parents\Collection;
use is\Parents\Data;
use is\Controller\Driver;

class Datasheet extends Data {
	
	/*
	этот класс отвечает за обмен системы с базой данных
	однако на самом деле всю работу осуществляет драйвер
	*/
	
	public $name;
	
	public $driver;
	
	public function init($settings) {
		
		$settings['driver'] = '\\is\\Controller\\Drivers\\' . $settings['driver'];
		$this -> driver = new $settings['driver'] ($settings);
		
		$this -> data = new Collection;
		$this -> driver -> connect();
		
	}
	
	public function reset() {
		unset(
			$this -> driver,
			$this -> data
		);
	}
	
	public function launch() {
		$this -> driver -> launch();
		$this -> data -> addByList($this -> driver -> data); // new
		$this -> driver -> resetData();
		$this -> driver -> cached = null;
	}
	
	public function cache($path) {
		$this -> driver -> cache($path);
	}
	
	public function collection($name) {
		$this -> driver -> collection($name);
	}
	
	public function query($name) {
		$this -> driver -> query($name);
	}
	
	public function rights($rights, $owner = null) {
		$this -> driver -> rights($rights, $owner);
	}
	
}

?>