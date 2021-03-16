<?php

namespace is\Model\Apis;

use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Helpers\Prepare;
use is\Model\Parents\Data;

class Api extends Data {
	
	public $class;
	public $method;
	
	public $key;
	public $token;
	
	public function __construct($settings) {
		$this -> init($settings);
	}
	
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
	
	public function setKey($key) {
		$this -> key = json_decode(Prepare::decode($key), true);
	}
	
	public function setToken($token) {
		$this -> token = [
			'current' => time(),
			'request' => Prepare::decode($token)
		];
	}
	
}

?>