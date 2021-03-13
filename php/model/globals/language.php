<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Prepare;
use is\Model\Parents;

class Language extends Parents\Globals {
	
	public $settings = [];
	public $list = [];
	public $code;
	public $lang;
	
	public function init() {
		
		$list = [
			'langs' => null,
			'arr' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) : null
		];
		
		if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list['arr'], $list['arr'])) {
			$list['langs'] = array_combine($list['arr'][1], $list['arr'][2]);
			foreach ($list['langs'] as $key => $item) {
				$list['langs'][$key] = $item ? $item : 1;
			}
			unset($key, $item);
			arsort($list['langs'], SORT_NUMERIC);
		} else {
			$list['langs'] = [];
		}
		
		$lang = Objects::first($list['langs'], 'key');
		
		unset($list);
		
		$lang = Strings::split($lang, '-');
		$lang = Objects::first($lang, 'value');
		$lang = Prepare::lower($lang);
		
		$this -> lang = $lang;
		
	}
	
}

?>