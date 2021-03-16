<?php
namespace is\Helpers;

class Strings {

	static public function match($haystack, $needle) {
		
		/*
		*  Функция проверки наличия подстроки или символа в заданной строке
		*/
		
		return mb_strpos($haystack, $needle) === false ? null : true;
		
	}

	static public function find($haystack, $needle, $position = null) {
		
		/*
		*  Функция поиска подстроки или символа в заданной строке
		*  если задан pos, то он ищет соответствие подстроки в заданной позиции
		*  положительное значение - поиск с начала, от 0
		*  отрицательное значение - поиск с конца, от -1
		*  если pos не задан, то возвращает первое значение с начала
		*  специальное значение pos 'r' задает возврат индекса последнего значения в строке
		*/
		
		$pos = System::set($position);
		
		if ($pos && $position !== 'r') {
			$result = mb_substr($haystack, $position, mb_strlen($needle));
			return $result === $needle ? true : false;
		} elseif ($position === 'r') {
			return mb_strrpos($haystack, $needle);
		} else {
			return mb_strpos($haystack, $needle);
		}
		
	}

	static public function get($haystack, $index, $length = null, $position = null) {
		
		/*
		*  Функция возвращает подстроку по указанному индексу (позиции) и заданной длины
		*  если длина задана, то она смещается
		*  положительное значение - вперед
		*  отрицательное значение - назад
		*  если длина не задана, то возвращается вся строка от индекса и до конца
		*  специальное значение position = true задает $length от конца строки
		*  
		*  например, строка "positionare"
		*  0 : positionare
		*  3 : itionare
		*  6 : onare
		*  0, 3 : pos
		*  3, 3 : iti
		*  6, 3 : ona
		*  6, -3 : tio
		*  -3 : are
		*  -6 : ionare
		*  -6, 3 : ion
		*  -6, -3 : iti
		*  1, 0, true : ositionare
		*  1, 1, true : ositionar
		*  1, 2, true : ositiona
		*/
		
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
			return mb_substr($haystack, $index, $length);
		} elseif ($length && $position) {
			return mb_substr($haystack, $index, 0 - $length);
			//return mb_substr($haystack, 0, $index);
		} else {
			return mb_substr($haystack, $index);
		}
		
	}

	static public function cut($haystack, $index = -1, $length = null, $position = null) {
		
		/*
		*  Функция удаления части строки с начала или с конца (при отрицательном значении)
		*  умолчание выставлено таким образом, что при многократном вызове функции, строка будет уменьшаться с конца
		*  первое значение - индекс, позиция с начала (или с конца при отрицательном значении)
		*  второе значение - длина, вперед или назад (при отрицательном значении), если не задано или 0, то вся длина до конца
		*  специальное значение position = true задает $length от конца строки
		*  
		*  например, строка "positionare"
		*  1 : p
		*  3 : pos
		*  6 : positi
		*  -1 : positionar
		*  -3 : position
		*  -6 : posit
		*  0, 1 : ositionare
		*  3, 1 : postionare
		*  6, 1 : positinare
		*  0, 3 : itionare
		*  3, 3 : posonare
		*  6, 3 : positire
		*  6, -3 : posinare
		*  -1, 1 : positionar
		*  -3, 1 : positionre
		*  -6, 1 : positonare
		*  -6, 3 : positare
		*  -6, -3 : posonare
		*  1, 0, true : p
		*  1, 1, true : pe
		*  1, 2, true : pre
		*/
		
		$len = mb_strlen($haystack);
		
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
		
		return mb_substr($haystack, 0, $first) . mb_substr($haystack, $last);
		
	}

	static public function add($haystack, $needle, $reverse = null) {
		
		/*
		*  Функция добавления значений в начало или в конец строки
		*/
		
		return $reverse ? $needle . $haystack : $haystack . $needle;
		
	}

	static public function reverse($item) {
		
		/*
		*  Функция разворота строки задом наперед
		*/
		
		$item = mb_convert_encoding($item, 'UTF-16LE', 'UTF-8');
		$item = strrev($item);
		return mb_convert_encoding($item, 'UTF-8', 'UTF-16BE');
		
	}

	static public function first($item) {
		
		/*
		*  Функция возврата первого символа строки
		*/
		
		return $item[0];
		
	}

	static public function last($item) {
		
		/*
		*  Функция возврата последнего символа строки
		*/
		
		return mb_substr($item, -1);
		
	}

	static public function unfirst($item) {
		
		/*
		*  Функция возврата первого символа строки
		*/
		
		return mb_substr($item, 1);
		
	}

	static public function unlast($item) {
		
		/*
		*  Функция возврата последнего символа строки
		*/
		
		return mb_substr($item, 0, -1);
		
	}

	static public function len($item) {
		
		/*
		*  Функция возврата длины строки
		*/
		
		return mb_strlen($item);
		
	}

	static public function split($item = null, $splitter = '\s,;', $clear = null) {
		
		/*
		*  Функция разбивает строку на массив данных по указанным символам
		*/
		
		if (System::typeOf($item) !== 'scalar') {
			return null;
		} elseif (System::type($splitter) !== 'string') {
			return [$item];
		}
		
		$result = preg_split('/[' . $splitter . ']/u', $item, null, null);
		
		if (System::set($clear)) {
			$result = Objects::clear($result);
			//$result = array_diff($result, [null]);
		}
		
		return $result;
		
		//return preg_split('/[' . $splitter . ']/u', $item, null, System::set($clear) ? PREG_SPLIT_NO_EMPTY : null);
		
	}

	static public function join($item, $splitter = ' ') {
		
		/*
		*  Функция объединяет массив в строку с разделителем
		*/
		
		$type = System::type($item);
		
		if ($type !== 'array' && $type !== 'object') {
			return $item;
		}
		
		if ($type === 'object') {
			$item = (array) $item;
		}
		
		return implode($splitter, $item);
		
	}

	static public function replace($item, $search, $replace) {
		
		/*
		*  Функция замены search на replace в строке item
		*  поддерживает массив замен, как в оригинальной функции на php так и в js реализации
		*/
		
		return str_replace($search, $replace, $item);
		
	}

	static public function clear($item) {
		
		/*
		*  Функция удаления всех пробелов и пустых символов из строки
		*/
		
		return preg_replace('/(\s|\r|\n|\r\n)+/u', '', $item);
		
	}

	static public function unique($item) {
		
		/*
		*  Функция удаления одинаковых значений из строки
		*/
		
		$result = preg_split('//u', $item);
		$result = array_unique($result);
		$result = implode('', $result);
		
		return $result;
		
	}

	static public function sort($haystack, $reverse = false, $register = true) {
		
		/*
		*  Функция сортировки строки по символам
		*  вторым аргументом можно задать сортировку в обратном порядке
		*  вторым аргументом можно выключить сортировку с учетом регистра
		*/
		
		$haystack = preg_split('//u', $haystack);

		sort($haystack, $register ? SORT_NATURAL : SORT_NATURAL | SORT_FLAG_CASE);
		
		if ($reverse) {
			$haystack = Objects::reverse($haystack);
		}
		
		$str = implode('', $haystack);
		
		//foreach ($haystack as $item) {
		//	$str .= $item;
		//}
		//unset($item);
		
		return $str;
		
	}

	static public function difference($haystack, $needle) {
		
		/*
		*  Функция возвращает строку, содержащую различия между двумя строками
		*/
		
		$haystack = preg_split('//u', $haystack);
		$needle = preg_split('//u', $needle);
		
		$diff = array_diff($haystack, $needle);
		
		$str = null;
		
		if (!empty($diff)) {
			foreach ($diff as $item) {
				$str .= $item;
			}
			unset($item);
		}
		
		return $str;
		
	}

	static public function fill($string, $len, $values = ' ', $reverse = null) {
		
		/*
		*  НОВАЯ Функция дополнения строки string до указанной длины len
		*  символами или подстрокой values
		*  последний аргумент reverse заставляет дополнять строку в начало
		*/
		
		return str_pad($string, $len, $values, $reverse ? STR_PAD_LEFT : STR_PAD_RIGHT);
		
	}

	static public function fillup($string, $len, $values = ' ', $reverse = null) {
		
		/*
		*  НОВАЯ Функция дополнения строки string на указанное число символов $len
		*  символами или подстрокой values
		*  последний аргумент reverse заставляет дополнять строку в начало
		*  отличие от fill в том, что здесь указывается
		*  на какое еще число символов нужно увеличить строку
		*/
		
		return self::fill($string, self::len($string) + $len, $values, $reverse);
		
	}

}

?>