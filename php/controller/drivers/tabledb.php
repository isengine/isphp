<?php

namespace is\Controller\Drivers;

use is\Controller\Driver;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Match;

class TableDB extends Driver {
	
	protected $path;
	
	public function connect() {
		
		$this -> path = preg_replace('/[\\/]+/ui', DS, DR . str_replace(':', DS, $this -> settings['name']) . DS);
		
	}
	
	public function close() {
		
	}
	
	public function launch() {
		
		/*
		protected $prepare;
		protected $settings;
		
		public $query; // тип запроса в базу данных - чтение, запись, добавление, удаление
		public $collection; // раздел базы данных
		
		public $id; // имя или имена записей в базе данных
		public $name; // имя или имена записей в базе данных
		public $type; // тип или типы записей в базе данных
		public $parents; // родитель или родители записей в базе данных
		public $owner; // владелец или владельцы записей в базе данных
		
		public $ctime; // дата и время (в формате unix) создания записи в базе данных
		public $mtime; // дата и время (в формате unix) последнего изменения записи в базе данных
		public $dtime; // дата и время (в формате unix) удаления записи в базе данных
		
		public $limit; // установить возвращаемое количество записей в базе данных
		*/
		
		if (!$this -> collection) {
			return;
		}
		
		if ($this -> query === 'read') {
			
			$json = json_encode($this -> filter);
			$hash = md5($json) . '.' . Strings::len($json);
			unset($json);
			
			$prepared = null;
			
			if ($this -> cache) {
				$prepared = $this -> readListFromCache($hash);
			}
			
			if (!$prepared && !is_array($prepared)) {
				$prepared = $this -> createList();
				if ($this -> cache) {
					$this -> writeListToCache($hash, $prepared);
				}
			}
			
			if (!is_array($prepared)) {
				$prepared = [];
			}
			
			$this -> data = $prepared;
			unset($prepared);
			
		}
		
		// ЕЩЕ НУЖНО СДЕЛАТЬ ФИЛЬТРАЦИЮ И ОТБОР ПО УКАЗАННЫМ QUERY ДАННЫМ
		// ЕЩЕ НУЖНО createFileFromInfo
		// ДЛЯ ПОДГОТОВКИ ФАЙЛА К ЗАПИСИ
		
		//echo $name . '<br>';
		//echo print_r($query, 1) . '<br>';
		//echo '<pre>';
		//echo print_r($prepared, 1) . '<br>';
		//echo '</pre>';
		
	}

	private function readListFromCache($hash) {
		$file = $this -> cache . $this -> collection . DS . $hash . '.ini';
		return $this -> readDataFromFile($file);
	}
	
	private function writeListToCache($hash, $data) {
		$file = $this -> cache . $this -> collection . DS . $hash . '.ini';
		$data = Parser::toJson($data, true);
		Local::createFile($file, $data);
		Local::saveFile($file, $data, 'replace');
	}
	
	private function createList() {
		
		$path = $this -> path . $this -> collection . '.csv';
		$stat = stat($path);
		
		if ($handle = fopen($path, "r")) {
		
		//$excel = SimpleXLSX::parse($path);
		
		//return [
		//	'parents' => Objects::convert(str_replace(DS, ':', Strings::unlast($item['path']))),
		//	'id' => $parse['id'],
		//	'name' => str_replace('--', '.', $parse['name']),
		//	'type' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['type'])),
		//	'owner' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['owner'])),
		//	'ctime' => $stat['ctime'],
		//	'mtime' => $stat['mtime'],
		//	'dtime' => $parse['dtime'],
		//];
		
		// Общие настройки
		
		$delimiter = $this -> settings['delimiter'] ? $settings['delimiter'] : ',';
		$enclosure = $this -> settings['enclosure'] ? $settings['enclosure'] : '"';
		
		$rowkeys = $this -> settings['rowkeys'] ? $this -> settings['rowkeys'] : 0;
		
		$rowskip = $this -> settings['rowskip'] ? (is_array($this -> settings['rowskip']) ? $this -> settings['rowskip'] : Objects::convert($this -> settings['rowskip'])) : [];
		
		$index = 0;
		while ($row = fgetcsv($handle, null, $delimiter, $enclosure)) {
			if ($index === $rowkeys) {
				$keys = $row;
				break;
			}
			$index++;
		}
		
		// Построчная обработка
		
		$index = 0;
		rewind($handle);
		while ($row = fgetcsv($handle, null, $delimiter, $enclosure)) {
			
			if (
				$index === $rowkeys ||
				Match::equalIn($rowskip, $index)
			) {
				$index++;
				continue;
			}
			
			$entry = Objects::combine($row, $keys);
			
			if (
				!$entry['name'] ||
				Strings::first($entry['name']) === '!'
			) {
				$entry = null;
			}
			
			if ($entry) {
				foreach ($entry as $k => $i) {
					/*
					// Это условие надо убрать, иначе будут биться любые строки
					// Нужно оставить разбор, как он был задан - через настройки контента
					// КСТАТИ, ЭТИ НАСТРОЙКИ ТАКЖЕ МОЖНО ВНЕСТИ В НАСТРОЙКИ ДРАЙВЕРА
					// И ТОГДА БУДЕТ ОЧЕНЬ КРУТО !!!
					// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
					if (
						Strings::match($i, ':') ||
						Strings::match($i, '|')
					) {
						$i = Parser::fromString($i);
					}
					*/
					if ($this -> settings['encoding']) {
						$i = mb_convert_encoding($i, 'UTF-8', $this -> settings['encoding']);
					}
					if (Strings::match($k, ':')) {
						// А вот это условие оставить - т.к. бьются только ключи и это правильно
						$levels = Parser::fromString($k);
						$entry = Objects::add($entry, Objects::level([], $levels, $i), true);
						unset($entry[$k], $levels);
					} elseif (Objects::match(['type', 'parents', 'owner'], $k)) {
						// Это условие тоже нужно оставить для базовых полей
						if (
							Strings::match($i, ':') ||
							Strings::match($i, '|')
						) {
							$entry[$k] = Parser::fromString($i);
						}
					}
				}
				unset($k, $i);
				
				// несколько обязательных полей
				if (!$entry['ctime']) {
					$entry['ctime'] = $stat['ctime'];
				}
				if (!$entry['mtime']) {
					$entry['mtime'] = $stat['mtime'];
				}
				
			}
			
			//echo '[' . print_r($entry, 1) . ']<br><br>';
			
			if ($entry && $this -> filter) {
				$entry = $this -> filtration($entry);
			}
			if ($entry) {
				//здесь мы не читаем данные - они и так подгружены
				//if (!$entry['data'] && $entry['path']) {
				//	$entry['data'] = $this -> readDataFromFile($entry['path']);
				//}
				$list[] = $entry;
			}
			
			$index++;
			
		}
		}
		
		return $list;
		
	}

