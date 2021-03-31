<?php
namespace is\Helpers;

class Parser {

	static public function textVariables($string, $function) {
		
		/*
		*  Функция парсинга текстовых переменных с многоуровневыми вложениями
		*  текстовая переменная должна иметь вид {type|data1:data2:data3...}
		*  Функция требует на вход строку для парсинга и функцию-обработчик
		*  Обработчик должен принимать первым аргуметом тип, вторым - массив данных
		*  таким образом, эта функция - лишь инструмент для реализации
		*  алгоритма разбора текстовых переменных
		*  это сделано потому, что реализация тесно связана с реализацией шаблонов
		*/
		
		if (!$string) {
			return null;
		} elseif (!Strings::match($string, '{')) {
			return $string;
		}
		
		$regexp = '/\{(?>[^}{]+|(?R))+\}/u';
		
		return preg_replace_callback($regexp, function($data) use ($function) {
			
			$data = $data[0];
			$data = Strings::get($data, 1, 1, 'r');
			
			if (Strings::match($data, '{')) {
				$data = self::textVariables($data, $function);
			}
			
			$data = self::fromString($data, ['simple' => null]);
			
			if (!System::typeIterable($data)) {
				return null;
			}
			
			$type = $data[0][0];
			$params = $data[1];
			
			return $function($type, $params);
			
		}, $string);
		
	}
	
	static public function fromString($item = null, $parameters = []) {
		
		/*
		*  Функция парсинга системных данных в системный объект
		*/
		
		$parameters = array_merge(['key' => null, 'clear' => null, 'simple' => true], is_array($parameters) ? $parameters : [$parameters]);
		
		$key = System::set($parameters['key']);
		
		if (System::type($item) !== 'string') {
			return $item;
		} elseif (
			mb_strpos($item, ':') === false &&
			mb_strpos($item, '|') === false
		) {
			if (mb_strpos($item, '!') === 0) {
				return null;
			} else {
				return $key ? [$item => null] : [$item];
			}
		} else {
			
			$split = Strings::split($item, '|', true);
			
			// сразу же делаем проверку и разбиваем два действия по условию,
			// чтобы не проверять потом при каждой итерации
			
			if ($key) {
				
				// это код для разбивки массива с ключами
				$split = Objects::each($split, [], function($i, $k, &$r) use ($parameters) {
					if (!System::set($i)) {
						return null;
					} elseif (mb_strpos($i, ':') === false) {
						$r[$i] = null;
					} else {
						
						//$spliti = Strings::split($i, '(?<!\\):', null);
						$spliti = Strings::split($i, ':', null);
						
						$splitk = reset($spliti);
						unset($spliti[0]);
						
						if (mb_strpos($splitk, '!') === 0) {
							return null;
						} if (!System::set($spliti)) {
							$r[$splitk] = null;
						} else {
							$r[$splitk] = Objects::each($spliti, [], function($i, $k, &$a) use ($parameters) {
								$r = mb_strpos($i, '!') !== 0 ? System::set($i, true) : null;
								if (System::type($r) === 'numeric') { $r = (float) $r; } // сразу приведение типов
								// этот код вместо вызова array_clear
								// не пробегает лишний раз по массиву и экономит ресурсы
								if (!$parameters['clear'] || $parameters['clear'] && $r) {
									$a[$k - 1] = $r;
								}
								unset($r);
							});
							// этот код вместо вызова array_simple
							// не пробегает лишний раз по массиву и экономит ресурсы
							if ($parameters['simple'] && count($r) === 1) {
								$r[$splitk] = array_shift($r[$splitk]);
							}
						}
						
						return null;
						
					}
				});
				
			} else {
				
				// это код для разбивки массива без ключей
				$split = Objects::each($split, null, function($i) use ($parameters) {
					if (!System::set($i)) {
						return null;
					} elseif (mb_strpos($i, ':') === false) {
						if (System::type($i) === 'numeric') { $i = (float) $i; } // сразу приведение типов
						//return [$i];
						return $parameters['simple'] ? $i : [$i];
					} else {
						
						//$spliti = Strings::split($i, '(?<!\\):', null);
						$spliti = Strings::split($i, ':', null);
						
						$a = Objects::each($spliti, [], function($i, $k, &$a) use ($parameters) {
							$r = mb_strpos($i, '!') !== 0 ? System::set($i, true) : null;
							if (System::type($r) === 'numeric') { $r = (float) $r; } // сразу приведение типов
							// этот код вместо вызова array_clear
							// не пробегает лишний раз по массиву и экономит ресурсы
							if (!$parameters['clear'] || $parameters['clear'] && $r) {
								$a[$k] = $r;
							}
							unset($r);
						});
						
						// этот код вместо вызова array_simple
						// не пробегает лишний раз по массиву и экономит ресурсы
						if ($parameters['simple'] && count($a) === 1) {
							$a = array_shift($a);
						}
						
						return $a;
						
					}
				});
				
				
				if ($parameters['simple'] && count($split) === 1) {
					$split = Objects::first($split, 'value');
				}
				
			}
			
			// этот код выключен, т.к. он оказался слишком неэкономный по ресурсам
			//if ($parameters['clear']) {
			//	$split = Objects::array_clear($split);
			//}
			//if ($parameters['simple']) {
			//	$split = Objects::array_simple($split);
			//}
			
			return $split;
			
		}
	}

