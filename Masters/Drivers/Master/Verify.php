<?php

namespace is\Masters\Drivers\Master;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

class Verify extends Rights {
	
	public function verifyName($name) {
		
		return !$name || !$this -> settings['all'] && Strings::first($name) === '!' ? null : true;
		
	}
	
	public function verifyTime($entry) {
		
		$result = true;
		if (!$this -> settings['all']) {
			$time = time();
			if (
				($entry['ctime'] && $entry['ctime'] > $time) ||
				($entry['dtime'] && $entry['dtime'] < $time)
			) {
				$result = null;
			}
		}
		return $result;
		
	}
	
	public function verify($entry) {
		
		// общая итоговая проверка
		
		// проверка по правам
		$entry = $this -> entryRights($entry);
		
		// проверка по фильтру
		if (!$this -> filter -> filtration($entry)) {
			$entry = null;
		}
		
		// еще раз проверка по имени - контрольная
		if (!$this -> verifyName($entry['name'])) {
			$entry = null;
		}
		
		return $entry;
		
	}
	
}

?>