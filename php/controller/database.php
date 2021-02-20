<?php

namespace is\Controller;

use is\Helpers\Sessions;
use is\Helpers\Objects;
use is\Parents\Singleton;
use is\Parents\Collection;
use is\Controller\Driver;

class Database extends Singleton {
	
	/*
	этот класс отвечает за обмен системы с базой данных
	однако на самом деле всю работу осуществляет драйвер
	*/
	
	public $name;
	
	protected $driver;
	
	public function init(Driver $driver) {
		$this -> setDriver($driver);
		$this -> data = new Collection;
		$this -> driver -> connect();
	}
	
	public function reset() {
		unset(
			$this -> driver,
			$this -> data
		);
		$this -> data = [];
	}
	
	public function setDriver(Driver $driver) {
		$this -> driver = $driver;
	}
	
}

?>