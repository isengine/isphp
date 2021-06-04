<?php
namespace is\Helpers;

class System {

	static public function render($item = null) {
		echo empty($item) ? 'null' : print_r($item, true);
	}

	static public function console($item = null, $title = null) {
		echo '<!--' . (empty($title) ? null : ' // ' . $title) . "\r\n" . (empty($item) ? 'null' : print_r($item, true)) . "\r\n"  . '-->';
	}

	static public function includes($item, $base = __DIR__ . DS . DP, $once = true, $object = null, $return = null) {
		
		// once влияет не на первое включение, а только на повторные
		
		$item = str_replace(['..','.','\/','\\',':'], ['','',DS,DS,DS], $item);
		$path = realpath($base) . DS . $item . '.php';
		
		if (file_exists($path)) {
			if ($once) {
				require_once $path;
			} else {
				require $path;
			}
			if ($return) {
				return $$return;
			} else {
				return true;
			}
		} else {
			return false;
		}
		
	}

	static public function exists($item = null) {
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
			//$item === 'false' ||
			//$item === 'null' ||
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

	static public function setReturn($item = null, $before = null, $after = null, $not = null) {
		
		/*
		НОВАЯ ФУНКЦИЯ
		проверка значения с его возвратом
		специальные аргументы
		before и after - если проверка прошла успешно, значение возвращается с заданными строками перед и после него
		not - данное значение возвращается в случае если проверка не прошла успешно
		*/
		
		return self::set($item) ? $before . $item . $after : $not;
		
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
		type && ( type === string || type === numeric )
		type && ( type === array || type === object )
		
		данная функция возвращает тип, даже если содержимое пустое
		*/
		
		$type = null;
		
		//if (is_scalar($item) && !is_bool($item)) {
		if (is_scalar($item) && $item !== false) {
			// УСЛОВИЕ ПРОВЕРКИ ДОПОЛНИЛОСЬ НА ОТМЕНУ BOOLEAN
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

	static public function typeClass($item = null, $compare = null) {
		
		/*
		Проверка на принадлежность к имени класса
		Второй аргумент позволяет выполнить сравнение и вывести его результат
		*/
		
		// НОВАЯ ФУНКЦИЯ
		
		$type = self::type($item);
		
		if (!is_object($item)) {
			return null;
		}
		
		$name = get_class($item);
		$pos = mb_strrpos($name, '\\');
		$result = mb_strtolower(mb_substr($name, $pos !== false ? $pos + 1 : 0));
		
		if ($compare) {
			return $compare === $result ? true : null;
		}
		
		return $result;
		
	}

	static public function typeIterable($item = null) {
		
		/*
		НОВАЯ ФУНКЦИЯ - проверка переменной на возможность его итерировать
		Призвана заменить многократные проверки
		type && ( type === array || type === object )
		*/
		
		return self::set($item) && self::typeOf($item, 'iterable');
	}

	static public function server($name) {
		
		// НОВАЯ ФУНКЦИЯ - ВОЗРАЩАЕТ РАЗНЫЕ ДАННЫЕ СЕРВЕРА
		
		if ($name === 'root') {
			$name = realpath($_SERVER['DOCUMENT_ROOT']) . DS;
		} elseif ($name === 'host') {
			$name = $_SERVER['HTTP_HOST'];
			//$name = $_SERVER['SERVER_NAME'];
		} elseif ($name === 'fullprotocol') {
			$name = $_SERVER['SERVER_PROTOCOL'];
		} elseif ($name === 'protocol') {
			$name = 'http';
			if (
				strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) === 'https' ||
				($_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off') ||
				$_SERVER['SERVER_PORT'] === 443 ||
				$_SERVER['SERVER_PORT'] === '443' ||
				$_SERVER['REQUEST_SCHEME'] === 'https' ||
				$_SERVER['HTTP_X_FORWARDED_PORT'] === 443 ||
				$_SERVER['HTTP_X_FORWARDED_PORT'] === '443' ||
				$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
			) {
				$name = 'https';
			}
		} elseif ($name === 'domain') {
			$name = self::server('protocol') . '://' . (
				extension_loaded('intl') ? idn_to_utf8(
					$_SERVER['HTTP_HOST'],
					null,
					version_compare(PHP_VERSION, '7.2.0', '<') ? INTL_IDNA_VARIANT_2003 : INTL_IDNA_VARIANT_UTS46
				) : $_SERVER['HTTP_HOST']
			);
		} elseif ($name === 'request') {
			$name = urldecode($_SERVER['REQUEST_URI']);
		} elseif ($name === 'method') {
			$name = strtolower($_SERVER['REQUEST_METHOD']);
		} elseif ($name === 'ip') {
			$name = $_SERVER['REMOTE_ADDR'];
		} elseif ($name === 'agent') {
			$name = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$name = null;
		}
		
		return $name;
		
	}

	static public function check($item, $stop = null) {
		
		// НОВАЯ ФУНКЦИЯ, вспомогательная, для отладки - выводит строку для проверки
		
		echo '[' . print_r($item, 1) . ']<br>';
		
		if ($stop) {
			exit;
		}
		
	}

}

?>