<?php
namespace is\Helpers;

class Objects {

	static public function associate($item) {
		
		// функция проверяет, является ли массив ассоциативным
		
		$result = null;
		
		if (System::typeData($item, 'object')) {
			foreach (array_keys($item) as $value) {
				if (!is_int($value)) {
					$result = true;
					break;
				}
			}
			unset($value);
		}
		
		return $result;
		
	}

	static public function numeric($item) {
		
		// функция проверяет, является ли массив состоящим из цифр
		
		$result = true;
		
		if (System::typeData($item, 'object')) {
			foreach ($item as $value) {
				if (!is_int($value)) {
					$result = null;
					break;
				}
			}
			unset($value);
		}
		
		return $result;
		
	}

	static public function convert($item) {
		
		// функция преобразует любые входные данные в системный объект
		
		$type = System::typeData($item);
		
		if ($type === 'string') {
			//$item = Parser::fromString($item);
			$item = Parser::fromString($item, ['key' => null, 'clear' => null, 'simple' => true]);
		} elseif ($type === 'json') {
			$item = Parser::fromJson($item);
		} elseif (!$type && System::set($item)) {
			$item = is_object($item) ? json_decode(json_encode($item), true) : [$item];
		}
		
		return $item;
		
	}

	static public function keys($item) {
		
		/*
		*  Функция возврата ключей массива
		*/
		
		return array_keys($item);
		
	}

	static public function values($item) {
		
		/*
		*  Функция возврата значений массива
		*/
		
		return array_values($item);
		
	}

	/*
	static public function match($needle, $haystack, $parameters = ['method' => null, 'haystack' => null, 'needle' => null]) {
	*  Функция проверки на совпадение элемента/ов needle в haystack
	*  needle - условие, может быть числом, строкой, массивом или данными
	*  haystack - исходные данные, в которых производится проверка
	*  параметры управляют проверкой
	*  method - метод поиска:
	*    null/false/true - по-умолчанию, проверка на полное совпадение
	*    search - по-умолчанию, проверка на вхождение строки
	*    range - порверка на соответствие диапазону
	*    regexp - проверка по регулярному выражению
	*/

	static public function match($haystack, $needle) {
		
		// ИСПРАВЛЕННАЯ
		// Функция проверки наличия строки или символа в заданном массиве
		// проверка по нестрогому соответствию, т.е. 3 === '3'
		
		//$result = in_array($needle, $haystack);
		// исходный вариант в ряде случаев осуществляет неправильную проверку,
		// например значение 0 в haystack (чаще в ключах массивов)
		// дает постоянное совпадение с любой не numeric строкой
		// такое поведение недопустимо, поэтому делаем дополнительную проверку
		// и вводим ряд условий
		
		$type = System::type($needle);
		
		if ($type === 'numeric') {
			$result = in_array((float)$needle, $haystack, true) || in_array((string)$needle, $haystack, true);
		} elseif ($type === 'string') {
			$result = in_array($needle, $haystack, true);
		} else {
			$result = in_array($needle, $haystack);
		}
		
		return !System::set($result) || $result === false ? null : true;
		
	}

	static public function matchByIndex($haystack, $needle) {
		
		// Функция проверки наличия строки или символа в ключах заданного массива
		
		$haystack = self::keys($haystack);
		
		$type = System::type($needle);
		
		if ($type === 'numeric') {
			$result = in_array((float)$needle, $haystack, true) || in_array((string)$needle, $haystack, true);
		} elseif ($type === 'string') {
			$result = in_array($needle, $haystack, true);
		} else {
			$result = in_array($needle, $haystack);
		}
		
		return !System::set($result) || $result === false ? null : true;
		
	}

