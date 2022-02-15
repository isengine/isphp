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
use is\Components\Cache;
use is\Components\Language;

class Module extends Singleton {
	
	// общие установки кэша
	
	public $path; // путь до папки модуля
	public $custom; // путь до кастомной папки модуля
	public $cache_path; // путь до кэша
	public $caching; // разрешение кэширования по-умолчанию
	
	public $settings; // настройки модуля
	
	public function init($path, $custom, $cache_path, $caching = false) {
		$this -> path = $path;
		$this -> custom = $custom;
		$this -> cache_path = $cache_path;
		$this -> caching = $caching;
	}
	
	public function launch($names, $data = null, $settings = null, $caching = 'default') {
		
		$names = Parser::fromString($names);
		$data = Parser::fromString($data, ['simple' => null]);
		
		$name = $names[0];
		$vendor = $names[1] ? $names[1] : 'isengine';
		$instance = $data[0] ? $data[0] : 'default';
		$template = $data[1] ? $data[1] : $instance;
		$assets = $data[2] ? $data[2] : $template;
		
		if (System::typeOf($instance, 'iterable')) {
			$instance = Strings::join($instance, ':');
		}
		if (System::typeOf($template, 'iterable')) {
			$template = Strings::join($template, ':');
		}
		if (System::typeOf($assets, 'iterable')) {
			$assets = Strings::join($assets, ':');
		}
		
		$path = $this -> path . $vendor . DS . $name . DS;
		$custom = $this -> custom . Prepare::upperFirst($vendor) . DS . Prepare::upperFirst($name) . DS;
		$cache_path = $this -> cache_path . $vendor . DS . $name . DS;
		
		// проверка манифеста, для защиты модулей от других классов и библиотек
		
		if (!Local::matchFile($path . 'manifest.ini')) {
			if (!Local::matchFile($custom . 'manifest.ini')) {
				return;
			}
		}
		
		// запись данных
		
		$this -> setData([
			'name' => $name,
			'vendor' => $vendor,
			'instance' => $instance,
			'template' => $template,
			'assets' => $assets,
			'settings' => $settings,
			'path' => $path,
			'custom' => $custom
		]);
		
		// сюда же можно добавить кэш
		
		$cache = new Cache($cache_path);
		$cache -> caching($caching === 'default' ? $this -> caching : $caching);
		$cache -> init($this -> getData());
		
		$data = $cache -> start();
		
		if (!$data) {
			
			// запуск модуля
			
			//$settings = $this -> settings($vendor, $name, $instance, $settings);
			//$this -> settings($vendor, $name, $instance, $settings);
			$this -> settings();
			
			$ns = __NAMESPACE__ . '\\Modules\\' . Prepare::upperFirst($vendor) . '\\' . Prepare::upperFirst($name);
			
			$module = new $ns(
				$vendor . ':' . $name . ':' . $instance,
				$template,
				$this -> settings,
				$path,
				$custom
			);
			
			$return = $module -> launch();
			
			// мы добавили, на первый взгляд, ненужную переменную
			// т.к. обычно модуль ничего не возвращает
			// но благодаря этой переменной, мы можем заставить модуль
			// отменить загрузку шаблона
			// это может оказаться полезным в ряде случаев,
			// например в модуле контента, когда мы делаем кеширование контента
			// средствами самого модуля и хотим загружать готовую страницу,
			// и в других подобных случаях
			
			// require template path in apps and next path template in vendor
			if ( !$return ) {
				$module -> template();
				//if ( !System::includes($template, $custom . 'templates', null, $module) ) {
				//	if ( !System::includes($template, $path . 'templates', null, $module) ) {
				//		if ( !System::includes('default', $custom . 'templates', null, $module) ) {
				//			System::includes('default', $path . 'templates', null, $module);
				//		}
				//	}
				//}
			}
			
			unset($module);
			
		}
		
		$cache -> stop();
		
		$this -> assets();
		
		$this -> settings = null;
		$this -> resetData();
		
		// и еще не хватает чтения манифестов с проверкой на загруженные в системе необходимые библиотеки
		// не хватает процессинга view в конфиге, не знаю, как его сделать,
		// на данный момент представляется либо через foreach готовых настроек, либо через сериализацию-десериализацию массива
		// либо уже оставить обработку при выводе шаблона, хотя это не достаточно автоматизирует процесс
		// чтение языковых файлов сейчас возможно через чтение языков или через view, а дополнительные языковые переменные прописывать в конфиге
		// и еще наверняка понадобится рендеринг, хотя бы в виде переноса, файлов
		
	}
	
	public function assets() {
		
		// метод проверки и вызова ассетов
		// ассеты в данном случае - это дополнения к модулям,
		// которые будут делать что-либо уже после кэширования и вывода шаблона
		
		// раньше ассеты назначались по имени шаблона,
		// теперь ассеты будут указываться третьим аргументом,
		// наследуемым от шаблона, если шаблон не укзан
		
		$custom = $this -> getData('custom');
		$path = $this -> getData('path');
		$assets = Strings::replace($this -> getData('assets'), ':', DS);
		
		$path = [$custom, $path];
		
		foreach ($path as $i) {
			$i = Paths::toReal($i . 'assets' . DS . $assets . '.php');
			if (Local::matchFile($i)) {
				if (!$this -> settings) {
					$this -> settings();
				}
				require($i);
				break;
			}
		}
		unset($i);
		
	}
	
	public function settings() {
		
		$vendor = $this -> getData('vendor');
		$name = $this -> getData('name');
		$instance = Strings::replace($this -> getData('instance'), ':', DS);
		$settings = $this -> getData('settings');
		
		// сюда же можно добавить кэш
		// нужно добавить в настройки текстовые переменные
		
		// read from custom path
		
		$path = $this -> custom . Prepare::upperFirst($vendor) . DS . Prepare::upperFirst($name) . DS . 'data' . DS . $instance . '.ini';
		$path = Local::readFile($path);
		$data = $path ? Parser::fromJson($path) : null;
		unset($path);
		
		// read from database
		
		if (!$data) {
			$data = $this -> dbread();
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
		
		$this -> settings = Objects::merge($data, $settings, true);
		
		// нужно добавить в настройки преобразование языков "val" : {"ru" : "..."} -> "val" : "..."
		$lang = Language::getInstance();
		$this -> settings = Parser::prepare($this -> settings, $lang -> lang);
		
	}
	
	public function dbread() {
		
		$instance = $this -> getData('instance');
		$name = $instance;
		$parents = '+' . $this -> getData('vendor') . ':+' . $this -> getData('name');
		
		if (Strings::match($instance, ':')) {
			$instance = Strings::split($instance, ':');
			$name = Objects::last($instance, 'value');
			$parents .= ':+' . Strings::join(Objects::cut($instance, -1), ':+');
		}
		
		unset($instance);
		
		$db = Database::getInstance();
		
		$db -> collection('modules');
		$db -> driver -> filter -> addFilter('name', '+' . $this -> getData('instance'));
		$db -> driver -> filter -> addFilter('parents', $parents);
		$db -> launch();
		$result = $db -> data -> getFirstData();
		$db -> clear();
		
		return $result;
		
	}
	
}

?>