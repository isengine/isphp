<?php

namespace is\Parents;

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
	
	public function setData($data, $key = null) {
		
		// Сохраняет данные
		// если указан ключ, то сохраняет значение этого ключа
		// или целиком перезаписывает массив данных
		
		if (System::set($key)) {
			$this -> data[$key] = $data;
		} else {
			$this -> data = $data;
		}
		
	}
	
	public function mergeData($data) {
		
		// Заменяет текущие данные новыми, переданными в массиве 'merge'
		
		$this -> data = Objects::merge($this -> data, $data);
		
	}
	
	public function eachData(&$parameters = null, $callback) {
		
		// Итератор данных
		
		return Objects::each($this -> data, $parameters, $callback);
		
	}
	
}

?>