	static public function find($haystack, $needle, $position = null) {
		
		/*
		*  Функция поиска строки или символа в заданном массиве
		*  если задан position, то он ищет соответствие строки в заданном ключе
		*  положительное значение - поиск с начала, от 0
		*  отрицательное значение - поиск с конца, от -1
		*  если position не задан, то возвращает первый ключ/индекс значение с начала
		*  специальное значение 'r' задает возврат индекса/ключа последнего значения в массиве
		*/
		
		$find = array_keys($haystack, $needle);
		//$find = self::keys(self::filter($haystack, $needle));
		
		//echo 'FIND:' . print_r($find, 1) . '<hr>';
		
		if (System::set($position) && $position !== true) {
			if ($position < 0) {
				$position = self::len($haystack) + $position;
			}
			return in_array($position, $find) === false ? null : true;
		} elseif ($position === true) {
			return self::last($find, 'value');
		} else {
			return self::first($find, 'value');
		}
		
	}

	static public function get($haystack, $index, $length = null, $position = null) {
		
		/*
		*  Функция возвращает срез массива по указанному индексу (позиции) и заданной длине
		*  если длина задана, то она смещается
		*  положительное значение - вперед
		*  отрицательное значение - назад
		*  если длина не задана, то возвращается вся строка от индекса и до конца
		*  специальное значение position = true задает $length от конца массива
		*/
		
		$len = System::set($length);
		
		if ($length && !$position) {
			if ($length < 0) {
				$idx = $index;
				$index += $length + 1;
				$length = abs($length);
				if ($idx > 0 && $index < 0) {
					$length = $idx + 1;
					$index = 0;
				} elseif ($length > self::len($haystack)) {
					$length = $idx + 1;
				}
			}
			return array_slice($haystack, $index, $length, true);
		} elseif ($position) {
			return array_slice($haystack, $index, $length ? 0 - $length : null, true);
		} else {
			return array_slice($haystack, $index, $length, true);
		}
		
	}

	static public function cut($haystack, $index, $length = null, $position = null) {
		
		/*
		*  Функция удаления части массива по указанному индексу (позиции) и заданной длине
		*  если длина задана, то она смещается
		*  положительное значение - вперед
		*  отрицательное значение - назад
		*  если длина не задана, то возвращается вся строка от индекса и до конца
		*  специальное значение position = true задает $length от конца массива
		*/
		
		$len = self::len($haystack);
		
		if (!$length) {
			$length = $position ? 0 : $len;
		}
		
		$first = $index < 0 ? $len + $index : $index;
		$last = $first + ($length < 0 ? $length + 1 : $length);
		
		if ($position) {
			$last = $len - abs($length);
		}
		
		if ($first > $last) {
			$point = $first + 1;
			$first = $last;
			$last = $point;
			unset($point);
		}
		
		if ($first < 0) {
			$first = 0;
		} elseif ($first > $len) {
			$first = $len;
		}
		if ($last < 0) {
			$last = 0;
		} elseif ($last > $len) {
			$last = $len;
		}
		
		return array_replace(
			array_slice($haystack, 0, $first, true),
			array_slice($haystack, $last, null, true)
		);
		
	}

	static public function before($haystack, $needle, $include = null, $reverse = null) {
		
		/*
		*  НОВАЯ
		*  Функция, которая возвращает срез массива до первого заданного значения
		*  включение include позволяет включить в массив найденное значение
		*  специальное значение reverse возвращает массив до последнего заданного значения
		*/
		
		$key = self::find($haystack, $needle, $reverse ? 'r' : null);
		
		if (!System::set($key)) {
			return $haystack;
		} elseif (!$key) {
			return null;
		}
		
		$keys = self::keys($haystack);
		$pos = self::find($keys, $key);
		
		$result = self::get($keys, 0, $pos + ($include ? 1 : 0));
		
		return array_intersect_key($haystack, self::join($result, null));
		
	}

	static public function after($haystack, $needle, $include = null, $reverse = null) {
		
		/*
		*  НОВАЯ
		*  Функция, которая возвращает срез массива после первого заданного значения
		*  включение include позволяет включить в массив найденное значение
		*  специальное значение reverse возвращает массив после последнего заданного значения
		*/
		
		$key = self::find($haystack, $needle, $reverse ? 'r' : null);
		
		if (!System::set($key)) {
			return $haystack;
		} elseif (!$key) {
			return null;
		}
		
		$keys = self::keys($haystack);
		$pos = self::find($keys, $key);
		
		$result = self::get($keys, $pos + 1 - ($include ? 1 : 0));
		
		return array_intersect_key($haystack, self::join($result, null));
		
	}

