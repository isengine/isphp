<?php

namespace is\Masters\Drivers;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

use is\Helpers\Parser;
use is\Helpers\Local;

class Localdb extends Master {
	
	protected $path;
	
	public function connect() {
		
		$this -> path = preg_replace('/[\\/]+/ui', DS, DR . str_replace(':', DS, $this -> settings['name']) . DS);
		
	}
	
	public function close() {
		
	}
	
	public function hash() {
		$json = json_encode($this -> filter) . json_encode($this -> fields) . json_encode($this -> rights);
		$this -> hash = md5(filemtime($this -> path . $this -> collection)) . '.' . md5($json) . '.' . Strings::len($json) . '.' . (int) $this -> settings['all'] . '.' . $this -> settings['limit'];
	}
	
	public function read() {
		
		$path = $this -> path . $this -> collection . DS;
		
		$files = [];
		
		$files = Local::search($path, ['return' => 'files', 'extension' => 'ini', 'subfolders' => true, 'merge' => true]);
		//echo '<pre>' . print_r($files, 1) . '</pre>';
		
		$count = 0;
		
		foreach ($files as $key => $item) {
			$entry = $this -> createInfoFromFile($item, $key);
			
			// создание новых полей/колонок и обработка текущих
			$this -> fields($entry);
			
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
			$entry = $this -> verify($entry);
			
			$count = $this -> result($entry, $count);
			if (!System::set($count)) {
				break;
			}
			
		}
		unset($key, $item);
		
		unset($files);
		
	}
	
	public function write($item) {
		
		if (!is_array($item) || !$item['name'] || !$item['data']) {
			return;
		}
		
		// сначала создаем правильное содержимое записи
		// переводим нужные поля в пути
		// затем ищем подходящие файлы в заданном пути
		
		$item = $this -> createInfoForFile($item);
		$files = Local::search($item['path'], ['return' => 'files', 'extension' => 'ini', 'subfolders' => null, 'merge' => true]);
		$first = true;
		$result = null;
		
		foreach ($files as $i) {
			
			// перебираем все файлы и ищем те, которые подходят под нашу запись
			
			$id = Strings::match($i['name'], '.' . $item['name'] . '.');
			$noid = Strings::find($i['name'], $item['name'] . '.', 0);
			
			if (!$id && !$noid) {
				continue;
			}
			
			$data = Parser::fromJson( Local::readFile($i['fullpath']) );
			
			if ($first) {
				
				// первое совпадене берем в работу, остальные - удаляем
				// переименовываем файл, используя полный путь, включая id, type и др.
				// затем записываем туда данные в формате json
				
				$path = $item['path'] . ($id ? $item['id'] . '.' : null) . $item['file'];
				
				if ($i['fullpath'] !== $path) {
					Local::renameFile($i['fullpath'], $path);
				}
				
				$result = $this -> writeDataToFile(
					$path,
					Objects::merge(
						$data ? $data : [],
						$item['data'],
						true
					)
				);
				
				$first = null;
				
			} else {
				Local::deleteFile($i['fullpath']);
			}
			
		}
		unset($files, $i, $id, $noid, $data, $path, $first);
		
		return $result;
		
	}
	
	private function readDataFromFile($path) {
		return Parser::fromJson(
			Local::readFile($path),
			$this -> format ? $this -> format : true
		);
	}
	
	private function writeDataToFile($path, $data) {
		return Local::writeFile(
			$path,
			Parser::toJson(
				$data,
				$this -> format ? $this -> format : true
			),
			'replace'
		);
	}
	
	private function createInfoFromFile($item, $key) {
		
		$stat = stat($item['fullpath']);
		
		// здесь мы распарсиваем имя на составляющие по точкам,
		// затем выясняем, есть ли здесь идентификатор
		// и сводим все в массив стандартной записи в базе данных
		
		//echo print_r($item, 1) . '<br>';
		
		$parse = Strings::split($item['file'], '\.');
		
		$first = Objects::first($parse, 'value');
		//$second = Objects::n($parse, 1, 'value');
		$second = Objects::first(Objects::get($parse, 1, 1), 'value');
		
		if (
			!is_numeric($first) ||
			is_numeric($first) && !$second
		) {
			$parse = Objects::add([$key], $parse);
		}
		
		$parse = Objects::join(['id', 'name', 'type', 'owner', 'dtime'], $parse);
		//$parse = Objects::combine($parse, [
		//	'id',
		//	'name',
		//	'type',
		//	'owner',
		//	'dtime',
		//]);
		
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
	
	private function createInfoForFile($item) {
		
		$path = $this -> path . $this -> collection . DS . ($item['parents'] ? Strings::join($item['parents'], DS) . DS : null);
		
		$item['name'] = Strings::replace($item['name'], '.', '--');
		$item['type'] = Strings::replace(Strings::join($item['type'], ' '), '.', '--');
		$item['owner'] = Strings::replace(Strings::join($item['owner'], ' '), '.', '--');
		
		$file = $item['name'] . '.';
		if (System::set($item['type'])) {
			$file .= $item['type'] . '.';
		}
		if (System::set($item['owner'])) {
			$file .= $item['owner'] . '.';
		}
		if ($item['dtime'] && System::type($item['dtime'], 'numeric')) {
			$file .= $item['dtime'] . '.';
		}
		$file .= 'ini';
		
		return [
			'path' => $path,
			'file' => $file,
			'id' => $item['id'],
			'name' => $item['name'],
			'data' => $item['data']
		];
		
	}
	
}

?>