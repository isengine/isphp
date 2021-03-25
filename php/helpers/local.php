<?php
namespace is\Helpers;

class Local {

	static public function list($path, $parameters = [], $basepath = null) {
		
		// now dirconnect and fileconnect is localList
		
		//fileconnect($dir, $ext = false)
		//localList($path, ['return' => 'files'/*, 'type' => $ext*/]) // $ext - либо массив значений, либо строка '_:_:_'
		//dirconnect($dir, $ext = false)
		//localList($path, ['return' => 'folders'/*, 'skip' => $ext*/]) // $ext - либо массив значений, либо строка '_:_:_'
		
		/*
		*  Функция получения списка файлов или папок с определенным расширением
		*  на входе нужно указать путь к папке $name и массив параметров
		*  
		*  return
		*    files - только файлы
		*    folders - только папки
		*    пропуск - и то и другое
		*  type/extension
		*    для файлов, вернуть только файлы указанного типа
		*  info
		*    любой ключ - часть по указанному ключу
		*    пропуск - полную инфу
		*  subfolders
		*    true/false включать в список подпапки
		*  nodisable
		*    true/false включать в список заблокированные элементы - файлы и папки
		*  merge
		*    true/false смешать список - папки, затем подпапки, затем файлы, затем подфайлы
		*  skip * пока не реализован
		*    set - строка исключения
		*    folders
		*      true/false разрешить исключать папки
		*    files
		*      true/false разрешить исключать файлы
		*  mask * пока не реализован
		*    set - строка совпадения
		*    in - ключ info, где будет использована
		*  
		*  третий параметр используется только для служебных целей, т.к. функция рекурсивная при вызове параметра 'subfolders'
		*  
		*  функция возвращает готовый массив
		*/
		
		if (!file_exists($path) || !is_dir($path)) {
			return false;
		}
		
		$path = str_replace(['/', '\\'], DS, $path);
		if (substr($path, -1) !== DS) { $path .= DS; }
		
		$scan = scandir($path);
		
		if (empty($scan)) {
			return false;
		}
		
		// настраиваем параметры
		
		$parameters['extension'] = Parser::fromString($parameters['extension']);
		$parameters['skip'] = Parser::fromString($parameters['skip']);
		
		// разбираем список
		
		$list = [
			'folders' => [],
			'files' => [],
			'sub' => [
				'folders' => [],
				'files' => []
			]
		];
		
		foreach ($scan as $key => $item) {
			
			if ($item !== '.' && $item !== '..') {
				
				$pathto = $path . $item;
				$isdir = is_dir($pathto);
				
				$i = [
					'fullpath' => $pathto . ($isdir ? DS : null),
					'name' => $item,
					'type' => ($isdir ? 'folder' : 'file'),
					'path' => $basepath ? Strings::get($pathto, Strings::len($basepath), Strings::len($item), true) : null,
					'file' => null,
					'extension' => null
				];
				
				$disable = $parameters['nodisable'] ? null : Strings::first($item) === '!';
				//echo $disable . '<br>';
				
				if (
					!$disable && !$isdir &&
					$parameters['return'] !== 'folders'
				) {
					$info = pathinfo($pathto);
					$i['file'] = $info['filename'];
					$i['extension'] = $info['extension'];
					
					if (
						!$parameters['extension'] ||
						$parameters['extension'] && $i['extension'] && Match::equalIn($parameters['extension'], $i['extension'])
					) {
						$list['files'][] = $parameters['info'] ? $i[$parameters['info']] : $i;
					}
				}
				
				if (!$disable && $isdir) {
					
					if ($parameters['return'] !== 'files') {
						$list['folders'][] = $parameters['info'] ? $i[$parameters['info']] : $i;
					}
					
					if ($parameters['subfolders']) {
						$sub = self::list($pathto, $parameters, $basepath ? $basepath : $path);
						
						$list['sub'] = array_merge_recursive(
							$list['sub'] ? $list['sub'] : [],
							$sub ? $sub : []
						);
						
						if ($basepath) {
							$list['folders'] = array_merge_recursive(
								$list['folders'] ? $list['folders'] : [],
								$list['sub']['folders'] ? $list['sub']['folders'] : []
							);
							$list['files'] = array_merge_recursive(
								$list['files'] ? $list['files'] : [],
								$list['sub']['files'] ? $list['sub']['files'] : []
							);
							unset($list['sub']);
						}
						
						//echo '<pre>+'.print_r($sub, 1).'</pre><br>';
						//echo '<hr>';
					}
					
				}
				
				unset($i);
				//echo '<pre>'.print_r($i, 1).'</pre><br>';
				
			}
			
		}
		
		//$list['folders'] = array_merge_recursive($list['folders'], $list['sub']['folders'], $list['files'], $list['sub']['files']);
		
		if (!$basepath && $parameters['merge']) {
			$list = array_merge($list['folders'], $list['sub']['folders'], $list['files'], $list['sub']['files']);
		}
		//$list = array_merge($list['folders'], $list['files']);
		//if (!$basepath) { echo '<pre>'.print_r($list, 1).'</pre><br>'; }
		
		return $list;
		
	}