	static public function add($haystack, $needle, $recursive = null) {
		
		/*
		*  Функция добавления значений в начало или в конец массива
		*/
		
		$haystack = self::convert($haystack);
		$needle = self::convert($needle);
		
		return $recursive ? array_merge_recursive($haystack, $needle) : array_merge($haystack, $needle);
		
	}

	static public function remove($haystack, $needle) {
		
		/*
		*  Функция удаления заданных значений из массива
		*/
		
		$haystack = self::convert($haystack);
		$needle = self::convert($needle);
		
		return array_diff($haystack, $needle);
		
	}

	static public function removeByIndex($haystack, $needle, $recursive = null) {
		
		/*
		*  Функция удаления заданных ключей из массива
		*  теперь может работать рекурсивано
		*/
		
		$haystack = self::convert($haystack);
		$needle = self::convert($needle);
		
		foreach ($needle as $item) {
			unset($haystack[$item]);
		}
		unset($item);
		
		if ($recursive) {
			foreach ($haystack as &$item) {
				if (is_array($item)) {
					$item = self::removeByIndex($item, $needle, $recursive);
				}
			}
			unset($item);
		}
		
		return $haystack;
		
	}

	static public function reverse($item) {
		
		/*
		*  Функция разворачивает массив
		*/
		
		return array_reverse($item);
		
	}

	static public function first($item, $result = null) {
		
		/*
		*  Функция возврата первого значения массива
		*/
		
		if (!is_array($item)) { return; }
		
		$key = null;
		$val = null;
		
		if (version_compare(PHP_VERSION, '7.3.0', '<')) {
			
			foreach($item as $k => $i) {
				if ($result !== 'value') {
					$key = $k;
				}
				if ($result !== 'key') {
					$val = $i;
				}
				break;
			}
			
		} else {
			
			if ($result !== 'value') {
				$key = array_key_first($item);
			}
			if ($result !== 'key') {
				$val = reset($item);
			}
			
		}
		
		if ($result === 'key') {
			return $key;
		} elseif ($result === 'value') {
			return $val;
		} else {
			return ['key' => $key, 'value' => $val];
		}
		
	}

	static public function last($item, $result = null) {
		
		/*
		*  Функция возврата последнего значения массива
		*/
		
		if (!is_array($item)) { return; }
		
		$key = null;
		$val = null;
		
		if (version_compare(PHP_VERSION, '7.3.0', '<')) {
			
			$item = array_slice($item, -1, 1, true);
			if ($result !== 'value') {
				$key = key($item);
			}
			if ($result !== 'key') {
				$val = reset($item);
			}
			
		} else {
			
			if ($result !== 'value') {
				$key = array_key_last($item);
			}
			if ($result !== 'key') {
				$val = end($item);
			}
			
		}
		
		if ($result === 'key') {
			return $key;
		} elseif ($result === 'value') {
			return $val;
		} else {
			return ['key' => $key, 'value' => $val];
		}
		
	}

	static public function refirst(&$item, $data) {
		
		/*
		*  Функция замены первого значения массива
		*/
		
		if (!$item) { return; }
		
		$key = self::first($item, 'key');
		$item[$key] = $data;
		
	}

	static public function relast(&$item, $data) {
		
		/*
		*  Функция замены последнего значения массива
		*/
		
		if (!$item) { return; }
		
		$key = self::last($item, 'key');
		$item[$key] = $data;
		
	}

	static public function unfirst(&$item) {
		
		/*
		*  Функция удаления первого значения массива
		*/
		
		return array_slice($item, 1, null, true);
		
	}

	static public function unlast(&$item) {
		
		/*
		*  Функция удаления последнего значения массива
		*/
		
		return array_slice($item, 0, -1, true);
		
	}

	static public function len($item, $recursive = null) {
		
		/*
		*  Функция возврата длины массива
		*  Добавлен второй аргумент, позволяющий считать длину многомерного массива
		*  Обычно считаются элементы, являющиеся вложенными массивами,
		*  но в режиме рекурсии они не подсчитываются
		*/
		
		if (!System::typeData($item, 'object')) {
			$item = self::convert($item);
		}
		
		if (!$item) {
			return;
		}
		
		if (!$recursive) {
			return count($item);
		}
		
		$c = 0;
		
		foreach ($item as $i) {
			if (is_array($i)) {
				$c += self::len($i, true);
			} else {
				$c++;
			}
		}
		unset($i);
		
		return $c;
		
	}

