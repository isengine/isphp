<?php

namespace is\Controller\Drivers;

use is\Controller\Driver;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;

class LocalDB extends Driver {
	
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
		
		$query = [
			'id'      => null,
			'name'    => null,
			'type'    => null,
			'parents' => null,
			'owner'   => null,
			'ctime'   => null,
			'mtime'   => null,
			'dtime'   => null
		];
		
		foreach ($query as $key => &$item) {
			$item = Objects::convert($this -> $key);
		}
		unset($key, $item);
		
		$cache = null;
		
		$prepared = null;
		
		if ($this -> cache && $this -> cachestorage) {
			$prepared = $this -> readListFromCache($query);
		}
		
		if (!$prepared && !is_array($prepared)) {
			$prepared = $this -> createList($query);
			if ($this -> cache && $this -> cachestorage) {
				// ЭТО УСЛОВИЯ, ВОЗМОЖНО, НУЖНО ПЕРЕДЕЛАТЬ ПОД ПРОВЕРКУ СУЩЕСТВОВАНИЯ ФАЙЛА
				// Т.К. БУДЕТ ЕЩЕ ДОБАВЛЯТЬСЯ ОБРАБОТКА С ФИЛЬТРАЦИЕЙ МАССИВА ПО УКАЗАННЫМ ДАННЫМ
				// ЛИБО ЭТУ ФИЛЬТРАЦИЮ ПЕРЕНЕСТИ В createList
				$this -> writeListToCache($query, $prepared);
			}
		}
		
		if (!is_array($prepared)) {
			$prepared = [];
		}
		
		//$this -> data = $prepared;
		
		// ЕЩЕ НУЖНО СДЕЛАТЬ ФИЛЬТРАЦИЮ И ОТБОР ПО УКАЗАННЫМ QUERY ДАННЫМ
		// ЕЩЕ НУЖНО createFileFromInfo
		// ДЛЯ ПОДГОТОВКИ ФАЙЛА К ЗАПИСИ
		
		//echo $name . '<br>';
		//echo print_r($query, 1) . '<br>';
		//echo '<pre>';
		//echo print_r($prepared, 1) . '<br>';
		//echo '</pre>';
		
	}

	private function readListFromCache($query) {
		
		$json = json_encode($query);
		$cache = md5($json) . '.' . Strings::len($json);
		unset($json);
		
		$file = $this -> cachestorage . $this -> collection . DS . $cache . '.ini';
		
		if (file_exists($file)) {
			$data = Local::openFile($file);
			return Parser::fromJson($data);
		}
		
	}
	
	private function writeListToCache($query, $data) {
		
		$json = json_encode($query);
		$cache = md5($json) . '.' . Strings::len($json);
		unset($json);
		
		$file = $this -> cachestorage . $this -> collection . DS . $cache . '.ini';
		
		$data = Parser::toJson($data, true);
		
		Local::createFile($file, $data);
		Local::saveFile($file, $data, 'replace');
		
	}
	
	private function createList($query) {
		
		//echo print_r($query, 1);
		
		$path = $this -> path . $this -> collection . DS;
		
		$list = [];
		$folders = [];
		$files = [];
		
		if ($query['parents']) {
			foreach ($query['parents'] as $item) {
				$folders[] = $path . $item . DS;
			}
			unset($item);
		} else {
			$folders[] = $path;
		}
		
		foreach ($folders as $item) {
			$n = Local::list($item, ['return' => 'files', 'savepath' => true]);
			$files = array_merge($files, $n);
		}
		unset($item);
		
		foreach ($files as $key => $item) {
			$entry = $this -> createInfoFromFile($item, $key, $query['parents']);
			// ВОТ ЭТУ ПРОЦЕДУРУ ПЕРЕРАБОТАТЬ
			// СЮДА ДОБАВИТЬ УСЛОВИЕ ПРОВЕРКИ НА ФИЛЬТРЫ query И ДОБАВЛЕНИЯ В СПИСОК
			
			echo '<pre>' . print_r($query, 1) . '</pre>';
			echo '<pre>' . print_r($entry, 1) . '</pre>';
			echo '<hr>';
			
			$list[] = $entry;
		}
		unset($key, $item);
		
		unset($folders, $files);
		
		return $list;
		
	}

	private function createInfoFromFile($item, $key, $parents) {
		
		$stat = stat($item);
		$info = pathinfo($item)['filename'];
		
		$parse = Strings::split($info, '\.');
		$first = Objects::first($parse, 'value');
		$second = Objects::n($parse, 1, 'value');
		
		if (
			!is_numeric($first) &&
			!is_numeric($second)
		) {
			$parse = Objects::add([$key], $parse);
		}
		
		foreach ($parse as &$i) {
			$i = str_replace(['--', ' '], ['.', ':'], $i);
		}
		unset($i);
		
		$parse = Objects::combine($parse, [
			'id',
			'name',
			'type',
			'owner',
			'dtime',
		]);
		
		return [
			'path' => $item,
			'parents' => $parents,
			'id' => $parse['id'],
			'name' => str_replace(['--'], ['.'], $parse['name']),
			'type' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['type'])),
			'owner' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['owner'])),
			'ctime' => $stat['ctime'],
			'mtime' => $stat['mtime'],
			'dtime' => $parse['dtime'],
		];
		
	}

}

?>