	static public function createFile($target) {
		
		/*
		*  Функция создает файл
		*/
		
		$pos = Strings::find($target, DS, 'r') + 1;
		$folder = Strings::get($target, 0, $pos);
		$file = Strings::get($target, $pos);
		
		if (!file_exists($folder)) {
			self::createFolder($folder);
		}
		
		file_put_contents($target, null, LOCK_EX);
		
	}

	static public function createFolder($target) {
		
		/*
		*  Функция создает папку
		*/
		
		$split = Strings::split($target, '\\' . DS);
		//echo print_r($a, 1);
		
		$result = null;
		
		foreach ($split as $item) {
			$result .= $item . DS;
			if (!file_exists($result)) {
				mkdir($result);
			}
			//echo print_r($result, 1) . '(' . $ex . ')<br>';
		}
		unset($item);
		
	}

	static public function deleteFolder($target) {
		
		/*
		*  Функция удаляет папку вместе со всем содержимым
		*/
		
		self::eraseFolder($target);
		chmod($target, 0755);
		rmdir($target);
		
	}

	static public function eraseFolder($target) {
		
		/*
		*  Функция очищает содержимое папки
		*/
		
		$list = self::list($target, ['subfolders' => true, 'merge' => true]);
		$list = Objects::reverse($list);
		
		foreach ($list as $item) {
			if ($item['type'] === 'folder') {
				chmod($item['fullpath'], 0755);
				rmdir($item['fullpath']);
			} else {
				self::deleteFile($item['fullpath']);
			}
		}
		unset($item);
		
	}

	static public function readFile($target) {
		
		/*
		*  Функция открывает файл $target
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*  
		*  вывод через функцию file_get_contents по сравнению с fopen+fgets+fclose
		*  оказывается быстрее при том же потреблении памяти, т.к. использует memory mapping
		*  
		*  функция вернет строку
		*/
		
		if (!file_exists($target)) {
			return null;
		}
		
		return file_get_contents($target);
		
	}

	static public function readFileLine($target, $separator = null) {
		
		/*
		*  Функция открывает файл $target и читает его построчно
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*  вторым аргументом можно задать разделитель строк, по-умолчанию - без разделителя
		*  
		*  отличие от предыдущей функции в том, что эта работает построчно
		*  
		*  функция возвращает строку
		*/
		
		if (!file_exists($target)) {
			return null;
		}
		
		$result = null;
		
		$handle = fopen($target, "r");
		while(!feof($handle)) {
			$result .= fgets($handle) . $separator;
		}
		fclose($handle);
		
		return $result;
		
	}

	static public function readFileArray($target) {
		
		/*
		*  Функция открывает файл $target и читает его построчно
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*  
		*  отличие от предыдущей функции в том, что эта возвращает массив строк
		*/
		
		if (!file_exists($target)) {
			return null;
		}
		
		$lines = [];
		
		$handle = fopen($target, "r");
		while(!feof($handle)) {
			$lines[] = fgets($handle);
		}
		fclose($handle);
		
		return $lines;
		
	}

