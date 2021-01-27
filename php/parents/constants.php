<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

class Constants extends Singleton {
	
	protected $constants = [];
	
	public function initialize() {
		
		if (System::typeData($this -> data, 'object')) {
			foreach ($this -> data as $key => $item) {
				if (System::typeData($item, 'object')) {
					foreach ($item as $k => $i) {
						$this -> set(strtoupper($key . '_' . $k), $i);
						//define(strtoupper($key . '_' . $k), $i);
					}
				}
			}
			unset($key, $item);
		}
		unset($this -> data);
		
	}
	
	public function set($key, $value) {
		$key = $this -> convert($key);
		if (
			!isset($this -> constants[$key]) &&
			System::typeOf($value, 'scalar')
		) {
			$this -> constants[$key] = $value;
		}
	}
	
	public function get($key = null) {
		return System::set($key) ? $this -> constants[ $this -> convert($key) ] : $this -> constants;
	}
	
	public function is($key) {
		return isset( $this -> constants[ $this -> convert($key) ] ) ? true : null;
	}
	
	protected function convert($key) {
		return $key ? strtoupper(Strings::replace($key, ':', '_')) : $key;
	}
	
}

?>