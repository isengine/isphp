<?php
namespace is\Helpers;

class Prepare {
	
	/*
	*  Класс, который производит очистку данных по заданному параметру
	*  например, перед передачей ее для записи в базу данных
	*  на входе нужно указать значение $data и тип преобразования $type
	*  
	*  параметры типов (если нужно несколько, можно перечислять через пробел и/или запятую):
	*    format - оставление в строке только (!) цифр, латинских букв, пробелов, и разрешенных знаков для передачи данных в формате системы
	*    alphanumeric - оставление в строке только (!) цифр, латинских букв и пробелов
	*    numeric - оставление в строке только (!) цифр
	*    datetime - оставление в строке только (!) цифр и знаков, встречающихся в формате даты и времени
	*    phone - приведение строки к телефонному номеру
	*    phone_ru - приведение строки к телефонному номеру россии (+7 заменяется на 8)
	*    phone_link - приведение строки к телефонной ссылке (добавляется + вначале)
	*    login/email - приведение строки к формату логина/email
	*    url - приведение строки к формату url, включая спецсимволы
	*    simpleurl - приведение строки к формату url без спецсимволов, с обрезкой всех параметров
	*    urlencode - приведение строки к формату url, в котором символы кодируются % и hex-кодом
	*    urldecode - приведение строки из формата urlencode в обычный текстовый вид
	*    leavespaces - укажите, чтобы оставить по одному пробелу (если они вообще есть) в начале и в конце сторки
	*    tospaces - приведение всех пробелов, табуляций и символов пробелов к одному пробелу
	*    nospaces - удаление всех пробелов
	*    codespaces - удаление незначащих для кода пробелов, сокращение кода
	*    onestring - приведение данных к однострочному виду
	*    code - htmlspecialchars
	*    entities - htmlentities
	*    notags - удаление всех тегов
	*    cleartags - очищение всех атрибутов внутри тегов
	*    tags - удаление всех тегов, кроме разрешенных
	*      чтобы этот параметр работал корректно, входящие данные должны быть кодированы 
	*      htmlspecialchars, в противном случае теги будут очищены
	*      на предварительном этапе обработки
	*  
	*  теперь, если указать третий параметр 'false', то чистка тегов будет пропущена
	*  т.е. все теги в тексте останутся как есть
	*  если указать 'true', то будут оставлены только теги по-умолчанию
	*  если же задать массив, то будут исключены все теги, кроме указанных
	*  действие этого параметра не распространяется на код php и скрипты, т.к. они будут очищены в любом случае
	*  
	*  в функцию добавился четвертый параметр, который может быть как массивом, так и иметь значение true
	*  в качестве массива он может содержать ключи 'minlen', 'maxlen', 'minnum', 'maxnum' и 'match',
	*  по которым будет идти проверка входной строки, объект же будет преобразован в массив
	*  также данный параметр имеет и еще одно свойство: если этот параметр не пустой
	*  (например, строка или число, хотя мы настоятельно рекомендуем указывать массив или 'true'),
	*  окончательная, очищенная строка будет сравниваться с исходной
	*  в случае совпадения будет возвращаться очищенная строка, в противном случае - false
	*  таким образом, эта функция теперь объединяет в себе очищение и проверку
	*  
	*  также нельзя считать эту функцию полностью безопасной, т.к. она не очистит обфусцированные и шифрованные данные
	*  (т.е. переданные фрагментами), например: '<scr ipt>' или 'PHNjcmlwdD4=' (base64)
	*  однако мы стараемся сделать так, чтобы все файлы системы проходили антивирусную проверку,
	*  в частности через AIBolit, и не выдавать даже подозрений на вирусы,
	*  так чтобы вредоносный код можно было сразу же обнаружить
	*  
	*  на выходе отдает преобразованное значение $data
	*/
	
	static public function clear($data) {
		
		$data = self::script($data);
		$data = self::stripTags($data);
		$data = self::trim($data);
		$data = self::spaces($data);
		
		return $data;
		
	}
	
	static public function script($data) {
		
		// выполняем предварительное очищение - от скриптов, программного кода
		
		$data = preg_replace('/<\?.+?\?>/u', '', $data);
		$data = preg_replace('/<script.+?\/script>/ui', '', $data);
		
		return $data;
		
	}
	