	static public function split($array) {
		
		/*
		*  НОВАЯ Функция которая разделяет массив по-очереди на ключи и значения
		*/
		
		$result = [];
		
		$i = 0;
		$key = null;
		foreach ($array as $item) {
			if ($i % 2 === 0) {
				$key = $item;
			} else {
				if (is_float($key)) {
					$key = (string) $key;
				}
				$result[$key] = $item;
			}
			$i++;
		}
		unset($key, $item, $i);
		
		return $result;
		
	}

	static public function join($keys, $values, $default = null) {
		
		/*
		*  НОВАЯ Функция создания массива из двух массивов
		*  первый используется в качестве ключей
		*  второй - в качестве значений
		*  
		*  если длина массивов разная, то
		*  итоговый массив создается по длине массива ключей
		*  дополняясь элементами default
		*  
		*  если в качестве значения передан не массив,
		*  то массив ключей целиком заполняется переданным значением
		*/
		
		if (System::type($values) !== 'array') {
			return self::join($keys, [], $values);
		}
		
		if (System::type($keys) !== 'array' || !count($keys)) {
			return array_values($values);
		}
		
		$keys = self::clear($keys);
		$lkeys = self::len($keys);
		$lvalues = self::len($values);
		
		if ($lkeys > $lvalues) {
			// СТАРОЕ ПОВЕДЕНИЕ
			//$keys = array_slice($keys, 0, $lvalues);
			// НОВОЕ ПОВЕДЕНИЕ
			$values = array_pad($values, $lkeys, $default);
		} elseif ($lvalues > $lkeys) {
			$values = array_slice($values, 0, $lkeys);
		}
		
		return array_combine($keys, $values);
		
	}

	static public function combine($item, $result = []) {
		
		/*
		*  НОВАЯ Функция объединяет многомерный массив в одномерный
		*/
		
		if (!System::typeIterable($item)) {
			return $item;
		}
		
		foreach ($item as $i) {
			if (System::typeIterable($i)) {
				$i = self::combine($i);
			}
			if (System::typeIterable($i)) {
				$result = array_merge($result, $i);
			} else {
				$result[] = $i;
			}
		}
		unset($i);
		
		return $result;
		
	}

	static public function combineByIndex($item, $result = []) {
		
		/*
		*  НОВАЯ Функция объединяет многомерный массив в одномерный с сохранением ключей
		*/
		
		if (!System::typeIterable($item)) {
			return $item;
		}
		
		foreach ($item as $k => $i) {
			if (System::typeIterable($i)) {
				$i = self::combineByIndex($i);
				$result = self::merge($result, $i);
			} else {
				$result[$k] = $i;
			}
		}
		unset($i);
		
		return $result;
		
	}

	static public function merge($item, $merge, $recursive = null) {
		
		/*
		*  Функция объединения двух массивов в один
		*/
		
		if (System::type($merge) !== 'array' || !count($merge)) {
			return $item;
		}
		
		if ($recursive) {
			return array_replace_recursive($item, $merge);
		} else {
			return array_replace($item, $merge);
		}
		
	}

