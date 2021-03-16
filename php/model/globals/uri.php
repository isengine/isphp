<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
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
		
		$url = Paths::host() . (!empty($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : null) . urldecode($_SERVER['REQUEST_URI']);
		
		$urlparse = Paths::parseUrl($url);
		
		$this -> scheme = $urlparse['scheme'];
		$this -> host = $urlparse['host'];
		$this -> www = Strings::find($urlparse['host'], 'www.', 0);
			
		$this -> user = $urlparse['user'];
		$this -> password = $urlparse['password'];
		$this -> port = $urlparse['port'];
			
		$this -> path = [
			'string' => Strings::unfirst($urlparse['path']),
			//'array' => Objects::reset(Strings::split($urlparse['path'], '\/', true))
			'array' => Objects::reset(Objects::unfirst(Strings::split($urlparse['path'], '\/')))
		];
		
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