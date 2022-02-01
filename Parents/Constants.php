<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

class Constants extends Singleton {
	
	protected $constants = [];
	
	public function init() {
		$this -> recursion($this -> data);
		$this -> resetData();
	}
	
	protected function recursion($data, $name = null) {
		
		if (System::typeData($data, 'object')) {
			foreach ($data as $key => $item) {
				$this -> recursion($item, ($name ? $name . '_' : null) . $key);
			}
			unset($key, $item);
		} elseif ($name) {
			$this -> set(strtoupper($name), $data);
		}
		
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
	
	public function get($key = null, $from = null) {
		return System::set($key) ? $this -> constants[ $this -> convert($key) ] : $this -> constants;
	}
	
	public function getArray($prefix, $convert = null) {
		$result = [];
		$prefix = Strings::replace(mb_strtoupper($prefix), ':', '_');
		$len = Strings::len($prefix) + 1;
		foreach ($this -> constants as $key => $item) {
			if (Strings::find($key, $prefix, 0)) {
				if ($convert) {
					$key = mb_strtolower(Strings::get($key, $len));
				}
				if (Strings::find($key, '_')) {
					$key = Strings::before($key, '_');
					$item = $this -> getArray($prefix . '_' . $key, $convert);
				}
				$result[$key] = $item;
			}
		}
		unset($key, $item);
		return $result;
	}
	
	public function is($key) {
		return isset( $this -> constants[ $this -> convert($key) ] ) ? true : null;
	}
	
	protected function convert($key) {
		return $key ? strtoupper(Strings::replace($key, ':', '_')) : $key;
	}
	
}

?>