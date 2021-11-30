<?php

namespace is\Masters;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

use is\Helpers\Parser;
use is\Helpers\Prepare;
use is\Helpers\Sessions;

use is\Parents\Singleton;
use is\Components\Collection;

class Database extends Singleton {
	
	/*
	этот класс отвечает за обмен системы с базой данных
	однако на самом деле всю работу осуществляет драйвер
	
	$name - имя базы данных, относится к этому объекту,
	т.е. к базе данных;
	обычно оно берется из раздела 'db' конфига;
	затем этот конфиг передается в драйвер
	new $driver ($settings);
	
	$driver - имя драйвера
	
	collection() - а это метод, который обращается к драйверу
	и записывает переданное значение в свойство collection,
	но уже в драйвере;
	свойство collection в свою очередь обращается к разделу
	базы данных;
	
	чтобы было понятнее, обратимся к mysql, где имя базы
	задается в конфиге, а collection - уже имя раздела
	внутри этой базы
	
	для локальных баз данных, как правило, имя - это папка,
	причем она может иметь вложенность
	(например, 'app:databases:default'),
	а collection - имя файла, либо папки
	внутри общей папки базы данных
	(например, 'first');
	по итогу, путь будет таким:
	/app/databases/default/first/ (для локальной базы из файлов json)
	или таким:
	/app/databases/default/first.csv (для таблиц)
	
	с точки зрения локальной базы данных,
	эту структуру можно было бы упростить,
	но с точки зрения практики работы с mysql и другими базами,
	нужно оставить как есть
	*/
	
	public $name;
	
	public $driver;
	
	public function init($settings) {
		
		$driver = __NAMESPACE__ . '\\Drivers\\' . Prepare::upperFirst($settings['driver']);
		
		$rights = $settings['rights'];
		unset($settings['driver'], $settings['rights']);
		
		$this -> driver = new $driver ($settings);
		
		$this -> data = new Collection;
		$this -> driver -> connect();
		
		if ($rights) {
			$this -> driver -> rights($rights);
		}
		
	}
	
	public function reset() {
		unset(
			$this -> driver,
			$this -> data
		);
	}
	
	public function clear() {
		$this -> driver -> filter -> resetFilter();
		$this -> data -> reset();
	}
	
	public function launch() {
		$this -> driver -> launch();
		if (System::typeOf($this -> driver -> data, 'iterable')) {
			$this -> data -> addByList($this -> driver -> data);
		} else {
			$this -> data -> reset();
		}
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