	static public function each(&$item, $callback, $ignore = null) {
		
		/*
		*  это ПЕРЕДЕЛАННАЯ ФУНКЦИЯ, старая теперь называется eachOf
		*  это простая замена foreach
		*  позволяет перебирать элементы объекта или массива
		*  только в том случае, если он не пустой
		*
		*  теперь он еще и позицию показывает - first, last или alone
		*
		*  сделана в основном для того, чтобы облегчить код
		*  например:
		*  Objects::each($sets['form'], function($i, $k){
		*    $this -> eget('form') -> addCustom($k, $i);
		*  });
		*  вместо:
		*  if (System::typeIterable($sets['form'])) {
		*    foreach ($sets['form'] as $key => $item) {
		*      $this -> eget('form') -> addCustom($key, $item);
		*    }
		*    unset($key, $item);
		*  }
		*  
		*  теперь добавлен последний аргумент, который позволяет
		*  обрабатывать пустые массивы
		*/

		
		if (!is_array($item) || (!$ignore && !System::typeIterable($item))) {
			return;
		}
		
		$target = [
			self::first($item, 'key'),
			self::last($item, 'key'),
			self::len($item)
		];
		
		foreach ($item as $key => &$value) {
			
			$position = null;
			if ($target[2] === 1) {
				$position = 'alone';
			} elseif ($key === $target[0]) {
				$position = 'first';
			} elseif ($key === $target[1]) {
				$position = 'last';
			}
			
			$value = call_user_func($callback, $value, $key, $position);
			
		}
		unset($key, $value);
		
		return $item;
		
	}
	
	static public function eachOf(&$item = null, $parameters = null, $callback) {
		
		/*
		*  это универсальная замена foreach, которая управляет элементами в текущем массиве
		*  item - входящий массив или объект
		*  parameter - параметр, который влияет на поведение в случае, когда значение 'item' = 'false' (но не null и не любое другое)
		*      filter - передает в качестве результата копию исходного массива и удаляет из него текущий элемент
		*      break - прерывает цикл
		*      continue - переходит к следующей итерации
		*  специальное значение параметра в виде массива или объекта, передает в функцию этот объект третьим параметром (не забудьте сделать его ссылкой) и вы можете изменять его
		*  callback - callback-функция, как правило анонимная, которая работает в итерации входящего массива, принимает параметры
		*    текущее значение
		*    текущий ключ
		*    возвращаемый массив или объект (не забудьте сделать его ссылкой), если он передан в параметр
		*  любой из этих параметров можно не указывать, и тогда они не будут участвовать в процессе
		*  эта функция возвращает результат, который записывается вместо текущего значения
		*  
		*  если вы хотите использовать входящий массив или строку, используйте их, переданными в виде объекта/массива через параметр
		*  например: each($obj, [ 'str' => null ], function ($v, $k, $p){ $p['str'] .= '...'; });
		*  
		*  производительность этой функции медленнее в 1.5-2.5 раза по сравнению с foreach,
		*  но на сложных расчетах внутри итерации ее скорость становится такой же, как у встроенной функции
		*  и для мелких итераций ее скорость остается почти такой же
		*  и она расходует столько же памяти, как встроенная функция
		*  удобство ее использования в том, что она универсальна как для php, так и для js
		*/
		
		$type = System::typeOf($parameters);
		
		if ($type === 'iterable') {
			
			foreach ($item as $key => &$value) {
				call_user_func_array($callback, [$value, $key, &$parameters]);
			}
			return $parameters;
			
		} elseif (!$type) {
			
			foreach ($item as $key => &$value) {
				$value = call_user_func($callback, $value, $key);
			}
			unset($key, $value);
			
		} else {
			
			foreach ($item as $key => &$value) {
				$result = call_user_func($callback, $value, $key);
				if ($result === false) {
					if ($parameters === 'filter') {
						unset($item[$key]);
						continue;
					} elseif ($parameters === 'break') {
						break;
					} elseif ($parameters === 'continue') {
						continue;
					}
				} else {
					$value = $result;
				}
			}
			unset($key, $value);
			
		}
		
		return $item;
		
	}

	static public function clear(&$item, $unique = null) {
		
		/*
		*  Функция очищает массив от пустых элементов
		*/
		
		if (!System::typeOf($item, 'iterable')) {
			return $item;
		}
		
		foreach ($item as $k => &$i) {
			if (System::typeOf($i, 'iterable')) {
				$i = self::clear($i, $unique);
			}
			if (!System::set($i)) {
				unset($item[$k]);
			}
		}
		unset($k, $i);
		
		if ($unique) {
			$item = array_unique($item);
		}
		
		return $item;
		
	}

	static public function unique($item) {
		
		/*
		*  Функция убирает повторяющиеся элементы в массиве
		*/
		
		return array_unique($item);
		
	}