	static public function stripTags($data, $tags = null) {
		
		// продолжаем предварительное очищение - от всех тегов, кроме разрешенных
		
		// задаем разрешенные теги
		
		if (empty($tags)) {
			$tags = [
				// base elements
				'a', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'span', 'font', 'br', 'hr', 'img',
				// base formatting
				'b', 'i', 's', 'u', 'blockquote', 'code', 'del', 'em', 'ins', 'small', 'strong', 'sub', 'sup', 
				// list
				'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'details', 'summary',
				// table
				'table', 'thead', 'tbody', 'tfoot', 'th', 'tr', 'td', 'col', 'colgroup', 'caption',
				// additional
				'abbr', 'bdi', 'bdo', 'cite', 'dfn', 'kbd', 'mark', 'q', 'rp', 'rt', 'rtc', 'ruby', 'samp', 'var', 'wbr'
			];
		} else {
			$tags = Objects::convert($tags);
		}
		
		// подготавливаем список
		$strip = '';
		foreach ($tags as $tag) {
			$strip .= '<' . $tag . '>';
		}
		
		// завершаем
		unset($tags, $tag);
		
		// очищаем
		$data = strip_tags($data, $strip);
		
		return $data;
		
	}
	
	static public function trim($data, $replace = null) {
		
		// убираем все пробелы и табуляцию вначале и в конце
		// второй параметр задает замену при очищении
		// по-умолчанию, пусто
		// можно указать '$1' для замены на найденное значение
		
		$data = preg_replace('/^(\s|(&nbsp;))+/ui', $replace, $data);
		$data = preg_replace('/(\s|(&nbsp;))+$/ui', $replace, $data);
		
		return $data;
		
	}
	
	static public function spaces($data, $replace = '$1') {
		
		// убираем двойные пробелы и табуляцию
		// второй параметр задает замену при очищении
		// по-умолчанию, '$1' - замена на найденное значение
		// можно указать null для очищения (nospaces) или ' ' для замены на пробел (tospaces)
		
		$data = preg_replace('/(\s|&nbsp;)+/ui', $replace, $data);
		
		return $data;
		
	}
	
	static public function comments($data) {
		
		// убираем комментарии
		
		// clear comments [//...] from json parse with frotect of 'href://' string
		$data = preg_replace('/([^\:\"\'])\s*?\/\/.*?([$\r\n])/u', '$1$2', $data);
		// clear comments [/*...*/]
		$data = preg_replace('/\/\*.*?\*\//u', '', $data);
		// clear comments [<!--...-->]
		$data = preg_replace('/(\<\!\-\-).*?(\-\-\>)/u', '', $data);
		
		return $data;
		
	}
	
