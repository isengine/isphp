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
		
		if ($isfile) {
			$result = [
				'name' => $parse['filename'],
				'extension' => $parse['extension'],
				'file' => $parse['basename'],
				'path' => $parse['dirname'] . DS
			];
		} else {
			$result = [
				'name' => null,
				'extension' => null,
				'file' => null,
				'path' => $parse['dirname'] . DS . $parse['basename'] . DS
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
		$root_len = Strings::len(DR);
		if (Strings::get($path, 0, $root_len) === DR) {
			$path = Strings::get($path, $root_len);
		}
		$parse = self::parseFile($path, 'file');
		$path = self::clearSlashes( self::convertToReal($path) );
		return $path ? $path . (!$parse ? DS : null) : null;
	}
	
	static public function absoluteReal($path) {
		return DR . self::relativeReal($path);
	}
	
	static public function relativeUrl($path = null) {
		$parse = self::parseFile($path, 'file');
		$path = self::clearSlashes( self::convertToUrl($path) );
		return $path ? '/' . $path . (!$parse ? '/' : null) : '/';
	}
	
	static public function absoluteUrl($path, $scheme = null) {
		return self::host($scheme) . self::relativeUrl($path);
	}
	
	static public function convertToReal($path) {
		return str_replace([':', '\\', '/'], DS, $path);
	}
	
	static public function convertToUrl($path) {
		return str_replace([':', '\\', '/'], '/', $path);
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