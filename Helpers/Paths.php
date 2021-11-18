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
		
		// данный параметр позволяет обнаружить файл в системе
		// однако она вызывает предупреждение, если права на доступ ограничены,
		// например когда система ищет файл в корневой директории
		// к тому же этот параметр нигде не используется,
		// он был сделан про запас, и потому принято решение его отключить
		// -
		//if (!$get || $get === 'exists') {
		//	$result['exists'] = file_exists($url);
		//}
		
		unset($parse);
		
		return $get ? $result[$get] : $result;
		
	}
	
	static public function prepareUrl($path = null, $host = null) {
		
		// корректно преобразует заданный путь в относительный
		// оставляет начало в абсолютном пути
		
		// узнаем, файл это или папка
		// и определяем, содержит ли путь фрагмент
		
		// здесь мы задаем поиск только в пределах папки System::server('root'),
		// так как все url должны находиться именно там
		$nofolder = self::parseFile(realpath(System::server('root') . $path), 'file') || Strings::match($path, '#') || Strings::match($path, '?');
		//$nofolder = self::parseFile($path, 'file') || Strings::match($path, '#') || Strings::match($path, '?');
		
		$absolute = self::absolute($path);
		
		$path = self::clearSlashes(self::toUrl($path));
		
		if ($path) {
			return ($absolute ? null : ($host ? System::server('domain') : null) . '/') . $path . (!$nofolder ? '/' : null);
		} else {
			return '/';
		}
		
	}
	
	static public function realToRelativeUrl($path = null) {
		
		// корректно преобразует заданный абсолютный путь в относительный
		// относительно базовой директории хоста
		
		$host = DI;
		if (Strings::find($path, $host) === 0) {
			return Strings::get($path, Strings::len($host) - 1);
		}
		
	}
	
	static public function toUrl($path) {
		return preg_replace('/\:(?!(\/|\\\\))+|\\\\|\//u', '/', $path);
	}
	
	static public function toReal($path) {
		return preg_replace('/\:(?!(\/|\\\\))+|\\\\|\//u', DS, $path);
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
	
	static public function absolute($path) {
		
		// определяем, абсолютный путь или нет (относительный) по :\ и :// в начале строки
		// в unix-системах пути всегда относительные:
		// localhost/path
		// /var/server/path
		// абсолютные url без протокола не проходят проверку,
		// поэтому они должны начинаться с протокола, адреса диска или с двойного слеша
		// однако это требование равносильно и для браузеров,
		// иначе адрес будет распознан неверно
		// пример:
		// other.com (false) -> site.com/other.com
		// //other.com (true) -> other.com
		// http://other.com (true) -> other.com
		
		return preg_match('/^(\/|\\\\){2}|\:(\/|\\\\)/u', Strings::get($path, 0, 8));
		
	}
	
	static public function querySplit($string) {
		// парсит строку get-запроса в массив данных
		$query = [];
		$string = Strings::after(Prepare::urldecode($string), '?');
		Objects::each(Strings::split($string, '&'), function($i) use (&$query){
			$a = Strings::split($i, '=');
			$k = Strings::replace($a[0], ['[', ']'], [':', null]);
			$map = Strings::split($k, ':');
			$query = Objects::inject($query, $map, $a[1]);
		});
		return $query;
	}
	
	static public function queryJoin($array) {
		// составляет строку get-запроса из массива данных
		if (is_array($array)) {
			Objects::recurse($array, function($i){
				return Prepare::urlencode($i);
			});
			return '?' . http_build_query($array);
		}
		return;
	}
	
	static public function restJoin($array) {
		// составляет rest строку из массива данных
		if (is_array($array)) {
			Objects::recurse($array, function($i){
				return Prepare::urlencode($i);
			});
			return Strings::replace(
				http_build_query($array),
				['%5B', '%5D', '&', '='],
				[':', null, '/', '/']
			) . '/';
		}
		return;
	}
	
	// перенести сюда другие функции из path
	
}

?>