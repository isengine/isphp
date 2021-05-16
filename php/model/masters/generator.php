<?php

namespace is\Model\Masters;

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

class Generator extends Singleton {
	
	public $info;
	public $file;
	public $error;
	public $exists;
	public $realfile;
	
	public function init($data = null) {
		
		$this -> setInfo($data);
		
		if ($this -> realfile) {
			$this -> exists = true;
		} elseif (System::set($this -> info)) {
			$ns = __NAMESPACE__ . '\\Files\\' . Prepare::upperFirst($this -> info['extension']) . '\\' . Prepare::upperFirst($this -> info['name']);
			if (class_exists($ns)) {
				$this -> file = new $ns;
				$this -> exists = true;
			} else {
				$this -> error = true;
			}
		}
		
	}
	
	public function setInfo($data = null) {
		
		if (System::typeIterable($data)) {
			$this -> info = [
				'name' => $data['name'],
				'extension' => $data['extension'],
				'url' => $data['url'],
				'real' => $data['real']
			];
		}
		
		if ($this -> info['name'] && $this -> info['real'] && file_exists($this -> info['real'])) {
			$this -> realfile = true;
		}
		
	}
	
	public function launch() {
		
		// запуск файла
		
		Sessions::setHeaderCode(200);
		
		if ($this -> realfile) {
			Sessions::setHeader(['Content-type' => mime_content_type($this -> info['real']) . '; charset=utf-8']);
			echo file_get_contents($this -> info['real']);
		} else {
			$this -> file -> launch();
			$this -> file -> printBuffer();
		}
		
		exit;
		
	}
	
}

?>