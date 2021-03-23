<?php

namespace is\Model\Databases;

use is\Model\Parents\Data;
use is\Helpers\Local;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\System;
use is\Helpers\Prepare;
use is\Helpers\Match;

abstract class Driver extends Data {
	
	/*
	это фактически интерфейс драйвера
	
	работаем с подготовленными запросами
	пока это происходит так
	заполняем данные в публичные свойства
	затем остается вызвать метод launch, который сформирует эти данные в готовый запрос, записав его в строку prepare
	и по этой строке соединится с базой данных
	возвращенные данные будут записаны в массив $data
	
	идея прав такова, что доступ будет назначаться только к тем полям и записям базы, которые разрешены
	но это будет происходить снаружи, т.е. не в драйвере
	то же самое касается фильтрации, сортировки и обрезки значений
	кстати, возвращенные данные должны быть перенесены и записаны в коллекцию
	
	чтение - здесь все понятно
	запись - имеется ввиду запись и перезапись существующих
	добавление - только новая запись, если такая запись уже есть, то она не перезаписывается
	удаление - здесь тоже все понятно
	
	чтобы создать и подключить свой собственный драйвер, нужно создать класс, наследующий данный класс
	и поместить его в пространство имен is\Model\Databases\Drivers
	подключить данный файл (или поместить его в папку фреймворка, что не рекомендуется)
	а затем, если вы работаете с ядром, проинициализировать его в настройках ядра
	
	в дальнейшем мы добавим классы по работе через PDO и, возможно, подключим сторонние библиотеки в проект
	*/
	
	public $prepare; // здесь должен будет храниться подготовленный запрос
	public $settings; // настройки подключения
	public $cache; // кэшированные данных
	public $cached; // триггер, что запрос прокеширован
	public $hash; // хэш, контрольная сумма запроса, по которой запрос будет искаться в кэше
	
	public $query; // тип запроса в базу данных - чтение, запись, добавление, удаление
	public $collection; // раздел базы данных
	
	/*
	public $id; // имя или имена записей в базе данных
	public $name; // имя или имена записей в базе данных
	public $type; // тип или типы записей в базе данных
	public $parents; // родитель или родители записей в базе данных
	public $owner; // владелец или владельцы записей в базе данных
	
	public $ctime; // дата и время (в формате unix) создания записи в базе данных
	public $mtime; // дата и время (в формате unix) последнего изменения записи в базе данных
	public $dtime; // дата и время (в формате unix) удаления записи в базе данных
	*/
	
	public $owner; // имя текущего владельца
	
	public $rights; // права доступа к базе данных
	public $filter; // параметры фильтрации результата из базы данных
	public $fields; // параметры правил обработки полей
	public $format; // формат данных в json (это может быть контент или структура)
	
	public function __construct($settings) {
		$this -> settings = $settings;
		$this -> filter = [
			'method' => 'and',
			'filters' => []
		];
	}
	
	abstract public function connect();
	abstract public function close();
	abstract public function launch();
	
	abstract public function prepare();
	
	public function cache($path) {
		if (!file_exists($path)) {
			Local::createFolder($path);
		}
		$this -> cache = $path;
	}
	
	public function readCache() {
		$path = $this -> cache . $this -> collection . DS . $this -> hash . '.ini';
		if (file_exists($path)) {
			$this -> cached = true;
			//$file = Local::readFile($path);
			//return Parser::fromJson($file);
			foreach (Local::readFileGenerator($path) as $line) {
				$parse = Parser::fromJson($line);
				if ($parse) {
					$this -> addData($parse);
				}
			}
		}
	}
	
	public function writeCache() {
		$file = $this -> cache . $this -> collection . DS . $this -> hash . '.ini';
		Local::createFile($file);
		//$data = Local::writeFileGenerator($file);
		foreach ($this -> data as $item) {
			$parse = Parser::toJson($item);
			Local::writeFile($file, $parse . "\n", 'append'); //
			//$data -> send($parse);
		}
		unset($item, $parse);
		//$data -> send(null);
		//unset($data);
	}
	
	public function hash() {
		$json = json_encode($this -> filter) . json_encode($this -> fields) . json_encode($this -> rights);
		$this -> hash = md5($json) . '.' . Strings::len($json) . '.' . (int) $this -> settings['all'] . '.' . $this -> settings['limit'];
	}
	
	public function read() {
		
		$this -> hash();
		$this -> resetData();
		
		if ($this -> cache) {
			//$this -> data = $this -> readCache();
			$this -> readCache();
		}
		
		if (!$this -> cached) {
			$this -> prepare();
			if ($this -> cache) {
				$this -> writeCache();
			}
		}
		
		if (!is_array($this -> data)) {
			$this -> resetData();
		}
		
	}
	
	public function collection($name) {
		$this -> collection = $name;
	}
	
	public function query($name) {
		$this -> query = $name;
	}
	
	public function settings($key, $item) {
		$this -> settings[$key] = $item;
	}
	
	public function fields($key, $item) {
		$this -> fields[$key] = $item;
	}
	