	static public function sort($haystack, $reverse = false, $keys = false) {
		
		/*
		*  Функция сортировки массива
		*  всегда используется NATCASESORT без учета регистра
		*  вторым аргументом можно задать сортировку в обратном порядке
		*  третьим аргументом можно задать сортировку по ключам
		*/
		
		$associate = self::associate($haystack);
		
		$numeric = $keys ? !$associate : self::numeric($haystack);
		
		if ($keys) {
			ksort($haystack, $numeric ? SORT_NUMERIC : SORT_NATURAL | SORT_FLAG_CASE);
		} else {
			asort($haystack, $numeric ? SORT_NUMERIC : SORT_NATURAL | SORT_FLAG_CASE);
		}
		
		if ($reverse) {
			$haystack = self::reverse($haystack);
		}
		
		if ($associate) {
			return $haystack;
		} else {
			$result = [];
			foreach ($haystack as $i) {
				$result[] = $i;
			}
			unset($i);
			return $result;
		}
		
	}

	static public function random($haystack) {
		
		/*
		*  Функция сортировки массива в случайном порядке
		*  с сохранением ключей в ассоциативном массиве
		*/
		
		$associate = self::associate($haystack);
		
		if ($associate) {
			
			$result = [];
			$keys = self::keys($haystack);
			shuffle($keys);
			foreach ($keys as $key) {
				$result[$key] = $haystack[$key];
			}
			unset($key);
			return $result;
			
		} else {
			
			shuffle($haystack);
			return $haystack;
			
		}
		
	}

	static public function difference($haystack, $needle) {
		
		/*
		*  Функция возвращает массив, содержащий различия между двумя массивами
		*/
		
		return array_diff($haystack, $needle);
		
	}

	static public function pairs($array, $splitter, $offset = null) {
		
		/*
		*  НОВАЯ Функция которая разделяет массив на два массива по значению
		*/
		
		$key = self::find($array, $splitter);
		
		if (!System::set($key)) {
			return [ $array, [] ];
		}
		
		$keys = self::keys($array);
		$pos = self::find($keys, $key);
		
		$before = $offset < 0 ? 1 : 0;
		$after = $offset > 0 ? 0 : 1;
		
		$first = self::get($keys, 0, $pos + $before);
		$last = self::get($keys, $pos + $after);
		
		return [
			array_intersect_key($array, self::join($first, null)),
			array_intersect_key($array, self::join($last, null))
		];
		
	}

	static public function pairsByIndex($array, $splitter, $offset = null) {
		
		/*
		*  НОВАЯ Функция которая разделяет массив на два массива по индексу
		*/
		
		$keys = self::keys($array);
		$pos = self::find($keys, $splitter);
		
		if (!System::set($pos)) {
			return [ $array, [] ];
		}
		
		$before = $offset < 0 ? 1 : 0;
		$after = $offset > 0 ? 0 : 1;
		
		$first = self::get($keys, 0, $pos + $before);
		$last = self::get($keys, $pos + $after);
		
		return [
			array_intersect_key($array, self::join($first, null)),
			array_intersect_key($array, self::join($last, null))
		];
		
	}

	static public function flip($array) {
		
		/*
		*  НОВАЯ Функция которая меняет значения и ключи массива местами
		*/
		
		return array_flip($array);
		
	}

	static public function inject($haystack, $map, $value = null) {

		/*
		*  Функция которая производит объединение данных в многомерных массивах или объектах
		*  на входе нужно указать:
		*    целевой массив или объект, которЫЙ будем заполнять - $haystack
		*    и массив или объект, который содержит ключи, которЫМИ будем заполнять haystack - $map
		*    третий, необязательный, аргумент - это значение
		*  ТЕПЕРЬ ПОВЕДЕНИЕ ТАКОВО, ЧТО ПО-УМОЛЧАНИЮ ПУСТЫЕ ЗНАЧЕНИЯ НЕ ЗАПОЛНЯЮТСЯ!
		*  
		*  Например, если указать:
		*  inject(['data' => null], ['a', 'b', 'c'], 'value')
		*  то на выходе получим такой массив:
		*  [ 'data' => ['a' => ['b' => ['c' => 'value']]] ];
		*  
		*  при этом, особенность данной функции в том, что она дополняет массив и не стирает другие имеющиеся в нем поля
		*/
		
		if (!is_array($haystack) || !is_array($map)) {
			return null;
		}
		
		self::reset(self::clear($map));
		
		$map = array_reverse($map);
		$c = count($map);
		$item = $value;
		
		if (!empty($c) && is_int($c)) {
			for ($i = 0; $i < $c; $i++) {
				$k = array_shift($map);
				$item = [$k => $item];
			}
		}
		
		unset($map, $c, $i, $value);
		
		if (!$item) { $item = []; }
		
		//return array_merge_recursive($haystack, $item);
		return array_replace_recursive($haystack, $item);
		
	}

