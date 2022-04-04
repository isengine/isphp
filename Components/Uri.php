<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Parents\Globals;

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
	
	public $route;
	
	public $domain;
	
	public $file;
	public $folder;
	
	public $language;
	
	public $url;
	public $previous;
		
	public $reload;
	public $original;
	
	public $rest;
	public $keys;
	
	public function create() {
		
		// получение данных
		
		$url = System::server('domain') . (!empty($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : null) . rawurldecode($_SERVER['REQUEST_URI']);
		
		// rawurldecode декодирует по стандарту RFC 3986
		// в том числе поддерживается гуглом
		// где символ пробела передается кодом %20
		// и в дальнейшем не вызывает конфликтов
		
		// urldecode декодирует не по этому стандарту
		// и символ пробела заменяет на знак +
		// что в дальнейшем приводит к конфликтам
		// при разборе параметов данных, например, в фильтрах
		
		$urlparse = Paths::parseUrl($url);
		
		$this -> scheme = $urlparse['scheme'];
		$this -> host = $urlparse['host'];
		$this -> www = Strings::find($urlparse['host'], 'www.', 0);
			
		$this -> user = $urlparse['user'];
		$this -> password = $urlparse['password'];
		$this -> port = $urlparse['port'];
		
		$this -> setPathArray(Strings::unfirst($urlparse['path']));
		$this -> setQueryArray($urlparse['query'] ? '?' . $urlparse['query'] : '');
		
		$this -> fragment = $urlparse['fragment'];
			
		$this -> setDomain();
		
		unset($url, $urlparse);
		
	}
	
	public function init() {
		$this -> create();
		$this -> setFile();
		$this -> setFolder();
		$this -> setUrl();
		$this -> setRoute();
		$this -> original = $this -> url;
	}
	
	public function setDomain() {
		$this -> domain = $this -> scheme . '://' . $this -> host . '/';
	}
	
	public function setFromString() {
		// нигде не используется
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
		//$this -> query['array'] = $this -> query['string'] ? Objects::split( Strings::split(Strings::unfirst($this -> query['string']), '=&') ) : [];
		$this -> query['array'] = $_GET;
	}
	
	public function setQueryString($data = null) {
		// нигде не используется
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
		if (!System::set($this -> path['array'])) {
			$this -> path['array'] = [];
		}
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
		$array = $this -> route ? $this -> route : $this -> path['array'];
		$this -> file = Paths::parseFile( Objects::last($array, 'value') );
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
		//return !System::set($id) ? $this -> path['array'] : Objects::n($this -> path['array'], $id, 'value');
		return !System::set($id) ? $this -> path['array'] : Objects::first(Objects::get($this -> path['array'], $id, 1), 'value');
	}
	
	public function unPathArray($id = null) {
		$this -> path['array'] = !$id ? Objects::reset( Objects::unfirst($this -> path['array']) ) : Objects::reset( Objects::cut($this -> path['array'], $id, 1) );
	}
	
	public function setRoute() {
		$this -> route = $this -> path['array'];
	}
	
	public function getRoute($id = null) {
		if (!System::set($id)) {
			return $this -> route;
		} elseif ($id === 'first') {
			return Objects::first($this -> route, 'value');
		} elseif ($id === 'last') {
			return Objects::last($this -> route, 'value');
		} else {
			return $this -> route[$id];
		}
	}
	
	public function addRoute($data) {
		$this -> route[] = $data;
	}
	
	public function unRoute($id) {
		if ($id === 'first') {
			$this -> route = Objects::unfirst($this -> route);
		} elseif ($id === 'last') {
			$this -> route = Objects::unlast($this -> route);
		} else {
			$this -> route = Objects::cut($this -> route, $id, 1);
		}
	}
	
	public function resetRoute() {
		$this -> route = Objects::reset($this -> route);
	}
	
	public function setUrl() {
		$this -> url = $this -> domain . ($this -> language ? $this -> language . '/' : null) . $this -> path['string'] . $this -> query['string'];
	}
	
	public function setRest($rest, $keys, $query = true) {
		
		$this -> rest = $rest;
		$this -> keys = $keys;
		
		$data = [];
		$array = null;
		
		$path_array = $this -> getPathArray();
		
		if ( System::type($rest, 'numeric') ) {
			$array = Objects::get($path_array, $rest - 1);
			$this -> route = Objects::get($path_array, 0, $rest - 1);
		} else {
			$find = Objects::find($path_array, $rest);
			if (System::set($find)) {
				$array = Objects::get($path_array, $find + 1);
				$this -> route = Objects::get($path_array, 0, $find);
			}
		}
		
		if ($array) {
			if ($keys) {
				$data = Objects::split($array);
			} else {
				$data = Objects::reset($array);
			}
		}
		
		if ($query) {
			$data = Objects::merge($data, $this -> query['array']);
			if (System::server('method') === 'post') {
				$data = Objects::merge($data, $_POST);
			}
		}
		
		$this -> setData($data);
		
		unset($data, $array);
		
		$this -> setFile();
		$this -> setFolder();
		
	}
	
}

?>