	public function format($item) {
		$this -> format = $item;
	}
	
	public function rights($rights, $owner = null) {
		$this -> rights = $rights;
		$this -> owner = $owner;
	}
	
	public function setRights() {
		$rights = null;
		if (
			$this -> rights[$this -> collection][$this -> query] ||
			$this -> rights[$this -> collection][$this -> query] === false
		) {
			$rights = $this -> rights[$this -> collection][$this -> query];
		} elseif (
			$this -> rights[$this -> collection] ||
			$this -> rights[$this -> collection] === false
		) {
			$rights = $this -> rights[$this -> collection];
		} elseif (
			$this -> rights['default'][$this -> query] ||
			$this -> rights['default'][$this -> query] === false
		) {
			$rights = $this -> rights['default'][$this -> query];
		} elseif (
			$this -> rights['default'] ||
			$this -> rights['default'] === false
		) {
			$rights = $this -> rights['default'];
		} elseif (
			$this -> rights[$this -> query] ||
			$this -> rights[$this -> query] === false
		) {
			$rights = $this -> rights[$this -> query];
		} else {
			$rights = $this -> rights;
		}
		return $rights;
	}
	
	public function rightsEntry($entry) {
		if (!$entry || !$this -> rights) {
			return null;
		}
		
		$rights = $this -> setRights();
		
		$owner = $entry['owner'] && System::typeOf($entry['owner'], 'iterable') ? Objects::match($entry['owner'], $this -> owner) : $entry['owner'] === $this -> owner;
		
		// теперь нет allow и deny - правила строятся на основе фильтров
		// owner также разрешает доступ к записям, если совпадает владелец (имя)
		// и он также в приоритете
		
		if ($rights['owner'] && $this -> owner && $owner) {
			// если это условие убрать, то приоритеты проверки сломаются
		} elseif (is_array($rights)) {
			
			if (is_array($rights['filters'])) {
				
				// сохраняем настройки и значения фильтров
				
				$filters = $this -> filter;
				
				// задаем новые
				
				$this -> resetFilter();
				if ($rights['method']) {
					$this -> methodFilter($rights['method']);
				}
				unset($rights['method']);
				
				// здесь делаем добавление правил из $rights в фильтры
				
				foreach ($rights['filters'] as $key => $item) {
					if (!is_array($item)) {
						$this -> addFilter($key, $item);
					}
				}
				unset($key, $item);
				
				// здесь делаем проверку по фильтру
				
				if (!$this -> filter($entry)) {
					$entry = null;
				}
				
				// возвращаем сохраненные настройки и значения фильтров
				
				$this -> filter = $filters;
				
			}
			
			// здесь очищаем поля deny
			// либо оставляем только поля allow
			// это касается только ключей полей данных записи, ключи исключения передаются в массиве
			
			$allow = $rights['allow'] && is_array($rights['allow']);
			$deny = $rights['deny'] && is_array($rights['deny']);
			
			if (
				($allow || $deny) && $entry['data'] && is_array($entry['data'])
			) {
				foreach ($entry['data'] as $key => $item) {
					if (
						$allow && !Match::maskOf($key, $rights['allow']) ||
						$deny && Match::maskOf($key, $rights['deny'])
					) {
						unset($entry['data'][$key]);
					}
				}
				unset($key, $item);
			}
			
		} elseif (!$rights) {
			$entry = null;
		}
		
		//echo '[' . $this -> collection . ':' . print_r($entry, 1) . ']<br><br>';
		return $entry;
		
	}
	
	public function methodFilter($name) {
		$this -> filter['method'] = $name;
	}
	
	public function resetFilter() {
		$this -> filter['filters'] = [];
	}
	
	public function addFilter($name = null, $data = null) {
		
		if (is_array($name)) {
			$this -> filter['filters'][] = $name;
		} else {
			
			$item = [
				'name' => null,
				'data' => null,
				'values' => []
			];
			
			$array = Parser::fromString($name);
			$item['name'] = Objects::first($array, 'value');
			if ($item['name'] === 'data') {
				$item['name'] = Objects::n($array, 1, 'value');
				$item['data'] = true;
			}
			unset($array);
			
			if ($data) {
				$array = Parser::fromString($data);
				foreach ($array as $i) {
					
					$value = [
						'name' => null,
						'type' => 'equal',
						'require' => null,
						'except' => null
					];
					
					$first = Strings::first($i);
					$num = Strings::match($i, '_');
					
					if (
						$first === '+' ||
						$first === '-'
					) {
						$value['except'] = $first === '-' ? true : null;
						$value['require'] = true;
						$i = Strings::unfirst($i);
						$value['name'] = $i;
						$first = Strings::first($i);
					}
					
					if ($first === '*') {
						$value['type'] = 'string';
						$value['name'] = Strings::unfirst($i);
					} elseif ($num) {
						$value['type'] = 'numeric';
						$value['name'] = Strings::split($i, '_');
						foreach ($value['name'] as &$ii) {
							if ($ii) {
								$ii = Prepare::numeric($ii);
							}
						}
						unset($ii);
					} else {
						$value['name'] = $i;
					}
					
					$item['values'][] = $value;
					
					unset($value);
					
				}
				unset($i, $array);
			} else {
				$item['values'][] = ['type' => 'noempty'];
			}
			
			$this -> filter['filters'][] = $item;
			
			unset($item);
			
			//echo '<pre>' . print_r($item, 1) . '</pre><br>';
			//$this -> driver -> $name = $data;
			
		}
		
	}
	
