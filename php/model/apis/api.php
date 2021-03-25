<?php

namespace is\Model\Apis;

use is\Helpers\Sessions;
use is\Helpers\Prepare;
use is\Model\Parents\Singleton;
use is\Model\Apis\Method;

class Api extends Singleton {
	
	public $class;
	public $method;
	
	public $key;
	public $token;
	
	public $settings;
	
	public function init($settings) {
		
		$this -> class = $settings['class'];
		$this -> method = $settings['method'];
		
		if ($settings['key']) {
			$this -> setKey($settings['key']);
		}
		
		if ($settings['token']) {
			$this -> setToken($settings['token']);
		}
		
		if ($settings['data']) {
			$this -> setData($settings['data']);
		}
		
	}
	
	public function launch() {
		
		$class_name = __NAMESPACE__ . '\\Methods\\' . $this -> class;
		$class = new $class_name($this -> getData());
		$method = $this -> method;
		$class -> $method();
		
	}
	
	public function setKey($key) {
		$this -> key = json_decode(Prepare::decode($key), true);
	}
	
	public function setToken($token) {
		$this -> token = [
			'current' => time(),
			'request' => Prepare::decode($token)
		];
	}
	
	public function setSettings($settings) {
		$this -> settings = $settings;
	}
	
}

?>