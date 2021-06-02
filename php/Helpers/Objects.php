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
		} elseif (!$type && $item) {
			$item = is_object($item) ? json_decode(json_encode($item), true) : [$item];
		}
		
		return $item;
		
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

	static public function find($haystack, $needle, $position = null) {
		
		/*
		*  Функция поиска строки или символа в заданном массиве
		*  если задан position, то он ищет соответствие строки в заданном ключе
		*  положительное значение - поиск с начала, от 0
		*  отрицательное значение - поиск с конца, от -1
		*  если position не задан, то возвращает первый ключ/индекс значение с начала
		*  специальное значение 'r' задает возврат индекса/ключа последнего значения в массиве
		*/
		
		//$find = array_keys($haystack, $needle);
		$find = self::keys(self::filter($haystack, $needle));
		
		$pos = System::set($position);
		
		if ($pos && $position !== 'r') {
			if ($position < 0) {
				$position = self::len($haystack) + $position;
			}
			return in_array($position, $find) === false ? null : true;
		} elseif ($position === 'r') {
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

	static public function add($haystack, $needle, $recursive = null) {
		
		/*
		*  Функция добавления значений в начало или в конец массива
		*/
		
		$haystack = self::convert($haystack);
		$needle = self::convert($needle);
		
		return $recursive ? array_merge_recursive($haystack, $needle) : array_merge($haystack, $needle);
		
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

	static public function n($item, $n, $result = null) {
		
		/*
		*  Функция возврата n-ного значения массива
		*/
		
		$r = self::get($item, $n, 1);
		$r = self::first($r);
		
		if ($result === 'key') {
			return $r['key'];
		} elseif ($result === 'value') {
			return $r['value'];
		} else {
			return $r;
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

	static public function ren(&$item, $n, $data) {
		
		/*
		*  Функция замены n-ного значения массива
		*/
		
		if (!$item) { return; }
		
		$key = self::n($item, $n, 'key');
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

	static public function unn(&$item, $n, $result = null) {
		
		/*
		*  Функция удаления n-ного значения массива
		*/
		
		$item = self::cut($item, $n, 1);
		
		if ($result === 'key') {
			return self::keys($item);
		} elseif ($result === 'value') {
			return self::values($item);
		} else {
			return $item;
		}
		
	}

	static public function len($item) {
		
		/*
		*  Функция возврата длины массива
		*/
		
		return System::typeData($item, 'object') ? count($item) : count(self::convert($item));
		
	}

	static public function levels($item, $max = null) {
		
		/*
		*  Функция подсчета глубины вложенности
		*  вторым параметром можно указать предел подсчета глубины, чтобы снизить траты ресурсов
		*/
		
		$n = 0;
		
		if ( System::typeOf($item, 'iterable') ) {
			foreach ($item as $i) {
				if ( System::typeOf($i, 'iterable') ) {
					$c = self::levels($i, $max);
					$n = ($n > $c) ? $n : $c;
				}
				if ($max && $n + 1 >= $max) {
					break;
				}
			}
			$n = $max && $n >= $max ? $max : $n + 1;
		}
		
		return $n;
		
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

	//static public function combine($keys, $values, $default = null) {
	//устаревшая функция

	static public function fill($keys, $values, $default = null) {
		
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
			return self::fill($keys, [], $values);
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

	static public function merge($item, $merge, $recursion = null) {
		
		/*
		*  Функция объединения двух массивов в один
		*/
		
		if (System::type($merge) !== 'array' || !count($merge)) {
			return $item;
		}
		
		if ($recursion) {
			return array_replace_recursive($item, $merge);
		} else {
			return array_replace($item, $merge);
		}
		
	}

	static public function each(&$item, $callback) {
		
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
		*/

		
		if (!System::typeIterable($item)) {
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

	static public function clear($item, $parameters = ['unique' => null]) {
		
		/*
		*  Функция очищает массив от пустых элементов
		*/
		
		if (!System::typeOf($item, 'iterable')) {
			return $item;
		}
		
		self::eachOf($item, 'filter', function($i) use ($parameters) {
			if (System::typeOf($i, 'iterable')) {
				$i = self::clear($i, $parameters);
			}
			return !System::set($i) ? false : $i;
		});
		
		//$item = array_diff($item, [null]);
		
		if ($parameters['unique']) {
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

	static public function filter($haystack, $needle = null, $notneedle = null) {
		
		/*
		*  Функция возвращает массив только с совпадающими значениями needle
		*  или только с несовпадающими значениями notneedle
		*  в дальнейшем возможно расширить функцию через поиск value и keys (and, or)
		*  а еще передачей их в виде массива
		*/
		
		$find = [];
		
		//if (!System::set($needle) && !System::set($notneedle)) {
		//	return $haystack;
		//}
		
		foreach ($haystack as $k => $i) {
			if ($needle && $i === $needle) {
				$find[$k] = $i;
			} elseif ($notneedle && $i !== $notneedle) {
				$find[$k] = $i;
			}
		}
		
		return $find;
		
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

	static public function randomize($haystack) {
		
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

	static public function level($haystack, $needle, $value = null) {

		/*
		*  Функция которая производит объединение данных в многомерных массивах или объектах
		*  на входе нужно указать:
		*    целевой массив или объект, которЫЙ будем заполнять - $haystack
		*    и массив или объект, который содержит ключи, которЫМИ будем заполнять haystack - $needle
		*    третий, необязательный, аргумент - это значение
		*  ТЕПЕРЬ ПОВЕДЕНИЕ ТАКОВО, ЧТО ПО-УМОЛЧАНИЮ ПУСТЫЕ ЗНАЧЕНИЯ НЕ ЗАПОЛНЯЮТСЯ!
		*  
		*  Например, если указать:
		*  level(['data' => null], ['a', 'b', 'c'], 'value')
		*  то на выходе получим такой массив:
		*  [ 'data' => ['a' => ['b' => ['c' => 'value']]] ];
		*  
		*  при этом, особенность данной функции в том, что она дополняет массив и не стирает другие имеющиеся в нем поля
		*/
		
		if (!is_array($haystack) || !is_array($needle)) {
			return null;
		}
		
		$needle = array_reverse($needle);
		$c = count($needle);
		$item = $value;
		
		if (!empty($c) && is_int($c)) {
			for ($i = 0; $i < $c; $i++) {
				$k = array_shift($needle);
				$item = [$k => $item];
			}
		}
		
		unset($needle, $c, $i, $value);
		
		if (!$item) { $item = []; }
		
		//return array_merge_recursive($haystack, $item);
		return array_replace_recursive($haystack, $item);
		
	}

	static public function extract($haystack, $needle) {
		
		/*
		*  Функция которая производит извлечение данных в многомерных массивах или объектах
		*  на входе нужно указать:
		*    целевой массив или объект, ИЗ котороГО будем извлекать данные - $haystack
		*    и массив или объект, согласно котороМУ будем извлекать эти данные - $needle
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
		
		foreach($needle as $i) {
			
			if (
				System::type($haystack, 'array') &&
				System::set($haystack[$i])
			) {
				$haystack = $haystack[$i];
			} elseif (
				System::type($haystack, 'object') &&
				System::set($haystack -> $i)
			) {
				$haystack = $haystack -> $i;
			} else {
				$haystack = null;
				break;
			}
		}
		
		return $haystack;
		
	}

	static public function reset(&$item) {
		
		/*
		*  НОВАЯ Функция которая сбрасывает ключи массива
		*/
		
		return self::values($item);
		
	}

	static public function pairs($array) {
		
		/*
		*  НОВАЯ Функция которая разделяет массив по-очереди на ключи и значения
		*/
		
		$keys = self::select($array, 2);
		$values = self::select($array, 2, 1);
		
		return self::fill($keys, $values);
		
	}

	static public function unpairs($array) {
		
		/*
		*  НОВАЯ Функция которая разбивает ключи и значения на последовательный массив
		*/
		
		$keys = self::keys($array);
		$values = self::values($array);
		
		$result = [];
		
		foreach ($keys as $key => $item) {
			$result[] = $item;
			$result[] = $values[$key];
		}
		unset($key, $item);
		
		return $result;
		
	}

	static public function select($array, $n, $offset = null) {
		
		/*
		*  НОВАЯ Функция которая выбирает каждое n-ное значение массива со смещением
		*/
		
		$result = [];
		$i = 0;
		foreach ($array as $key => $item) {
			if (($i + $offset) % $n === 0) {
				$result[$key] = $item;
			}
			$i++;
		}
		unset($key, $item, $i);
		
		return $result;
		
	}

	static public function flip($array) {
		
		/*
		*  НОВАЯ Функция которая меняет значения и ключи массива местами
		*/
		
		return array_flip($array);
		
	}

	static public function array_simple($item) {
		
		/*
		*  Функция очищает массив от пустых элементов
		*/
		
		if (!System::typeOf($item, 'iterable')) {
			return $item;
		}
		
		
		self::eachOf($item, null, function($i) {
			if (System::typeOf($i, 'iterable')) {
				if (count($i) === 1) {
					$i = reset($i);
				}
			}
			return $i;
		});
		
		return $item;
		
	}

}

?>