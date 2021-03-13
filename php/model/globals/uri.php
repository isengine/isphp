<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Url;
use is\Model\Parents;

class Uri extends Parents\Globals {
	
	public $scheme;
	public $host;
	public $www;
		
	public $user;
	public $password;
	public $port;
		
	public $path;
	public $query;
	public $fragment;
	
	public $domain;

	public function init() {
		
		// получение данных
		
		$url = $_SERVER['REQUEST_SCHEME'] . '://' . (extension_loaded('intl') ? idn_to_utf8($_SERVER['HTTP_HOST']) : $_SERVER['HTTP_HOST']) . (!empty($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : null) . urldecode($_SERVER['REQUEST_URI']);
		
		$urlparse = Url::parseUrl($url);
		
		$this -> scheme = $urlparse['scheme'];
		$this -> host = $urlparse['host'];
		$this -> www = Strings::find($urlparse['host'], 'www.', 0);
			
		$this -> user = $urlparse['user'];
		$this -> password = $urlparse['password'];
		$this -> port = $urlparse['port'];
			
		$this -> path = [
			'base' => null,
			'string' => Strings::unfirst($urlparse['path']),
			'array' => []
		];
		
		$this -> path['array'] = $this -> path['string'] ? Strings::split($this -> path['string'], '\/', true) : [];
		
		$this -> query = [
			'string' => !empty($urlparse['query']) ? '?' . $urlparse['query'] : null,
			'array' => $_GET
		];
		
		$this -> fragment = $urlparse['fragment'];
			
		$this -> domain = $urlparse['scheme'] . '://' . $urlparse['host'] . '/';
		
		unset($url, $urlparse);
		
	}
	
}

?>