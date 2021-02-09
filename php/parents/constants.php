<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

class Constants extends Singleton {
	
	protected $constants = [];
	
	public function initialize() {
		$this -> recursive($this -> data);
		$this -> reset();
	}
	
	protected function recursive($data, $name = null) {
		
		if (System::typeData($data, 'object')) {
			foreach ($data as $key => $item) {
				$this -> recursive($item, ($name ? $name . '_' : null) . $key);
			}
			unset($key, $item);
		} elseif ($name) {
			$this -> set(strtoupper($name), $data);
		}
		
	}
	
	public function reset() {
		$this -> data = [];
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