<?php

namespace is\Model\Databases;

use is\Helpers\Sessions;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\Prepare;
use is\Model\Parents\Singleton;
use is\Model\Parents\Collection;
use is\Model\Databases\Driver;

class Database extends Singleton {
	
	/*
	этот класс отвечает за обмен системы с базой данных
	однако на самом деле всю работу осуществляет драйвер
	*/
	
	public $name;
	
	public $driver;
	
	public function init($settings) {
		
		$driver = __NAMESPACE__ . '\\Drivers\\' . $settings['driver'];
		unset($settings['driver']);
		
		$this -> driver = new $driver ($settings);
		
		$this -> data = new Collection;
		$this -> driver -> connect();
		
	}
	
	public function reset() {
		unset(
			$this -> driver,
			$this -> data
		);
	}
	
	public function clear() {
		$this -> driver -> resetFilter();
		$this -> data -> reset();
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