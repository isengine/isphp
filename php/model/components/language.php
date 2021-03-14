<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Prepare;

use is\Model\Globals;

class Language extends Globals\Language {
	
	public $settings = [];
	public $list = [];
	public $codes = [];
	public $code;
	
	public function setLang($lang) {
		
		if (!$lang) {
			$this -> init();
			return;
		}
		
		$lang = Strings::split($lang, '-');
		$lang = Objects::first($lang, 'value');
		$lang = Prepare::lower($lang);
		
		$lang = $this -> mergeLang($lang);
		
		if (!$lang) {
			$lang = Objects::first($this -> list, 'value');
		}
		
		$this -> lang = $lang;
		$this -> setCode();
		
	}
	
	public function mergeLang($lang) {
		return $lang ? $this -> list[$lang] : null;
	}
	
	public function setSettings($settings) {
		$this -> settings = $settings;
	}
	
	public function addList($key, $array = null) {
		
		if (
			System::set($array) &&
			System::typeOf($array, 'iterable')
		) {
			$this -> list = Objects::merge($this -> list, Objects::fill($array, $key));
		} else {
			$this -> list[$key] = $key;
		}
		
	}
	
	public function addCode($key, $item = null) {
		
		$item = Prepare::upper($item ? $item : $key);
		
		$this -> codes[$key] = $item;
		
	}
	
	public function setCode() {
		
		$this -> code = $this -> codes[$this -> lang];
		
	}
	
}

?>