	static public function readFileGenerator($target) {
		
		/*
		*  Функция открывает файл $target и читает его построчно
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*  
		*  отличие от предыдущих функций в том, что эта действует через генератор
		*  это значит, что она позволяет распределять ресурсы при большой нагрузке
		*  и потребляет меньше оперативной памяти - размером ровно на одну строку
		*  
		*  результат этой функции нужно оборачивать в итератор, например:
		*  
		*  foreach (Local::readFileGenerator($path) as $index => $line) {
		*    ...
		*  }
		*  
		*  функция возвращает текущую строку итерации
		*/
		
		$handle = fopen($target, "r");
		while(!feof($handle)) {
			yield fgets($handle);
		}
		fclose($handle);
		
	}

	static public function writeFile($target, $data = null, $mode = null) {
		
		/*
		*  Функция сохраняет данные $data в файл $target
		*  
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*  второй параметр - данные для записи
		*  последний параметр задает режим
		*    null/false/по-умолчанию - запись в новый файл
		*    replace - замена файла
		*    append - дозапись в конец файла
		*  
		*  здесь вывод через функцию file_put_contents по сравнению с fopen+fwrite+fclose
		*  
		*  функция вернет true в случае успешного выполнения
		*/
		
		if (is_writable($target)) {
			if (!$mode) {
				return false;
			} elseif ($mode === 'replace') {
				self::deleteFile($target);
			}
		}
		
		if ($mode === 'append') {
			return file_put_contents($target, $data, FILE_APPEND | LOCK_EX);
		} else {
			return file_put_contents($target, $data, LOCK_EX);
		}
		
	}

	static public function writeFileLine($target, $data = null, $mode = null, $separator = PHP_EOL) {
		
		/*
		*  Функция открывает файл $target и записывает его построчно
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*  
		*  отличие от предыдущей функции в том, что эта
		*  может принимать на вход как массив, так и обычные данные
		*  и записывает их построчно
		*/
		
		$handle = fopen($target, $mode === 'append' ? "c" : "w");
		fseek($handle, 0, SEEK_END);
		if (System::typeOf($data, 'iterable')) {
			foreach ($data as $item) {
				fwrite($handle, $item . $separator);
			}
			unset($item);
		} else {
			fwrite($handle, $data);
		}
		
		fclose($handle);
		
	}

	static public function writeFileGenerator($target, $mode = null, $separator = PHP_EOL) {
		
		/*
		*  Функция открывает файл $target и записывает его построчно
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*  
		*  отличие от предыдущих функций в том, что эта действует через генератор
		*  это значит, что она позволяет распределять ресурсы при большой нагрузке
		*  и потребляет меньше оперативной памяти - размером ровно на одну строку
		*  
		*  передавать данные нужно через втроенный системный метод send(), например:
		*  $file = Local::writeFileGenerator($path);
		*  foreach ($data as $index => $line) {
		*    ...
		*    $file -> send($line);
		*  }
		*  
		*  Функция прекращает работу после передачи пустого значения
		*/
		
		$handle = fopen($target, $mode === 'append' ? "c" : "w");
		fseek($handle, 0, SEEK_END);
		
		$c = true;
		
		while ($c) {
			$data = yield;
			if (
				!System::set($data)
			) {
				$c = null;
			} else {
				fwrite($handle, $data . $separator);
			}
		}
		
		fclose($handle);
		yield false;
		
	}

	static public function deleteFile($target) {
		
		/*
		*  Функция удаляет файл $target
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*/
		
		if (!file_exists($target)) {
			return null;
		}
		
		chmod($target, 0644);
		unlink($target);
		
	}

	static public function eraseFile($target) {
		
		/*
		*  Функция очищает содержимое файла $target, оставляя сам файл 
		*  на входе нужно указать полный путь к файлу с названием и расширением
		*/
		
		fclose(fopen($target, 'w'));
		
	}

