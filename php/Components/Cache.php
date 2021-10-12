<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Helpers\Parser;
use is\Helpers\Local;

use is\Parents\Data;

class Cache extends Data {
	
	// предусмотрено, что данный класс
	// будет являться не наследуемым, а встраиваемым классом
	// т.е. он будет назначаться какому-то свойству (обычно cache) класса верхнего уровня
	
	// как работает кэш
	// при инициализации задаем путь к папке кэширования
	// потому что по правилам фреймворк не должен иметь жестких привязок
	// в отличие от ядра, где мы можем читать путь из настроек
	// папка может включать несколько уровней вложенности
	// далее идет следующая схема
	// - чтение триггера, разрешающего кэширование
	// - создание хэш-суммы, включая изменяемые данные и временную метку
	// - проверка наличия кэша по хэш-сумме
	// - если кэш есть и он совпадает по хэш-сумме, то идет чтение кэша
	// - если кэша нет или хэш-сумма не совпадает, то идет запись кэша
	// запись кэша проходит следующим образом
	// - создается триггер на запись
	// - далее формируются данные, обычный обработчик в классе верхнего уровня
	// - далее при наличии триггера производится запись кэша
	
	// обычное использование
	// $this -> cache = new Cache($path);
	// $this -> cache -> init($name, $settings);
	// $data = $this -> cache -> read();
	// if (!$data) {
	//   ...
	// }
	// $this -> cache -> write($data);
	// $this -> setData($data);
	// unset($data);
	
	// использование для страниц
	// $cache = new Cache($path);
	// $cache -> init($name, $settings);
	// $data = $cache -> start();
	// if (!$data) {
	//   ...
	// }
	// $cache -> end();
	// unset($data);
	
	public $path; // путь к папке кэша
	public $name; // полный путь к файлу кэша
	public $hash; // хэш-сумма
	public $cached; // показатель, было выполнено кэширование или нет
	public $caching; // триггер разрешения кэширования
	public $format; // триггер разрешения форматирования
	public $reset; // триггер перезапуска кэширования, устанавливается через compare
	
	public $self; // просто так
	
	public function __construct($path) {
		
		$this -> path = Paths::toReal($path);
		
		if (!file_exists($path)) {
			Local::createFolder($path);
		}
		
		$this -> cached = null;
		$this -> caching = true;
		$this -> format = true;
		
	}
	
	public function caching($caching = true) {
		
		// включение/выключение кэширования
		
		$this -> caching = $caching ? true : null;
		
	}
	
	public function format($format = true) {
		
		// включение/выключение форматирования
		// например, выключение может понадобиться для вывода напрямую
		
		$this -> format = $format ? true : null;
		
	}
	
	public function hash(...$items) {
		
		// создание хэш-суммы
		
		$json = null;
		
		foreach ($items as $item) {
			$json .= json_encode($item);
		}
		unset($key, $item);
		
		$this -> hash = md5($json) . '.' . Strings::len($json);
		
	}
	
	// мы должны передать файл, нам нужно имя
	// либо мы передаем имя файла
	// либо мы формируем имя файла по хэш-сумме
	// в первом случае, нам нужно сравнить хэш-сумму
	// во втором случае, у нас будет множество файлов и кэш быстро забьется
	
	public function init(...$items) {
		
		if (!$this -> caching) {
			return;
		}
		
		$this -> hash($items);
		
		$this -> name = $this -> path . $this -> hash . '.ini';
		
	}
	
	public function start() {
		$this -> format = null;
		$data = $this -> reset ? null : $this -> read();
		$this -> reset = null;
		if (!$data) {
			if ($this -> cached !== 'start') {
				$this -> cached = 'start';
				ob_start();
			}
		} else {
			echo $data;
			return true;
		}
	}
	
	public function stop() {
		if ($this -> cached === 'start') {
			$data = ob_get_contents();
			ob_end_clean();
			echo $data;
			$this -> cached = null;
		}
		if ($this -> format) {
			return;
		}
		$this -> write($data);
	}
	
	public function compare($original) {
		
		// проверяет mtime
		// время последнего изменения оригинального файла
		// и сравнивает с кэшем
		// если mtime больше, чем время кэша,
		// это значит, что файл подвергался изменениям
		// и кэш надо обновить
		
		$mtime_original = realpath($original) ? filemtime($original) : 0;
		$mtime_cache = realpath($this -> name) ? filemtime($this -> name) : 0;
		$this -> reset = $mtime_original > $mtime_cache;
		
	}
	
	public function read() {
		
		if (
			!$this -> caching ||
			!$this -> name ||
			!file_exists($this -> name)
		) {
			return;
		}
		
		$this -> cached = true;
		
		$content = Local::readFile($this -> name);
		return $this -> format ? Parser::fromJson($content) : $content;
		
		//старый код
		////$file = Local::readFile($this -> name);
		////return Parser::fromJson($file);
		//foreach (Local::readFileGenerator($this -> name) as $line) {
		//	$parse = Parser::fromJson($line);
		//	if ($parse) {
		//		$this -> addData($parse);
		//	}
		//}
		
	}
	
	public function write($data) {
		
		if (
			$this -> cached ||
			!$this -> caching ||
			!$this -> name
		) {
			return;
		}
		
		Local::createFile($this -> name);
		Local::writeFile(
			$this -> name,
			$this -> format ? Parser::toJson($data) : $data,
			'replace'
		);
		
		//старый код
		////$data = Local::writeFileGenerator($this -> name);
		//foreach ($this -> data as $item) {
		//	$parse = Parser::toJson($item);
		//	Local::writeFile($this -> name, $parse . "\n", 'append'); //
		//	//$data -> send($parse);
		//}
		//unset($item, $parse);
		////$data -> send(null);
		////unset($data);
		
	}
	
}

?>