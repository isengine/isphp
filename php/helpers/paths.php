<?php
namespace is\Helpers;

class Paths {

	static public function parseUrl($url, $get = null){
		
		/*
		*  Функция парсинга url-адреса
		*  первый аргумент - адрес для парсинга,
		*  второй - если нужно вернуть только одну часть
		*  
		*  scheme, host, port, user, password, path, query, fragment
		*/
		
		$parse = parse_url($url);
		
		$parse['password'] = $parse['pass'];
		unset($parse['pass']);
		
		return $get ? $parse[$get] : $parse;
		
	}
	
	static public function parseFile($url, $get = null){
		
		/*
		*  Функция парсинга имени и адреса файла
		*  первый аргумент - адрес для парсинга,
		*  второй - если нужно вернуть только одну часть
		*  
		*  path, file, name, extension
		*/
		
		$parse = pathinfo($url);
		
		$isfile = $parse['extension'] ? true : null;
		$dirname = $parse['dirname'] && $parse['dirname'] !== '.' && $parse['dirname'] !== '..' ? $parse['dirname'] . DS : null;
		
		if ($isfile) {
			$result = [
				'name' => $parse['filename'],
				'extension' => $parse['extension'],
				'file' => $parse['basename'],
				'path' => $dirname
			];
		} else {
			$result = [
				'name' => null,
				'extension' => null,
				'file' => null,
				'path' => $dirname . $parse['basename'] . DS
			];
		}
		
		$result['exists'] = file_exists($url);
		
		unset($parse);
		
		return $get ? $result[$get] : $result;
		
	}
	
	static public function host($scheme = null) {
		return ($scheme ? $scheme : $_SERVER['REQUEST_SCHEME']) . '://' . (extension_loaded('intl') ? idn_to_utf8(
			$_SERVER['HTTP_HOST'],
			null,
			version_compare(PHP_VERSION, '7.2.0', '<') ? INTL_IDNA_VARIANT_2003 : INTL_IDNA_VARIANT_UTS46
		) : $_SERVER['HTTP_HOST']);
	}
	
	static public function prepareUrl($path = null, $host = null) {
		
		// корректно преобразует заданный путь в относительный
		// оставляет начало в абсолютном пути
		
		// узнаем, файл это или папка
		// и определяем, содержит ли путь фрагмент
		
		$nofolder = self::parseFile($path, 'file') || Strings::match($path, '#');
		
		// определяем, абсолютный путь или нет (относительный) по :\ и :/ в строке
		// в unix-системах пути будут относительными
		
		$absolute = preg_match('/\:(\/|\\\\)/u', $path);
		
		$path = preg_replace('/\:(?!\/)+|\\\\|\//u', '/', $path);
		$path = self::clearSlashes($path);
		
		if ($path) {
			return ($absolute ? null : ($host ? self::host() : null) . '/') . $path . (!$nofolder ? '/' : null);
		} else {
			return '/';
		}
		
	}
	
	static public function parent($path) {
		
		// корректно выбирает родительский каталог в заданном пути
		
		$host = Strings::find($path, ':\\\\');
		if ($host) {
			$path = Strings::get($path, $host + 2);
		} else {
			$host = Strings::find($path, '://');
			if ($host) {
				$path = Strings::get($path, $host + 2);
			} else {
				$host = Strings::find($path, ':\\');
				if ($host) {
					$path = Strings::get($path, $host + 1);
				} else {
					$host = Strings::find($path, ':/');
					if ($host) {
						$path = Strings::get($path, $host + 2);
					}
				}
			}
		}
		
		$convert = Strings::replace($path, [':', '\\', '/'], ':');
		$last = Strings::last($convert);
		if (Strings::last($convert) === ':') {
			$convert = Strings::unlast($convert);
		}
		
		$pos = Strings::find($convert, ':', 'r');
		//if ($pos) { $pos++; } else { $pos = 0; }
		
		$path = $pos ? Strings::get($path, 0, $pos + 1) : '/';
		
		return $path;
		
	}
	
	static public function clearSlashes($path) {
		return preg_replace('/^[\\\\\/]*(.*?)[\\\\\/]*$/ui', '$1', $path);
	}
	
	static public function clearDoubleSlashes($path) {
		return preg_replace('/([\\\\\/]){2,}/ui', '$1', $path);
	}
	
	// перенести сюда другие функции из path

}

?>