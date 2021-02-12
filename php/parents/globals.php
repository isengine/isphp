<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Objects;

abstract class Globals extends Singleton {
	
	abstract public function init();
	
	public function get($name = null) {
		return $name ? $this -> $name : get_object_vars($this);
	}
	
	public function compare($keys, $value) {
		
		$keys = Objects::convert($keys);
		$len = Objects::len($keys) - 1;
		
		foreach ($keys as $key => $item) {
			
			if (!isset($value[$item])) {
				return null;
			}
			
			$value = $value[$item];
			
			if ($key === $len) {
				return $value;
			} elseif (!is_array($value)) {
				return null;
			}
			
		}
		
		unset($key, $item);
		
	}
	
}

?>