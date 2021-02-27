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
		
		if ($this -> query === 'read') {
			$this -> read();
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
	
	public function prepare() {
		
		$path = $this -> path . $this -> collection . DS;
		
		$files = [];
		
		$files = Local::list($path, ['return' => 'files', 'extension' => 'ini', 'subfolders' => true, 'merge' => true]);
		//echo '<pre>' . print_r($files, 1) . '</pre>';
		
		$count = 0;
		
		foreach ($files as $key => $item) {
			$entry = $this -> createInfoFromFile($item, $key);
			
			// проверка по имени
			if (!$this -> verifyName($entry['name'])) {
				$entry = null;
			}
			
			// проверка по датам
			if (!$this -> verifyTime($entry)) {
				$entry = null;
			}
			
			if ($entry) {
				$entry['data'] = $this -> readDataFromFile($entry['path']);
			}
			
			// контрольная проверка
			$count = $this -> verifyFinal($entry, $count);
			if (!$count) {
				break;
			}
			
		}
		unset($key, $item);
		
		unset($files);
		
	}
	
	private function readDataFromFile($path) {
		$file = Local::readFile($path);
		return Parser::fromJson($file);
	}
	
	private function createInfoFromFile($item, $key) {
		
		$stat = stat($item['fullpath']);
		
		// здесь мы распарсиваем имя на составляющие по точкам,
		// затем выясняем, есть ли здесь идентификатор
		// и сводим все в массив стандартной записи в базе данных
		
		//echo print_r($item, 1) . '<br>';
		
		$parse = Strings::split($item['file'], '\.');
		
		$first = Objects::first($parse, 'value');
		$second = Objects::n($parse, 1, 'value');
		
		if (
			!is_numeric($first) ||
			is_numeric($first) && !$second
		) {
			$parse = Objects::add([$key], $parse);
		}
		
		$parse = Objects::combine($parse, [
			'id',
			'name',
			'type',
			'owner',
			'dtime',
		]);
		
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
		
	}
	
}

?>