	static public function toString($item, $parameters = ['key' => null, 'clear' => null, 'simple' => null]) {
		
		/*
		*  Функция обратного преобразования системного объекта в системные данные
		*  параметры:
		*    key - задает ключи первыми значениями после разделителя
		*    clear - пропускает пустые значения массива
		*    simple - преобразует одно значение массива в одно число
		*/
		
		$item = Objects::convert($item);
		
		$key = !empty($parameters['key']) ? true : null;
		$clear = !empty($parameters['clear']) ? true : null;
		$levels = Objects::levels($item, 2);
		$str = '';
		
		$first = Objects::first($item);
		$item = Objects::unfirst($item);
		
		if ($levels === 1) {
			
			if ($clear) {
				$str .= $key ? $first['key'] . System::set($first['value'], ':') : null;
				$str .= System::set($first['value'], true);
			} else {
				$str .= $key ? $first['key'] . ':' : null;
				$str .= $first['value'];
			}
			
			if (System::typeData($item, 'object')) {
				foreach ($item as $k => $i) {
					$str .= $key ? '|' . $k : null;
					$str .= $clear ? System::set($i, ':' . $i) : ':' . $i;
				}
				unset($k, $i);
			}
			
		} else {
			
			$parameters['key'] = null;
			
			$str .= $key ? $first['key'] . ':' : null;
			$str .= self::toString($first['value'], $parameters);
			
			if (System::typeData($item, 'object')) {
				foreach ($item as $k => $i) {
					$str .= '|' . ($key ? $k . ($clear ? System::set($i, ':') : ':') : null);
					$str .= self::toString($i, $parameters);
				}
				unset($key, $item);
			}
			
		}
		
		return $str;
		
	}

	static public function fromJson($data, $format = true){
		
		/*
		*  Функция обработки данных в формате json
		*  на входе нужно указать данные в виде строковой переменной
		*  
		*  функция примет данные и
		*    переведет их в массив, если второй параметр $format задан true/structure/content
		*    переведет их в объект, если второй параметр $format задан false или не задан
		*/
		
		if (empty($data)) {
			return null;
		}
		
		if ($format === 'structure') {
			$data = str_replace(['[', ']'], ['{','}'], $data);
			$data = preg_replace('/(["\}])(\s+\")/u', '$1,$2', $data);
			$data = preg_replace_callback(
				'/(\"[\w:\-_.]+\"[^ :],?)/u', 
				function ($matches, $i=0) {
					static $i;
					return '"' . ++$i . '" : ' . $matches[1];
				},
				$data
			);
		} elseif ($format === 'content') {
			$data = preg_replace('/([\"\'])\s{2,}/u', '$1 ', $data);
			$data = preg_replace('/\s{2,}([\"\'])/u', ' $1', $data);
			$data = htmlspecialchars($data, ENT_NOQUOTES);
			//$data = clear($data, 'tags tospaces'); // <------------------------------------
			$data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
		}
		
		// clear comments [//...]
		$data = preg_replace('/([^\:\"\'])\s*?\/\/.*?([$\r\n])/u', '$1$2', $data);
		// clear line breaks
		$data = preg_replace('/\r\n\s*|\r\s*|\n\s*/u', '', $data);
		// clear comments [/*...*/]
		$data = preg_replace('/\/\*.*?\*\//u', '$1', $data);
		// clear empty arrays
		$data = preg_replace('/\[\s*\]/u', '[]', $data);
		$data = preg_replace('/\[\"\s*\"\]/u', '[]', $data);
		
		//$data = clear($data); // <------------------------------------
		
		$data = json_decode($data, true);
		
		if ($format && System::typeData($data)) {
			$data = self::prepare($data);
		}
		
		return $data;
		
	}
	
	static public function toJson($data, $format = null) {
		
		/*
		*  Функция перевода данных из массива в формат json
		*  на входе нужно указать данные в виде массива
		*  
		*  функция примет его и переведет в формат json
		*/
		
		$data = json_encode($data, $format ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : JSON_UNESCAPED_UNICODE);
		
		return $data;
		
	}
	
	static public function prepare($data, $up = null) {
		
		/*
		*  Рекурсивная функция для обработки массива и приведения его к надлежащему виду
		*  Необходима для работы парсера и в других случаях
		*  
		*  Сюда включено:
		*  выключение ключей и значений массива
		*  замена текущего родительского значения дочерним значением с ключом,
		*  содержащим определенный параметр (языковую конструкцию)
		*/
		
		foreach ($data as $key => &$item) {
			if (
				mb_strpos($key, '!') === 0 ||
				!is_array($item) && strpos($item, '!') === 0
			) {
				unset($data[$key]);
			} elseif (!empty($up) && is_array($up) && in_array($key, $up)) {
				$data = Objects::merge($data, $data[$key]);
				unset($data[$key]);
			} elseif (is_array($item)) {
				$item = self::prepare($item);
			}
		}
		
		return $data;
		
	}
	
}

?>