	static public function link($path, $folder, $prefix = true, $variant = null, $return = null) {
		
		/*
		*  Простая функция, которая проверяет указанных файл
		*  и возвращает путь к нему вместе с параметром версии
		*  
		*  Первым параметром задается путь
		*  Вторым параметром - системная папка
		*  Третий, необязательный параметр - префикс, который назначается принудительно
		*   - если 'true', то устанавливается от времени изменения файла
		*   - если пустой, то не устанавливается
		*  Четвертый, необязательный параметр - минимизированный вариант файла
		*  Пятый, необязательный параметр - тип возврата
		*  
		*  Путь читается в формате '/', '\' или ':'
		*  Например, 'path\to\file.ext' вернет 'path/to/file.ext?mtime'
		*/
		
		$path = Prepare::clear($path);
		$path = Prepare::urldecode($path);
		
		if (!empty($variant)) {
			$point = strrpos($path, '.');
			$pathv = substr($path, 0, $point) . '.' . $variant . substr($path, $point);
			$filev = constant('PATH_' . strtoupper($folder)) . str_replace(['/', '\\', ':'], DS, $pathv);
			unset($point);
		} else {
			$pathv = null;
			$filev = null;
		}
		
		if (
			!empty($pathv) &&
			!empty($filev) &&
			file_exists($filev)
		) {
			$file = $filev;
			$path = $pathv;
		} else {
			$file = constant('PATH_' . strtoupper($folder)) . str_replace(['/', '\\', ':'], DS, $path);
			if (!file_exists($file)) {
				return null;
			}
		}
		
		//global $uri;
		return constant('URL_' . strtoupper($folder)) . str_replace(['/', '\\', ':'], '/', $path) . (!empty($prefix) ? '?' . ($prefix === true ? filemtime($file) : $prefix) : null);
		
	}

	static public function copy($from, $to, $rewrite = true){
		
		/*
		*  функция, позволяющая скопировать папку со всем содержимым
		*  в другую папку на сервере
		*  
		*  первый параметр - адрес исходной папки
		*  второй параметр - адрес, куда копировать (это может быть несуществующая папка)
		*  третий параметр - перезапись файлов, если они имеются
		*/
		
		if (!file_exists($from)) {
			return false;
		}
		
		if (is_dir($from)) {
			
			@mkdir($to);
			$d = dir($from);
			
			while (( $entry = $d -> read() ) !== false) {
				if ($entry == '.' || $entry == '..') continue;
				localCopy($from . DS . $entry, $to . DS . $entry, $rewrite);
			}
			
			$d -> close();
			
		} elseif (!file_exists($to) || $rewrite) {
			copy($from, $to);
		}
		
	}

	static public function saveFromUrl($filename, $url, $delete = false) {
		
		/*
		*  функция, позволяющая сохранить файл на сервере,
		*  содержимое которого прочитано из url
		*  
		*  первым параметром передается адрес файла для сохранения на сервере
		*  второй параметр - адрес ссылки
		*  третий параметр определяет поведение при условии, если файл с таким именем уже существует:
		*    false - по-умолчанию, ничего не делать, пропускать работу функции и возвращать false
		*    true - сперва удалять файл, а затем записывать его заново
		*/
		
		if (file_exists($filename)) {
			if (!$delete) {
				return false;
			} else {
				self::deleteFile($filename);
			}
		}
		
		$file = file_get_contents($url);
		
		if (empty($file)) {
			return false;
		} elseif (file_put_contents($filename, $file)) {
			return true;
		} else {
			return false;
		}
		
	}

