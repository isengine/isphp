<?php

namespace is\Model\Files;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\Prepare;
use is\Helpers\Paths;
use is\Helpers\Sessions;
use is\Model\Parents\Singleton;
use is\Model\Components\State;
use is\Model\Components\Uri;

class File extends Singleton {
	
	public $file;
	public $error;
	public $exists;
	
	public function init() {
		
		$uri = Uri::getInstance();
		$data = $uri -> file;
		
		if (System::set($data)) {
			$ns = __NAMESPACE__ . '\\' . Prepare::upperFirst($data['extension']) . '\\' . Prepare::upperFirst($data['name']);
			if (class_exists($ns)) {
				$this -> file = new $ns;
				$this -> exists = true;
			} else {
				$this -> error = true;
			}
		}
		
	}
	
	public function launch() {
		// запуск файла
		$this -> file -> launch();
		$this -> file -> printBuffer();
		Sessions::setHeaderCode(200);
		exit;
	}
	
}

?>