<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;

class Data {
	
	public $data = [];
	
	public function get($key = null, $from = null) {
		
		// Отдает данные по ключу, который парсится в массив
		// из публичных свойств объекта
		// или из массива данных, включая многомерный массив
		// источник, откуда брать данные, можно указать явно: class/data
		// или он будет определен в автоматическом режиме
		// по приоритету и наличию ключа
		
		$result = null;
		
		$set = System::set($key);
		
		if (!$set && $from !== 'key') {
			$result = $this -> data;
		} elseif (Strings::match($key, ':') && $from !== 'key') {
			$data = Parser::fromString($key);
			$result = Objects::extract($this -> data, $data);
		} elseif ($this -> $key && $from !== 'data') {
			$result = $this -> getKey($key);
		} elseif ($set && System::typeOf($key, 'scalar') && $from !== 'key') {
			$result = $this -> data[$key];
		}
		
		return $result;
		
	}
	
	public function getKey($key) {
		
		// Отдает данные объекта по ключу
		// если данные не защищены
		
		$prop = new \ReflectionProperty(static::class, $key);
		return $prop -> isPrivate() ? null : $this -> $key;
		
	}
	
	public function getData($key = null) {
		
		// Отдает данные
		// если указан ключ, то по ключу
		// или весь массив данных сразу
		
		if (System::set($key)) {
			return $this -> data[$key];
		} else {
			return $this -> data;
		}
		
	}
	
	public function setData($first, $second = null) {
	//public function setData($data, $key = null) {
		
		// Сохраняет данные
		// если указан ключ, то сохраняет значение этого ключа
		// или целиком перезаписывает массив данных
		
		if ($second) {
			$key = &$first;
			$data = &$second;
		} else {
			$key = null;
			$data = &$first;
		}
		
		if (!$data) {
			$this -> resetData();
		} elseif (System::set($key)) {
			$this -> data[$key] = $data;
		} else {
			$this -> data = $data;
		}
		
	}
	
	//public function addData($data) {
	//	// Добавляет данные
	//	$this -> data[] = $data;
	//}
	
	public function addData($first, $second = null) {
		
		// Добавляет данные
		
		if ($second) {
			$this -> data[$first] = $second;
		} else {
			$this -> data[] = $first;
		}
		
	}
	
	public function resetData() {
		
		// Сбрасывает все данные
		
		$this -> data = [];
		
	}
	
	public function addDataKey($key, $data) {
		
		// Добавляет данные по ключу
		
		$this -> data[$key] = $data;
		
	}
	
	public function resetDataKey($key) {
		
		// Сбрасывает все данные по ключу
		
		$this -> data[$key] = null;
		
	}
	
	public function deleteDataKey($key) {
		
		// Удаляет данные по ключу
		
		unset($this -> data[$key]);
		
	}
	
	public function mergeData($data, $recursion = null) {
		
		// Заменяет текущие данные новыми, переданными в массиве 'merge'
		
		$this -> data = Objects::merge($this -> data, $data, $recursion);
		
	}
	
	public function eachData(&$parameters = null, $callback) {
		
		// Итератор данных
		
		return Objects::eachOf($this -> data, $parameters, $callback);
		
	}
	
}

?>