	public function filter($entry) {
		
		if (!$entry || !$this -> filter || !$this -> filter['filters']) {
			return true;
		}
		
		$and = $this -> filter['method'] === 'and';
		
		$tpass = $and;
		
		foreach ($this -> filter['filters'] as $key => $item) {
			
			if ($item['data']) {
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
				
				if ($i['except']) {
					$pass = !$pass;
				}
				
				if ($i['require'] && !$pass) {
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
			
			if ($and && $gpass && $tpass) {
				$tpass = true;
			} elseif (!$and && ($gpass || $tpass)) {
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
		
		return $tpass;
		
	}
	
	public function verifyFields(&$entry) {
		
		if (!$entry || !$this -> fields) {
			return null;
		}
		
		$keys_needed = Objects::keys($this -> fields);
		$keys_data = Objects::keys($entry['data']);
		
		//echo print_r($keys_needed, 1) . '<br>';
		//echo print_r($keys_data, 1) . '<br>';
		
		$keys = array_diff($keys_needed, $keys_data);
		//echo print_r($keys, 1) . '<br>';
		
		if ($keys) {
			$entry['data'] = Objects::add(
				$entry['data'],
				Objects::fill($keys, null)
				//Objects::combine([], $keys, null)
			);
		}
		
		if ($entry['data']) {
			foreach ($entry['data'] as $key => &$item) {
				
				$field = $this -> fields[$key];
				
				if (!$field) {
					continue;
				}
				
				if ($field['default'] && !System::set($item)) {
					$item = $field['default'];
				}
				
				if ($field['exclude']) {
					$item = null;
					unset($entry['data'][$key]);
				}
				
				if ($item) {
					
					if ($field['convert'] === 'array') {
						$item = Objects::convert($item);
					} elseif ($field['convert'] === 'json') {
						$item = Parser::toJson($item);
					} elseif ($field['convert'] === 'string') {
						$item = Strings::join($item);
					} elseif ($field['convert'] === 'first') {
						$item = Objects::first($item);
					} elseif ($field['convert'] === 'last') {
						$item = Objects::last($item);
					}
					
					if ($field['prepare']) {
						foreach ($field['prepare'] as $i) {
							$item = Prepare::$i($item);
						}
						unset($i);
					}
					
					if ($field['match'] && $field['match']['type']) {
						
						if ($field['match']['type'] === 'string') {
							if (!Match::string($item, $field['match']['data'])) {
								$item = null;
							}
						} elseif ($field['match']['type'] === 'numeric') {
							$field['match']['data'] = Objects::convert($field['match']['data']);
							if (!Match::numeric($item, $field['match']['data'][0], $field['match']['data'][1])) {
								if ($field['match']['data'][2]) {
									// т.к. совпадения нет, значит item уже или меньше или больше допустимых пределов
									$item = $item > $field['match']['data'][1] ? $item = $field['match']['data'][1] : $field['match']['data'][0];
								} else {
									$item = null;
								}
							}
						} elseif ($field['match']['type'] === 'len') {
							$field['match']['data'] = Objects::convert($field['match']['data']);
							$n = Prepare::len($item, $field['match']['data'][0], $field['match']['data'][1]);
							if ($item !== $n) {
								if ($field['match']['data'][2]) {
									$item = $n;
								} else {
									$item = null;
								}
							}
						}
						
					}
					
				}
					
					
				//echo '[' . $field . ']<br>';
			}
			unset($key, $item);
		}
		
	}

	public function verifyName($name) {
		
		return !$name || !$this -> settings['all'] && Strings::first($name) === '!' ? null : true;
		
	}

	public function verifyTime($entry) {
		
		$result = true;
		if (!$this -> settings['all']) {
			$time = time();
			if (
				($entry['ctime'] && $entry['ctime'] > $time) ||
				($entry['dtime'] && $entry['dtime'] < $time)
			) {
				$result = null;
			}
		}
		return $result;
		
	}

	public function verify($entry, $count) {
		
		// общая итоговая проверка
		
		// проверка по правам
		$entry = $this -> rightsEntry($entry);
		
		// проверка по фильтру
		if (!$this -> filter($entry)) {
			$entry = null;
		}
		
		// проверка и подготовка полей
		$this -> verifyFields($entry);
		
		// еще раз проверка по имени - контрольная
		if (!$this -> verifyName($entry['name'])) {
			$entry = null;
		}
		
		if ($entry) {
			$this -> addData($entry);
			$count++;
			if ($this -> settings['limit'] && $this -> settings['limit'] <= $count) {
				$count = null;
			}
		}
		
		return $count;
		
	}

}

?>