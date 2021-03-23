<?php

namespace is\Model\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Model\Parents\Globals;

class Uri extends Globals {
	
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
	
	public $file;
	public $folder;
	
	public $language;
	
	public $url;
	public $previous;
		
	public $reload;
	public $original;
	
	public function create() {
		
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
			//'array' => Objects::reset(Objects::unfirst(Strings::split($urlparse['path'], '\/')))
			'array' => Objects::reset(Strings::split(Paths::clearSlashes($urlparse['path']), '\/'))
		];
		
		$this -> query = [
			'string' => !empty($urlparse['query']) ? '?' . $urlparse['query'] : null,
			'array' => $_GET
		];
		
		$this -> fragment = $urlparse['fragment'];
			
		$this -> domain = $urlparse['scheme'] . '://' . $urlparse['host'] . '/';
		
		unset($url, $urlparse);
		
	}
	
	public function init() {
		$this -> create();
		$this -> setFile();
		$this -> setFolder();
		$this -> setUrl();
		$this -> original = $this -> url;
	}
	
	public function setFromString() {
		$this -> setPathArray();
		$this -> setFile();
		$this -> setFolder();
		$this -> setUrl();
	}
	
	public function setFromArray() {
		$this -> path['array'] = Objects::reset($this -> path['array']);
		$this -> setFile();
		$this -> setPathString();
		$this -> setFolder();
		$this -> setUrl();
	}
	
	public function setQueryArray($data = null) {
		if (System::typeOf($data, 'scalar')) {
			$this -> query['string'] = $data;
		}
		$this -> query['array'] = $this -> query['string'] ? Objects::pairs( Strings::split(Strings::unfirst($this -> query['string']), '=&') ) : [];
	}
	
	public function setQueryString($data = null) {
		if (System::typeIterable($data)) {
			$this -> query['array'] = $data;
		}
		$this -> query['string'] = System::typeIterable($this -> query['array']) ? Strings::combine($this -> query['array'], '&', '=', '?') : null;
	}
	
	public function setPathArray($data = null) {
		if (System::typeOf($data, 'scalar')) {
			$this -> path['string'] = $data;
		}
		$this -> path['array'] = $this -> path['string'] ? Objects::reset(Strings::split(Paths::clearSlashes($this -> path['string']), '\/')) : [];
	}
	
	public function setPathString($data = null) {
		
		if (System::typeIterable($data)) {
			$this -> path['array'] = $data;
		}
		
		$this -> path['string'] = !empty($this -> path['array']) ? Strings::join($this -> path['array'], '/') . (!$this -> file ? '/' : null) : null;
		$this -> path['string'] = preg_replace('/^\/+/ui', null, $this -> path['string']);
		$this -> path['string'] = preg_replace('/\/+/ui', '/', $this -> path['string']);
		
	}
	
	public function setFile() {
		$this -> file = Paths::parseFile( Objects::last($this -> path['array'], 'value') );
		if (!$this -> file['extension']) {
			$this -> file = [];
		}
	}
	
	public function setFolder() {
		$this -> folder = Strings::find($this -> path['string'], '/', -1);
		if (!$this -> path['array'] && !$this -> file) {
			$this -> folder = true;
		}
	}
	
	public function addPathArray($data) {
		$this -> path['array'][] = $data;
	}
	
	public function getPathArray($id = null) {
		return !System::set($id) ? $this -> path['array'] : Objects::n($this -> path['array'], $id, 'value');
	}
	
	public function unPathArray($id = null) {
		$this -> path['array'] = !$id ? Objects::reset( Objects::unfirst($this -> path['array']) ) : Objects::reset( Objects::unn($this -> path['array'], $id) );
	}
	
	public function setUrl() {
		$this -> url = $this -> domain . ($this -> language ? $this -> language . '/' : null) . $this -> path['string'] . $this -> query['string'];
	}
	
}

?>