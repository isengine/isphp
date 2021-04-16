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
		
		$path = self::clearSlashes(self::toUrl($path));
		
		if ($path) {
			return ($absolute ? null : ($host ? self::host() : null) . '/') . $path . (!$nofolder ? '/' : null);
		} else {
			return '/';
		}
		
	}
	
	static public function realToRelativeUrl($path = null) {
		
		// корректно преобразует заданный абсолютный путь в относительный
		// относительно базовой директории хоста
		
		$host = System::server('root');
		if (Strings::find($path, $host) === 0) {
			return Strings::get($path, Strings::len($host) - 1);
		}
		
	}
	
	static public function toUrl($path) {
		return preg_replace('/\:(?!\/)+|\\\\|\//u', '/', $path);
	}
	
	static public function toReal($path) {
		return preg_replace('/\:(?!\/)+|\\\\|\//u', DS, $path);
	}
	
	static public function parent($path, $level = null) {
		
		// корректно выбирает родительский каталог в заданном пути
		// второй аргумент позволяет выбрать уровень смещения родителя
		
		$start = Strings::first($path);
		//$real = Strings::match($path, DS);
		$url = Strings::match($path, '://');
		//$array = Strings::split($path, $real ? '\\\\' : '\/');
		$array = Strings::split($path, $url ? '\/' : '\\' . DS);
		
		//echo '[' . print_r($array, 1) . ']<br>';
		
		$first = Objects::first($array, 'value');
		if (!Objects::last($array, 'value')) {
			$level++;
		}
		$array = Objects::get($array, 0, $level + 1, 'r');
		
		if (!System::set($array)) {
			$array = $start === '\\' || $start === '/' ? true : null;
		} elseif (Objects::len($array) === 1) {
			$f = Objects::first($array, 'value');
			if (Strings::match($f, ':')) {
				$array = $first . ($url ? '/' : null);
			} else {
				$array = null;
			}
		}
		
		$result = $array === true ? '' : Strings::join($array, $url ? '/' : DS);
		
		return $result || $result === '' ? $result . ($url ? '/' : DS) : null;
		
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