<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Parents\Entry;
use is\Parents\Globals;

class User extends Globals {
	
	public $settings;
	public $special;
	public $rights;
	
	public function init() {
		unset($this -> data);
		$this -> data = new Entry;
	}
	
	public function setSettings($settings) {
		$this -> settings = $settings;
	}
	
	public function setRights($rights) {
		$this -> rights = $rights;
	}
	
	public function setSpecial() {
		$this -> special = [];
		if ($this -> settings) {
			foreach ($this -> settings as $key => $item) {
				if ($item['special']) {
					$this -> special[ $item['special'] ][] = $key;
				}
			}
			unset($key, $item);
		}
	}
	
	public function getFields($name = null) {
		return $name ? $this -> data['data'][$name] : $this -> data['data'];
	}
	
	public function getFieldsBySpecial($name, $all = null) {
		
		$result = [];
		$specials = $this -> special[$name];
		
		if (!$specials) {
			return null;
		}
		
		foreach ($specials as $item) {
			$r = $this -> data -> getData($item);
			if ($all) {
				$result[] = $r;
			} else {
				$result = $r;
				break;
			}
		}
		unset($item);
		
		return $result;
		
	}
	
	public function setFieldsBySpecial($name, $data) {
		
		$field = getFieldsNameBySpecial($name);
		$this -> data -> setData($field, $data);
		
	}
	
	public function addFieldsBySpecial($name, $data) {
		
		$field = getFieldsNameBySpecial($name);
		$this -> data -> addData($field, $data);
		
	}
	
	public function getFieldsNameBySpecial($name, $all = null) {
		
		$specials = $this -> special[$name];
		
		return $all ? $specials : Objects::first($specials, 'value');
		
	}
	
}

?>