	private function readDataFromFile($path) {
		$file = Local::openFile($path);
		return Parser::fromJson($file);
	}
	
	private function createInfoFromFile($item, $key) {
		/*
		$stat = stat($item['fullpath']);
		
		return [
			'path' => $item['fullpath'],
			'parents' => Objects::convert(str_replace(DS, ':', Strings::unlast($item['path']))),
			'id' => $parse['id'],
			'name' => str_replace('--', '.', $parse['name']),
			'type' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['type'])),
			'owner' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['owner'])),
			'ctime' => $stat['ctime'],
			'mtime' => $stat['mtime'],
			'dtime' => $parse['dtime'],
		];
		*/
	}

	private function filtration($entry) {
		
		$method = $this -> filter['method'];
		
		$tpass = $method === 'and';
		
		foreach ($this -> filter['filters'] as $key => $item) {
			
			if ($item['data']) {
				// здесь не нужно читать содержимое файла - данные и так подгружены
				//$entry['data'] = $this -> readDataFromFile($entry['path']);
				$data = $entry['data'][$item['name']];
			} else {
				$data = $entry[$item['name']];
			}
			
			$gpass = null;
			
			foreach ($item['values'] as $i) {
				
				//if ($i['type'] === 'noempty') {
				//	$pass = System::set($data);
				//} elseif ($i['type'] === 'equal') {
				//	$pass = is_array($data) ? Match::equalIn($data, $i['name'], null) : Match::equal($data, $i['name']);
				//} elseif ($i['type'] === 'string') {
				//	$pass = is_array($data) ? Match::stringIn($data, $i['name'], null) : Match::string($data, $i['name']);
				//} elseif ($i['type'] === 'numeric') {
				//	$pass = is_array($data) ? Match::numericIn($data, $i['name'][0], $i['name'][1], null) : Match::numeric($data, $i['name']);
				//}
				
				if ($i['type'] === 'noempty') {
					$pass = System::set($data);
				} else {
					$func = $i['type'] . (is_array($data) ? 'In' : null);
					if ($i['type'] === 'numeric') {
						$pass = Match::$func($data, $i['name'][0], $i['name'][1], null);
					} else {
						$pass = Match::$func($data, $i['name'], null);
					}
					unset($func);
				}
				
				if ($item['except']) {
					$pass = !$pass;
				}
				
				if ($item['require'] && !$pass) {
					$gpass = null;
					break;
				}
				
				if ($pass || $gpass) {
					$gpass = true;
				}
				
				unset($pass);
				//echo '<pre>I:' . print_r($i, 1) . '</pre>';
				
			}
			unset($i);
			
			if ($method === 'and' && $gpass && $tpass) {
				$tpass = true;
			} elseif ($method === 'or' && ($gpass || $tpass)) {
				$tpass = true;
			} else {
				$tpass = null;
			}
			
			unset($gpass);
			
			//echo '<pre>' . print_r($entry, 1) . '</pre>';
			//echo '<pre>' . print_r($item, 1) . '</pre>';
			//echo '<pre>DATA:' . print_r($data, 1) . '</pre>';
			//echo '<pre>RESULT:' . print_r($tpass, 1) . '</pre>';
			//echo '<hr>';
			
		}
		unset($key, $item);
		
		//echo '<pre>RESULT:' . print_r($tpass, 1) . '</pre>';
		// если вернуть пустое значение, то текущая запись не внесется в общий лист
		
		return $tpass ? $entry : null;
		
	}

}

?>