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
		
		if (!$lang) {
			return null;
		}
		
		if (Objects::match($this -> list, $lang)) {
			return $lang;
		} else {
			$l = $lang;
			$lang = null;
			if ($this -> settings) {
				foreach ($this -> settings as $key => $item) {
					if (Objects::match($item['alias'], $l)) {
						$lang = $key;
						break;
					}
				}
				unset($key, $item);
			}
		}
		
		return $lang;
		
	}
	
	public function setSettings($settings) {
		
		$this -> settings = $settings;
		$this -> setList();
		$this -> setCode();
		
	}
	
	public function setList() {
		
		$this -> list = Objects::keys($this -> settings);
		
	}
	
	public function setCode() {
		
		$this -> code = $this -> settings[$this -> lang]['code'];
		
	}
	
}

?>