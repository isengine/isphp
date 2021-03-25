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
		return ($scheme ? $scheme : $_SERVER['REQUEST_SCHEME']) . '://' . (extension_loaded('intl') ? idn_to_utf8($_SERVER['HTTP_HOST']) : $_SERVER['HTTP_HOST']);
	}
	
	static public function relativeReal($path) {
		// внутренняя функция
		$root_len = Strings::len(DR);
		if (Strings::get($path, 0, $root_len) === DR) {
			$path = Strings::get($path, $root_len);
		}
		$parse = self::parseFile($path, 'file');
		$path = self::clearSlashes( self::convertToReal($path) );
		return $path ? $path . (!$parse ? DS : null) : null;
	}
	
	static public function absoluteReal($path) {
		// вообще нигде не используется
		// конвертирует путь в абсолютный
		return DR . self::relativeReal($path);
	}
	
	static public function mergeAbsolutePath($path = null) {
		// внутренняя функция
		// определяет, абсолютный путь или нет (относительный)
		// по :\ и :/ в строке
		// в unix-системах пути будут относительными
		return preg_match('/\:(\/|\\\\)/u', $path);
	}
	
	static public function fragmentUrl($path = null) {
		// внутренняя функция
		if (Strings::match($path, '#') && Strings::last($path) === '/') {
			$path = Strings::unlast($path);
		}
		return $path;
	}
	
	static public function prepareUrl($path = null) {
		// вообще нигде не используется
		$path = self::relativeUrl($path, 'file');
		if (Strings::first($path) === '/') {
			$path = Strings::unfirst($path);
		}
		return self::fragmentUrl($path);
	}
	
	static public function relativeUrl($path = null) {
		// ставит относительный линк в структуре
		// преобразует линк в ошибке
		$parse = self::parseFile($path, 'file');
		$absolue = self::mergeAbsolutePath($path);
		$path = self::clearSlashes( self::convertToUrl($path) );
		$path = $path ? ($absolue ? null : '/') . $path . (!$parse ? '/' : null) : '/';
		return self::fragmentUrl($path);
	}
	
	static public function absoluteUrl($path, $scheme = null) {
		// вообще нигде не используется
		$absolue = self::mergeAbsolutePath($path);
		return ($absolue ? null : self::host($scheme)) . self::relativeUrl($path);
	}
	
	static public function parent($path) {
		
		$convert = Strings::replace($path, [':', '\\', '/'], ':');
		$last = Strings::last($convert);
		if (Strings::last($convert) === ':') {
			$convert = Strings::unlast($convert);
		}
		$pos = Strings::find($convert, ':', 'r') + 1;
		return Strings::get($path, 0, $pos);
		
	}
	
	static public function convertToReal($path) {
		// внутренняя функция
		return preg_replace('/\:(?!\/)+|\\\\|\//u', DS, $path);
	}
	
	static public function convertToUrl($path) {
		// внутренняя функция
		return preg_replace('/\:(?!\/)+|\\\\|\//u', '/', $path);
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