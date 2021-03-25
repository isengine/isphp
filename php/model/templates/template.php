<?php

namespace is\Model\Templates;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Model\Parents\Singleton;

class Template extends Singleton {
	
	public $settings;
	public $view;

	public function init($settings = []) {
		$this -> settings = $settings;
	}
	
	public function launch() {
		$viewname = __NAMESPACE__ . '\\Views\\' . ($this -> settings['view'] ? $this -> settings['view'] : 'DefaultView');
		$this -> view = new $viewname($this -> settings['path']);
		$this -> view -> setRealCache($this -> settings['cache']);
	}
	
}

?>