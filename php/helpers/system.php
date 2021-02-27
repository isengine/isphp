<?php
namespace is\Helpers;

class System {

	static public function print($item = null) {
		echo empty($item) ? 'null' : print_r($item, true);
	}

	static public function console($item = null, $title = null) {
		echo '<!--' . (empty($title) ? null : ' // ' . $title) . "\r\n" . (empty($item) ? 'null' : print_r($item, true)) . "\r\n"  . '-->';
	}

	static public function include($item) {
		
		$item = str_replace(['..','\/','\\','.',':'], ['','','','',DS], $item);
		$item = realpath(__DIR__ . DS . DP) . DS . $item . '.php';
		
		if (file_exists($item)) {
			require_once $item;
			return true;
		} else {
			return false;
		}
		
	}

	static public function isset($item = null) {
		return isset($item);
	}

	static public function set($item = null, $yes = null, $no = null) {
		
		if ($yes) {
			
			// return \is\Helpers\System::set()
			// прямой вызов, неудобен тем, что жестко привязан
			
			// return self::set();
			// относительный вызов
			
			// return static::set();
			// позднее связывание, сработает в наследованиях, но для методов в этом, очевидно, нет нужды
			
			return self::set($item) ? ($yes === true ? $item : $yes) : $no;
			
		}
		
		if (
			isset($item) &&
			$item === true
		) {
			return true;
		} elseif (
			!isset($item) ||
			$item === false ||
			$item === null
		) {
			return null;
		} elseif (
			empty($item) &&
			is_numeric($item)
		) {
			return true;
		} elseif (empty($item)) {
			return null;
		} elseif (is_array($item) || is_object($item)) {
			foreach ($item as $i) {
				if (self::set($i)) {
					return true;
				}
			}
			return null;
		} elseif (
			is_string($item) && 
			(mb_strpos($item, ' ') !== false || mb_strpos($item, '	') !== false)
		) {
			return preg_replace('/[\s]+/ui', '', $item) ? true : null;
		}
		
		return true;
		
	}

	static public function type($item = null, $compare = null) {
		
		/*
		Число, в том числе строка записанная числом
		Объект/именованный не пустой массив
		Простой, неименованный не пустой массив
		Строка
		Триггер/булев
		Пустой элемент, false, null, undefined, пустая строка или пустой объект/массив, но не ноль
		
		Вторым аргументом добавлена проверка на указанный тип
		*/
		
		$type = null;
		$data = null;
		$set = self::set($item);
		
		if (is_array($item)) {
			$type = 'array';
		} elseif (is_object($item)) {
			$type = 'object';
		} elseif (!$set) {
			$type = null;
		} elseif (is_bool($item)) {
			$type = 'true';
		} elseif (is_string($item)) {
			
			$item = preg_replace('/\s/', '', $item);
			$item = str_replace(',', '.', $item);
			$set = self::set($item);
			
			if (!$set) {
				$type = null;
			} elseif (is_numeric($item)) {
				$type = 'numeric';
			} else {
				$type = 'string';
			}
			
		} elseif (is_numeric($item)) {
			$type = 'numeric';
		} else {
			$type = 'string';
		}
		
		if ($compare) {
			return $compare === $type ? true : null;
		}
		
		return $type;
		
	}

	static public function typeOf($item = null, $compare = null) {
		
		/*
		Более упрощенная проверка на принадлежность к типу:
			скаляный (строковый) тип - строка или число
			итерируемый (объектный) тип - массив или объект
		Второй аргумент позволяет задать сравнение с типом и вывести его результат
		
		Призвана заменить многократные проверки
		type === string || type === numeric
		type === array || type === object
		
		данная функция возвращает тип, даже если содержимое пустое
		*/
		
		$type = null;
		
		if (is_scalar($item)) {
			$type = 'scalar';
		} elseif (is_array($item) || is_object($item)) {
			$type = 'iterable';
		} else {
			$set = self::set($item);
			if (!$set || $item === true || is_resource($item)) {
				return null;
			}
			unset($set);
		}
		
		if ($compare) {
			return $compare === $type ? true : null;
		}
		
		return $type;
		
	}

	static public function typeData($item = null, $compare = null) {
		
		/*
		Проверка на принадлежность к системному типу данных:
			скаляный (строковый) тип - строка или число
			итерируемый (объектный) тип - массив или объект
			json данные
			системные данные
		Второй аргумент позволяет задать сравнение с типом и вывести его результат
		
		данная функция не возвращает тип, если содержимое пустое,
		т.к. любой тип с пустым содержимым не относится к системным данным
		
		Призвана заменить
		проверку type по данным
		проверку objects::is
		и расширить проверку на json
		*/
		
		// Внимание! Здесь намеренное различие типов с версией для js
		
		// Objects::is($item) => System::typeData(item, 'object')
		
		$type = self::type($item);
		$result = null;
		
		if ($type === 'string') {
			$first = $item[0];
			$last = mb_substr($item, -1);
			if (
				($first === '{' && $last === '}') ||
				($first === '[' && $last === ']')
			) {
				$result = 'json';
			} elseif (mb_strpos($item, ':') !== false || mb_strpos($item, '|') !== false) {
				$result = 'string';
			}
			unset($first, $last);
		} elseif ($type === 'array') {
			$result = 'object';
		}
		
		if ($compare) {
			return $compare === $result ? true : null;
		}
		
		return $result;
		
	}

}

?>