	static public function extract($haystack, $map) {
		
		/*
		*  Функция которая производит извлечение данных в многомерных массивах или объектах
		*  на входе нужно указать:
		*    целевой массив или объект, ИЗ котороГО будем извлекать данные - $haystack
		*    и массив или объект, согласно котороМУ будем извлекать эти данные - $map
		*  
		*  Третий аргумент может принимать значение true
		*  и тогда результирующий массив будет преобразован в объект и наоборот
		*  
		*  Если вы хотите извлечь значение из многомерного массива, использовать так:
		*  $arr = objectExtract($arr, ['field', 'field', 'field']);
		*  Например, если $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]]]
		*  и вам надо извлечь d, то используйте такой вызов:
		*  $arr = objectExtract($haystack, ['a', 'b', 'd']);
		*  
		*  на выходе отдает готовый массив $haystack
		*/
		
		self::reset(self::clear($map));
		
		foreach($map as $i) {
			
			if (
				System::type($haystack, 'array')
				//&& System::set($haystack[$i])
			) {
				$haystack = $haystack[$i];
			} elseif (
				System::type($haystack, 'object')
				//&& System::set($haystack -> $i)
			) {
				$haystack = $haystack -> $i;
			} else {
				$haystack = null;
				break;
			}
		}
		
		return $haystack;
		
	}

	static public function delete(&$haystack, $map) {

		/*
		*  Функция которая удаляет ключ и значение по заданной карте в многомерных массивах или объектах
		*  на входе нужно указать:
		*    целевой массив или объект, в котором будем удалять ключ - $haystack
		*    и массив или объект, который содержит ключи, по которым будем искать путь - $map
		*  
		*  Например, если указать:
		*  delete(['a' => ['b' => ['c' => 'value']]], ['a', 'b', 'c']);
		*  то на выходе получим такой массив:
		*  ['a' => ['b' => []]];
		*  
		*/
		
		if (!is_array($haystack) || !is_array($map)) {
			return null;
		}
		
		self::reset(self::clear($map));
		
		$c = count($map) - 1;
		$current = &$haystack;
		
		foreach ($map as $key => $item) {
			if (!is_array($current)) {
				break;
			}
			if ($key === $c) {
				unset($current[$item]);
			} else {
				$current = &$current[$item];
			}
		}
		unset($key, $item);
		
		return $haystack;
		
	}

	static public function remap(&$item, $name) {
		
		/*
		*  НОВАЯ Функция для вложенных массивов
		*  переназначает ключи главного массива
		*  по значению указанного поля внутреннего массива
		*/
		
		foreach ($item as $k => $i) {
			if (!is_array($i)) {
				continue;
			}
			$key = $i[$name];
			if (!System::set($key)) {
				continue;
			}
			$item[$key] = $i;
			unset($item[$k]);
		}
		unset($k, $i);
		
		return $item;
		
	}

	static public function reset(&$item) {
		
		/*
		*  НОВАЯ Функция которая сбрасывает ключи массива
		*/
		
		return self::values($item);
		
	}

}

/*
Objects        | Strings
associate      | -
numeric        | -
convert        | -
keys           | -
values         | -
match
find
get
cut
before
after
add
reverse
first
last
refirst
relast
unfirst
unlast
len
split
join
combine
combineByIndex | combineMask
merge          | except
each           | replace
eachOf         | -
clear
unique
sort
random
difference
pairs
pairsByIndex
flip           | -
inject         | -
extract        | -
reset          | -
*/

?>