	static public function openUrl($url, $method = false, $redirect = 0) {
		
		/*
		*  функция, позволяющая загружать файл с сервера,
		*  содержимое которого прочитано из url
		*  
		*  первым параметром передается url файла
		*  второй параметр - метод
		*  
		*  третий параметр служебный, служит для предотвращения более 1 редиректа
		*/
		
		$target = null;
		
		if (!$method || $method === true) {
			
			$target = file_get_contents($url);
			
		} elseif ($method === 'curl' && extension_loaded('curl')) {
			
			$init = curl_init();
			curl_setopt($init, CURLOPT_URL, $url);
			curl_setopt($init, CURLOPT_HTTPGET, true);
			curl_setopt($init, CURLOPT_USERAGENT, USER_AGENT);
			curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
			//curl_setopt($init, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			//curl_setopt($init, CURLOPT_HEADER, true);
			
			$target = curl_exec($init);
			//$errors = curl_error($init);
			
			// вывод ошибок пока закомментирован, т.к. он не отлажен
			
			// код ниже осуществляет проверку заголовков и статусов
			// если, например, статус не равен 200, то возвращает false
			// а если задан редирект, то переходит по нему
			
			// начало кода
			$info = [
				'headers' => curl_getinfo($init),
				'code' => null,
				'redirect' => null
			];
			
			if (!empty($info['headers']['http_code'])) {
				$info['code'] = $info['headers']['http_code'];
			}
			if (!empty($info['headers']['redirect_url'])) {
				$info['redirect'] = $info['headers']['redirect_url'];
			}
			
			unset($info['headers']);
			
			if ($info['redirect']) {
				if ($redirect > 1) {
					$target = false;
				} else {
					$target = localOpenUrl($info['redirect'], $method, $redirect++);
				}
			} elseif ($info['code'] != '200') {
				$target = false;
			}
			// конец кода
			
			if (DEFAULT_MODE === 'develop' && !empty($errors)) {
				//$target = print_r($errors, true);
			}
			
			curl_close($init);
			
		} elseif ($method === 'fsock') {
			
			// Устанавливаем соединение
			$init = fsockopen($url, -1, $errno, $errstr, 30);
			
			if (!$init && DEFAULT_MODE === 'develop') {
				
				// Проверяем успешность установки соединения
				$target = $errstr . ' (' . $errno . ')';
				
			} else {
				
				// Формируем HTTP-заголовки для передачи его серверу
				$header = 'GET ' . $url . ' ' . $_SERVER['SERVER_PROTOCOL'] . "\r\n";
				$header .= 'User-Agent: ' . USER_AGENT . "\r\n";
				$header .= "Connection: Close\r\n\r\n";
				
				// Отправляем HTTP-запрос серверу
				fwrite($init, $header);
				
				// Получаем ответ
				while (!feof($init)) {
					$target .= fgets($init, 1024);
				}
				
				// Закрываем соединение
				fclose($init);
				
			}
			
		}
		
		return $target;
		
	}

	static public function requestUrl($url, $data = false, $method = false, $redirect = 0) {
		
		/*
		*  функция, позволяющая запросить какой-либо url
		*  
		*  первым параметром передается url
		*  второй параметр - данные
		*  третий параметр - метод (любой метод обращается к вызову через curl)
		*    * на данный момент поддерживается только post и только через curl
		*    * также нужно быть внимательным, чтобы отправлять запрос с абсолютной ссылкой, а не относительной
		*  
		*  четвертый параметр служебный, служит для предотвращения более 1 редиректа
		*    * на данный момент редирект убран из кода
		*/
		
		$target = null;
		
		
		if (!$method || $method === true) {
			
			$target = file_get_contents($url);
			
		} elseif (is_string($method) && extension_loaded('curl')) {
			
			$init = curl_init();
			
			curl_setopt($init, CURLOPT_URL, $url);
			
			if (strtolower($method) === 'post') {
				curl_setopt($init, CURLOPT_POST, true);
				curl_setopt($init, CURLOPT_POSTFIELDS, $data);
			} else {
				curl_setopt($init, CURLOPT_HTTPGET, true);
				//$url .= ''
			}
			
			curl_setopt($init, CURLOPT_USERAGENT, USER_AGENT);
			curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
			
			$target = curl_exec($init);
			
			// код ниже осуществляет проверку заголовков и статусов
			// если, например, статус не равен 200, то возвращает false
			// а если задан редирект, то переходит по нему
			
			// начало кода
			$info = [
				'headers' => curl_getinfo($init),
				'code' => null
			];
			
			if (!empty($info['headers']['http_code'])) {
				$info['code'] = $info['headers']['http_code'];
			}
			
			unset($info['headers']);
			
			if ($info['code'] != '200') {
				$target = false;
			}
			// конец кода
			
			curl_close($init);
			
		}
		
		return $target;
		
	}

	static public function unzip($filename, $path, $delete = true) {
		
		/*
		*  функция, позволяющая распаковать файл, хранящийся на сервере
		*  
		*  первым параметром передается имя и адрес файла на сервере
		*  вторым - путь для распаковки
		*  третий параметр определяет поведение после успешной распаковки:
		*    false - оставить исходный файл архива
		*    true - удалить его (по-умолчанию)
		*/
		
		if (
			!extension_loaded('zip') ||
			!file_exists($filename)
		) {
			return false;
		}
		
		$zip = new ZipArchive;
		
		$res = $zip -> open($filename);
		
		if ($res === true) {
			
			$zip -> extractTo($path);
			$zip -> close();
			
			if ($delete === true) {
				if (!self::deleteFile($filename)) {
					return false;
				}
			}
			
			return true;
			
		} else {
			return false;
		}
		
	}

}

?>