	static public function format($data) {
		$data = preg_replace('/[^a-zA-Z0-9_\- .,:;]/u', '', $data);
		return $data;
	}
	static public function letters($data) {
		$data = preg_replace('/[^\w]|\d/u', '', $data);
		return $data;
	}
	static public function words($data) {
		$data = preg_replace('/[^\w ]|\d/u', '', $data);
		return $data;
	}
	static public function text($data) {
		$data = preg_replace('/^[\w\d\s\-\'\"\.\,\!\?\(\)\:\№\*«»…—‒–]+$/u', '', $data);
		return $data;
	}
	static public function alphanumeric($data, $replace = null, $compact = true) {
		$data = preg_replace('/[^a-zA-Z0-9_\- ]/u', $replace, $data);
		if ($compact) {
			$data = preg_replace('/\\' . $replace . '{2,}/u', $replace, $data);
		}
		return $data;
	}
	static public function numeric($data) {
		$data = preg_replace('/^[^\d]+?(\d)/', '$1', $data);
		$data = str_replace(',', '.', $data);
		$data = (float) $data;
		return $data;
	}
	static public function datetime($data) {
		$data = preg_replace('/[^0-9_\-.,:()\\\\\/ ]/u', '', $data);
		return $data;
	}
	static public function phone($data, $locale = null) {
		
		$data = preg_replace('/[^0-9*#]/u', '', $data);
		//$original = !empty($special) ? $data : null;
		
		if ($locale === 'ru') {
			$first = mb_substr($data, 0, 1);
			if (strlen($data) == 10) {
				$data = substr_replace($data, '7', 0, 0);
				//$original = !empty($special) ? $data : null;
			} elseif ($first == 8) {
				$data = substr_replace($data, '7', 0, 1);
			}
		}
		
		return '+' . $data;
		
	}
	static public function codephone($data) {
		return preg_replace('/[^0-9+*#]/u', '', $data);
	}
	static public function login($data) {
		return self::email($data);
	}
	static public function email($data) {
		$data = preg_replace('/[^a-zA-Z0-9\-_.@]/u', '', $data);
		return $data;
	}
	static public function url($data) {
		$data = preg_replace('/[^a-zA-Z0-9\-_.:\/?&\'\"=#+]/u', '', $data);
		$data = rawurlencode($data);
		return $data;
	}
	static public function simpleurl($data) {
		$data = preg_replace('/[?&].*$/u', '', $data);
		$data = preg_replace('/[^a-zA-Z0-9\-_.:\/\w]/u', '', $data);
		$data = htmlspecialchars($data);
		return $data;
	}
	static public function urlencode($data) {
		$data = rawurlencode($data);
		return $data;
	}
	static public function urldecode($data) {
		$data = rawurldecode($data);
		//$data = preg_replace('/[^a-zA-Z0-9\-_.,:\/?&=#+\w ]/u', '', $data);
		return $data;
	}
	static public function onestring($data) {
		// clear line breaks from json prepare: vvv
		// $data = preg_replace('/\r\n\s*|\r\s*|\n\s*/u', '', $data);
		$data = preg_replace('/([^\s]|^)[\s]*(\r?\n){1,}[\s]*([^\s]|$)/u', '$1 $3', $data);
		return $data;
	}
	static public function code($data) {
		$data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5);
		return $data;
	}
	static public function entities($data) {
		$data = htmlentities($data);
		return $data;
	}
	static public function tags($data) {
		$data = htmlspecialchars_decode($data);
		$data = strip_tags($data, $striptags);
		return $data;
	}
	static public function notags($data, $replace = null) {
		
		// второй параметр задает замену при очищении
		// по-умолчанию, null - замена на пустое значение
		// можно указать ' ' для замены на пробел (notagsspaced)
		
		//$data = preg_replace('/([^\s\t]|^)[\s\t]*(\r?\n){1,}[\s\t]*([^\s\t]|$)/', '$1 $3', $data);
		$data = preg_replace('/(<\/\w+?>)|(<\w+?\s.+?>)|(<\w+?>)/u', $replace, $data);
		
		return $data;
		
	}
	static public function cleartags($data) {
		//$data = preg_replace('/([^\s\t]|^)[\s\t]*(\r?\n){1,}[\s\t]*([^\s\t]|$)/', '$1 $3', $data);
		$data = preg_replace('/<(\w+)?\s.+?>/u', '<$1>', $data);
		return $data;
	}
	
	static public function len($data, $min = null, $max = null) {
		
		// раньше было specail minlen/maxlen
		// сравнение original/minmun/maxnum теперь через класс match
		
		// правило, задающее минимальную длину строки
		
		if (
			!empty($min) &&
			is_numeric($min) &&
			$min > 0 &&
			mb_strlen($data) < $min
		) {
			$data = null;
		}
		
		// правило, задающее максимальную длину строки
		
		if (
			!empty($max) &&
			is_numeric($max) &&
			$max > 0 &&
			mb_strlen($data) > $max
		) {
			$data = mb_substr($data, 0, $max);
		}
		
		return $data;
		
	}
	
	static public function upper($data) {
		
		// правило, переводящую строку в верхний регистр
		
		return mb_convert_case($data, MB_CASE_UPPER);
		
	}
	
	static public function lower($data) {
		
		// правило, переводящую строку в верхний регистр
		
		return mb_convert_case($data, MB_CASE_LOWER);
		
	}
	
	static public function upperFirst($data) {
		
		// правило, переводящую строку в верхний регистр
		
		return mb_convert_case(mb_substr($data, 0, 1), MB_CASE_UPPER) . mb_convert_case(mb_substr($data, 1), MB_CASE_LOWER);
		
	}
	
	static public function upperEach($data) {
		
		// правило, переводящую строку в верхний регистр
		
		return mb_convert_case($data, MB_CASE_TITLE);
		
	}
	
	static public function encode($str) {
		
		/*
		*  Функция которая кодирует данные
		*  на входе нужно указать исходную строку
		*  на выходе отдает готовую строку
		*  кодирование происходит в формат base64
		*  
		*  Теперь функция кодирует и данные в формате json
		*/
		
		$type = System::typeOf($str);
		
		if ($type === 'iterable') {
			$str = Parser::toJson($str);
		} elseif ($type !== 'scalar') {
			return null;
		}
		
		//if (!System::typeOf($str, 'scalar')) {
		//	return null;
		//}
		
		$len = Strings::len($str) % 3;
		if ($len) {
			$str = Strings::add($str, 3 - $len);
		}
		
		return base64_encode($str);
		
	}
	
	static public function decode($str) {
		
		/*
		*  Функция которая декодирует данные
		*  на входе нужно указать исходную строку
		*  на выходе отдает готовую строку
		*  декодирование происходит из формата base64
		*  
		*  Теперь функция декодирует и данные в формате json
		*/
		
		if (!System::typeOf($str, 'scalar')) {
			return null;
		}
		
		$result = base64_decode($str);
		
		if (System::typeOf($result, 'iterable')) {
			$result = Parser::fromJson($result);
		}
		
		return $result;
		
	}
	
	static public function hash($str) {
		
		/*
		*  Функция которая делает хэш данных
		*  на входе нужно указать исходную строку
		*  на выходе отдает готовую строку
		*/
		
		if (!System::typeOf($str, 'scalar')) {
			return null;
		}
		
		return md5($str);
		
	}
	
	static public function matchHash($str, $hash) {
		
		/*
		*  Функция которая проверяет,
		*  соответствует ли переданная строка своему хэшу
		*  на входе нужно указать исходную строку и ее хэш
		*  на выходе отдает результат проверки
		*/
		
		if (!System::typeOf($str, 'scalar')) {
			return null;
		}
		
		return md5($str) === $hash;
		
	}
	
	static public function crypt($str) {
		
		/*
		*  Функция которая делает необратимое шифрование данных
		*  на входе нужно указать исходную строку
		*  на выходе отдает готовую строку
		*/
		
		if (!System::typeOf($str, 'scalar')) {
			return null;
		}
		
		return password_hash($str, PASSWORD_DEFAULT);
		
	}
	
	static public function matchCrypt($str, $hash) {
		
		/*
		*  Функция которая проверяет,
		*  соответствует ли переданная строка своему хэшу в необратимом шифровании
		*  на входе нужно указать исходную строку и ее хэш
		*  на выходе отдает результат проверки
		*/
		
		if (!System::typeOf($str, 'scalar')) {
			return null;
		}
		
		return password_verify($str, $hash);
		
	}
	
	static public function toObject($data, $num = null) {
		
		/*
		*  Функция которая преобразует строку в объект
		*  Является синонимом Objects::convert
		*  Теперь в нее добавлен второй аргумент,
		*  позволяющий ограничить число элементов в массиве
		*  положительное значение - от начала,
		*  отрицательное значение - от конца
		*/
		
		$data = Objects::convert($data);
		
		if ($num > 0) {
			$data = Objects::get($data, 0, $num);
		} elseif ($num < 0) {
			$data = Objects::get($data, 0 - $num, 0, true);
		}
		
		return $data;
		
	}
	
	static public function toString($data) {
		
		/*
		*  Функция которая преобразует объект в строку
		*  Является синонимом Strings::join
		*/
		
		return Strings::join($data, ':');
		
	}
	
	static public function toJson($data) {
		
		/*
		*  Функция которая преобразует объект в json
		*  Является синонимом Parser::toJson
		*/
		
		return Parser::toJson($data);
		
	}
	
	static public function replace($data, $search, $replace = null) {
		
		/*
		*  Функция которая делает замену в строке
		*  Является синонимом Strings::replace
		*/
		
		if (!System::typeOf($data, 'scalar')) {
			return null;
		}
		
		return Strings::replace($data, $search, $replace);
		
	}
	
}

?>