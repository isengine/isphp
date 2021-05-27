<?php

namespace is\Masters;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Match;
use is\Helpers\Paths;
use is\Helpers\Prepare;
use is\Parents\Singleton;
use is\Masters\Database;

class Module extends Singleton {
	
	// общие установки кэша
	
	public $path; // путь до папки модуля
	public $custom; // путь до кастомной папки модуля
	public $cache; // путь до кэша
	
	public function init($path, $custom, $cache) {
		$this -> path = $path;
		$this -> custom = $custom;
		$this -> cache = $cache;
	}
	
	public function launch($names, $data = null, $settings = null, $caching = null) {
		
		$names = Parser::fromString($names);
		$data = Parser::fromString($data);
		
		$name = $names[0];
		$vendor = $names[1] ? $names[1] : 'isengine';
		$instance = $data[0] ? $data[0] : 'default';
		$template = $data[1] ? $data[1] : $instance;
		
		$settings = $this -> settings($vendor, $name, $instance, $settings);
		$path = $this -> path . $vendor . DS . $name . DS;
		$custom = $this -> custom . $vendor . DS . $name . DS;
		$cache = $this -> cache . $vendor . DS . $name . DS;
		
		// сюда же можно добавить кэш
		
		$ns = __NAMESPACE__ . '\\Modules\\' . Prepare::upperFirst($vendor) . '\\' . Prepare::upperFirst($name);
		
		$module = new $ns(
			$instance,
			$template,
			$settings,
			$path,
			$custom,
			$cache
		);
		
		$module -> launch();
		
		// require template path in apps and next path template in vendor
		
		if ( !System::includes($template, $custom . 'templates', null, $module) ) {
			if ( !System::includes($template, $path . 'templates', null, $module) ) {
				if ( !System::includes('default', $custom . 'templates', null, $module) ) {
					System::includes('default', $path . 'templates', null, $module);
				}
			}
		}
		
		// и еще не хватает чтения манифестов с проверкой на загруженные в системе необходимые библиотеки
		// не хватает процессинга view в конфиге, не знаю, как его сделать,
		// на данный момент представляется либо через foreach готовых настроек, либо через сериализацию-десериализацию массива
		// либо уже оставить обработку при выводе шаблона, хотя это не достаточно автоматизирует процесс
		// чтение языковых файлов сейчас возможно через чтение языков или через view, а дополнительные языковые переменные прописывать в конфиге
		// и еще наверняка понадобится рендеринг, хотя бы в виде переноса, файлов
		
	}
	
	public function settings($vendor, $name, $instance, $settings = null) {
		
		// сюда же можно добавить кэш
		// нужно добавить в настройки текстовые переменные
		// нужно добавить в настройки преобразование языков "val" : {"ru" : "..."} -> "val" : "..."
		
		// read from custom path
		
		$path = $this -> custom . $vendor . DS . $name . DS . 'data' . DS . $instance . '.ini';
		$path = Local::readFile($path);
		$data = $path ? Parser::fromJson($path) : null;
		unset($path);
		
		// read from database
		
		if (!$data) {
			$data = $this -> dbread($vendor, $name, $instance);
		}
		
		// read from original path
		
		if (!$data) {
			$path = $this -> path . $vendor . DS . $name . DS . 'data' . DS . $instance . '.ini';
			$path = Local::readFile($path);
			//$path = Local::readFileGenerator($path);
			$data = $path ? Parser::fromJson($path) : null;
			unset($path);
		}
		
		if (System::typeOf($settings, 'scalar')) {
			$settings = Parser::fromJson($settings);
		}
		
		return Objects::merge($data, $settings, true);
		
	}
	
	public function dbread($vendor, $name, $instance) {
		
		$db = Database::getInstance();
		
		$db -> collection('modules');
		$db -> driver -> filter -> addFilter('name', '+' . $instance);
		$db -> driver -> filter -> addFilter('parents', '+' . $vendor . ':+' . $name);
		$db -> launch();
		$result = $db -> data -> getFirstData();
		$db -> clear();
		
		return $result;
		
	}
	
}

?>