<?php

namespace is\Controller;

use is\Helpers\Sessions;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\Prepare;
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
	}
	
	public function cache($path = null) {
		if ($path && file_exists($path)) {
			$this -> driver -> cachestorage = $path;
		}
	}
	
	public function collection($name) {
		$this -> driver -> collection = $name;
	}
	
	public function query($name) {
		$this -> driver -> query = $name;
	}
	
	public function set($name, $data) {
		$this -> driver -> $name = $data;
	}
	
	public function methodFilter($name) {
		$this -> driver -> filter['method'] = $name;
	}
	
	public function resetFilter($name) {
		$this -> driver -> filter['filters'] = [];
	}
	
	public function filter($name = null, $data = null) {
		
		if (is_array($name)) {
			$this -> driver -> filter['filters'][] = $name;
		} else {
			
			$item = [
				'name' => null,
				'data' => null,
				'values' => []
			];
			
			$array = Parser::fromString($name);
			$item['name'] = Objects::first($array, 'value');
			if ($item['name'] === 'data') {
				$item['name'] = Objects::n($array, 1, 'value');
				$item['data'] = true;
			}
			unset($array);
			
			if ($data) {
				$array = Parser::fromString($data);
				foreach ($array as $i) {
					
					$value = [
						'name' => null,
						'type' => 'equal',
						'require' => null,
						'except' => null
					];
					
					$first = Strings::first($i);
					$num = Strings::match($i, '_');
					
					if ($first === '+') {
						$value['require'] = true;
						$value['name'] = Strings::unfirst($i);
					} elseif ($first === '-') {
						$value['except'] = true;
						$value['name'] = Strings::unfirst($i);
					} elseif ($first === '*') {
						$value['type'] = 'string';
						$value['name'] = Strings::unfirst($i);
					} elseif ($num) {
						$value['type'] = 'numeric';
						$value['name'] = Strings::split($i, '_');
						foreach ($value['name'] as &$ii) {
							if ($ii) {
								$ii = Prepare::numeric($ii);
							}
						}
						unset($ii);
					} else {
						$value['name'] = $i;
					}
					
					$item['values'][] = $value;
					
					unset($value);
					
				}
				unset($i, $array);
			} else {
				$item['values'][] = ['type' => 'noempty'];
			}
			
			$this -> driver -> filter['filters'][] = $item;
			
			unset($item);
			
			//echo '<pre>' . print_r($item, 1) . '</pre><br>';
			//$this -> driver -> $name = $data;
			
		}
		
	}
	
}

?>