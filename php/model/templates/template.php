<?php

namespace is\Model\Templates;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Paths;
use is\Model\Components\Language;
use is\Model\Components\Router;
use is\Model\Components\Uri;
use is\Model\Parents\Singleton;
use is\Model\Templates\View;

class Template extends Singleton {
	
	public $viewname;
	public $view;
	public $path;
	
	public $lang;
	public $router;
	public $uri;
	
	public function init($viewname = null, $path = null) {
		$this -> viewname = $viewname ? $viewname : 'default';
		$this -> lang = Language::getInstance();
		$this -> router = Router::getInstance();
		$this -> uri = Uri::getInstance();
		if ($path) {
			$this -> setRealPath($path);
		}
	}
	
	public function launch() {
		$viewname = __NAMESPACE__ . '\\Views\\' . $this -> viewname;
		$this -> view = new $viewname;
	}
	
	// группа работы с языком
	
	public function langName() {
		return $this -> lang -> lang;
	}
	
	public function langCode($lang = null) {
		if (!$lang) {
			return $this -> lang -> code;
		} else {
			$name = $this -> lang -> list[$lang];
			return $this -> lang -> codes[$name];
		}
	}
	
	public function langList() {
		return Objects::keys($this -> lang -> settings);
	}
	
	public function langListAll() {
		return $this -> lang -> list;
	}
	
	public function lang($data) {
		$data = Parser::fromString($data);
		$array = $this -> lang -> getData();
		return Objects::extract($array, $data);
	}
	
	// группа работы с uri
	
	public function uri() {
		return $this -> uri -> url;
	}
	
	public function scheme() {
		return $this -> uri -> scheme;
	}
	
	public function host() {
		return $this -> uri -> host;
	}
	
	public function domain() {
		return $this -> uri -> domain;
	}
	
	public function previous() {
		return $this -> uri -> previous;
	}
	
	public function uriPath() {
		return $this -> uri -> path['string'];
	}
	
	public function uriPathArray() {
		return $this -> uri -> path['array'];
	}
	
	public function query() {
		return $this -> uri -> query['string'];
	}
	
	public function queryArray() {
		return $this -> uri -> query['array'];
	}
	
	public function data() {
		return $this -> uri -> data;
	}
	
	// группа работы с абсолютным путем
	
	public function setRealPath($path) {
		$this -> path = $path;
	}
	
	public function getRealPath() {
		return $this -> path;
	}
	
	// группа работы с роутером
	
	public function route() {
		return $this -> router -> route;
	}
	
	public function template() {
		return $this -> router -> template['name'];
	}
	
	public function section() {
		return $this -> router -> template['section'];
	}
	
	public function name() {
		$entry = System::typeClass($this -> router -> current, 'entry');
		return $entry ? $this -> router -> current -> getEntryData('name') : null;
	}
	
	public function parents() {
		$entry = System::typeClass($this -> router -> current, 'entry');
		return $entry ? $this -> router -> current -> getEntryKey('parents') : null;
	}
	
	public function type() {
		$entry = System::typeClass($this -> router -> current, 'entry');
		return $entry ? $this -> router -> current -> getEntryKey('type') : null;
	}
	
	public function nameByLang() {
		$name = $this -> name();
		return $name ? $this -> lang('menu:' . $name) : null;
	}
	
	public function parentsByLang() {
		$parents = $this -> parents();
		$result = [];
		if (System::typeIterable($parents)) {
			foreach ($parents as $item) {
				$name = $this -> lang('menu:' . $item);
				$result[] = $name ? $name : $item;
			}
			unset($key, $item);
		}
		return $result;
	}
	
	public function routeByLang() {
		$route = $this -> route();
		$result = [];
		if (System::typeIterable($route)) {
			foreach ($route as $item) {
				$name = $this -> lang('menu:' . $item);
				$result[] = $name ? $name : $item;
			}
			unset($key, $item);
		}
		return $result;
	}
	
}

?>