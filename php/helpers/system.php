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

	static public function refresh($path = '/', $code = null, $data = null) {
		
		/*
		*  функция перезагружает страницу
		*  
		*  на входе можно указать:
		*    url-адрес (относительный)
		*    код ответа (заголовок будет подставлен автоматически)
		*    массив данных, которые будут добавлены в заголовок
		*/
		
		if (headers_sent()) {
			return;
		}
		
		if (!empty($data) && is_array($data)) {
			foreach ($data as $key => $item) {
				header($key . ': ' . $item);
			}
			unset($key, $item);
		}
		
		if (!empty($code)) {
			$status = self::code($code);
			header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $status, true, (int) $code);
		}
		
		header('Location: ' . $path);
		
		exit;
		
	}

	static public function code($code = 200) {
		
		/*
		*  функция возвращает статус ответа для переданного кода
		*/
		
		$type = null;
		
		switch ((int) $code) {
			
			// 1xx informational response
			case '100' : $type = 'Continue'; break;
			case '101' : $type = 'Switching Protocols'; break;
			case '102' : $type = 'Processing'; break; // WebDAV; RFC 2518
			case '103' : $type = 'Early Hints'; break; // RFC 8297
			
			// 2xx success
			case '200' : $type = 'OK'; break;
			case '201' : $type = 'Created'; break;
			case '202' : $type = 'Accepted'; break;
			case '203' : $type = 'Non-Authoritative Information'; break; // since HTTP/1.1
			case '204' : $type = 'No Content'; break;
			case '205' : $type = 'Reset Content'; break;
			case '206' : $type = 'Partial Content'; break; // RFC 7233
			case '207' : $type = 'Multi-Status'; break; // WebDAV; RFC 4918
			case '208' : $type = 'Already Reported'; break; // WebDAV; RFC 5842
			case '226' : $type = 'IM Used'; break; // RFC 3229
			
			// 3xx redirection
			case '300' : $type = 'Multiple Choices'; break;
			case '301' : $type = 'Moved Permanently'; break;
			case '302' : $type = 'Found'; break; // Previously "Moved temporarily"
			case '303' : $type = 'See Other'; break; // since HTTP/1.1
			case '304' : $type = 'Not Modified'; break; // RFC 7232
			case '305' : $type = 'Use Proxy'; break; // since HTTP/1.1
			case '306' : $type = 'Switch Proxy'; break;
			case '307' : $type = 'Temporary Redirect'; break; // since HTTP/1.1
			case '308' : $type = 'Permanent Redirect'; break; // RFC 7538
			
			// 4xx client errors
			case '400' : $type = 'Bad Request'; break;
			case '401' : $type = 'Unauthorized'; break; // RFC 7235
			case '402' : $type = 'Payment Required'; break;
			case '403' : $type = 'Forbidden'; break;
			case '404' : $type = 'Not Found'; break;
			case '405' : $type = 'Method Not Allowed'; break;
			case '406' : $type = 'Not Acceptable'; break;
			case '407' : $type = 'Proxy Authentication Required'; break; // RFC 7235
			case '408' : $type = 'Request Timeout'; break;
			case '409' : $type = 'Conflict'; break;
			case '410' : $type = 'Gone'; break;
			case '411' : $type = 'Length Required'; break;
			case '412' : $type = 'Precondition Failed'; break; // RFC 7232
			case '413' : $type = 'Payload Too Large'; break; // RFC 7231
			case '414' : $type = 'URI Too Long'; break; // RFC 7231
			case '415' : $type = 'Unsupported Media Type'; break; // RFC 7231
			case '416' : $type = 'Range Not Satisfiable'; break; // RFC 7233
			case '417' : $type = 'Expectation Failed'; break;
			case '418' : $type = 'I\'m a teapot'; break; // RFC 2324, RFC 7168
			case '421' : $type = 'Misdirected Request'; break; // RFC 7540
			case '422' : $type = 'Unprocessable Entity'; break; // WebDAV; RFC 4918
			case '423' : $type = 'Locked'; break; // WebDAV; RFC 4918
			case '424' : $type = 'Failed Dependency '; break; // ebDAV; RFC 4918
			case '425' : $type = 'Too Early'; break; // RFC 8470
			case '426' : $type = 'Upgrade Required'; break;
			case '428' : $type = 'Precondition Required'; break; // RFC 6585
			case '429' : $type = 'Too Many Requests'; break; // RFC 6585
			case '431' : $type = 'Request Header Fields Too Large'; break; // RFC 6585
			case '451' : $type = 'Unavailable For Legal Reasons'; break; // RFC 7725
			
			// 5xx server errors
			case '500' : $type = 'Internal Server Error'; break;
			case '501' : $type = 'Not Implemented'; break;
			case '502' : $type = 'Bad Gateway'; break;
			case '503' : $type = 'Service Unavailable'; break;
			case '504' : $type = 'Gateway Timeout'; break;
			case '505' : $type = 'HTTP Version Not Supported'; break;
			case '506' : $type = 'Variant Also Negotiates'; break; // RFC 2295
			case '507' : $type = 'Insufficient Storage'; break; // WebDAV; RFC 4918
			case '508' : $type = 'Loop Detected'; break; // WebDAV; RFC 5842
			case '510' : $type = 'Not Extended'; break; // RFC 2774
			case '511' : $type = 'Network Authentication Required'; break; // RFC 6585
			
		}
		
		return $type;
		
	}

}

?>