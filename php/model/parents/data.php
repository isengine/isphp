<?php

namespace is\Model\Parents;

use is\Helpers\System;
use is\Helpers\Objects;

class Data {
	
	public $data = [];
	
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
		
		if (System::set($key)) {
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
		
		$this -> data[$key][] = $data;
		
	}
	
	public function resetDataKey($key) {
		
		// Сбрасывает все данные по ключу
		
		$this -> data[$key] = null;
		
	}
	
	public function mergeData($data, $recursion = null) {
		
		// Заменяет текущие данные новыми, переданными в массиве 'merge'
		
		$this -> data = Objects::merge($this -> data, $data, $recursion);
		
	}
	
	public function eachData(&$parameters = null, $callback) {
		
		// Итератор данных
		
		return Objects::each($this -> data, $parameters, $callback);
		
	}
	
}

?>