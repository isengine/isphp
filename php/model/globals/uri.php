<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Parents;

class Uri extends Parents\Globals {
	
	public $scheme;
	public $host;
	public $www;
		
	public $user;
	public $password;
	public $port;
		
	public $path = [
		'base' => null,
		'string' => null,
		'array' => []
	];
	public $query = [
		'string' => null,
		'array' => []
	];
	public $fragment;
		
	public $domain;
	public $url;
	public $previous;
		
	public $refresh;
	
	public function initialize() {
		
		// получение данных
		
		$url = $_SERVER['REQUEST_SCHEME'] . '://' . (extension_loaded('intl') ? idn_to_utf8($_SERVER['HTTP_HOST']) : $_SERVER['HTTP_HOST']) . (!empty($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : null) . urldecode($_SERVER['REQUEST_URI']);
		
		$urlparse = parse_url($url);
		
		$this -> scheme = $urlparse['scheme'];
		$this -> host = $urlparse['host'];
		$this -> www = Strings::find($urlparse['host'], 'www.', 0);
			
		$this -> user = $urlparse['user'];
		$this -> password = $urlparse['pass'];
		$this -> port = $urlparse['port'];
			
		$this -> path = [
			'base' => '/',
			'string' => '',
			'array' => []
		];
		$this -> query = [
			'string' => !empty($urlparse['query']) ? '?' . $urlparse['query'] : null,
			'array' => $_GET
		];
		$this -> fragment = $urlparse['fragment'];
			
		$this -> domain = $urlparse['scheme'] . '://' . $urlparse['host'] . '/';
		$this -> url = null;
		$this -> previous = null;
			
		$this -> refresh = null;
		
		// подготовка данных
		
		$this -> path['array'] = Strings::split($urlparse['path'], '\/', true);
		
		$this -> path['string'] = !empty($this -> path['array']) ? Strings::join($this -> path['array'], '/') . '/' : null;
		
		$this -> url = $this -> domain . $this -> path['string'] . $this -> query['string'];
		
		unset($url, $urlparse);
		
	}
	
}

?>