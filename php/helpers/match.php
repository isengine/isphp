<?php
namespace is\Helpers;

class Match {

	static public function equal($haystack, $needle) {
		
		// функция задействует сравнение, приводя данные к строке
		
		return (string) $haystack === (string) $needle ? true : null;
		
	}

	static public function string($haystack, $needle) {
		
		// функция проверяет наличие строки
		
		return Strings::match($haystack, $needle);
		
	}

	static public function numeric($haystack, $min = null, $max = null) {
		
		// функция сравнивает число в диапазоне от мин до макс включительно
		// если мин/макс не заданы, то считаются минус/плюс бесконечностью
		
		$haystack = Prepare::numeric($haystack);
		$min = System::set($min) ? (float) $min : false;
		$max = System::set($max) ? (float) $max : false;
		
		$rmin = $min === false ? true : $haystack >= $min;
		$rmax = $max === false ? true : $haystack <= $max;
		
		return $rmin && $rmax ? true : null;
		
	}

	static public function equalIn($haystack, $needle, $and = true) {
		
		// функция задействует сравнение, приводя данные к строке
		// сравнение идет в массиве haystack
		
		foreach ($haystack as $item) {
			$result = self::equal($item, $needle);
			if ( ($and && !$result) || (!$and && $result) ) {
				break;
			}
		}
		unset($item);
		
		return $result;
		
	}

	static public function stringIn($haystack, $needle, $and = true) {
		
		// функция проверяет наличие строки
		// сравнение идет в массиве haystack
		
		foreach ($haystack as $item) {
			$result = self::string($item, $needle);
			if ( ($and && !$result) || (!$and && $result) ) {
				break;
			}
		}
		unset($item);
		
		return $result;
		
	}

	static public function numericIn($haystack, $min = null, $max = null, $and = true) {
		
		// функция сравнивает число в диапазоне от мин до макс включительно
		// если мин/макс не заданы, то считаются минус/плюс бесконечностью
		// сравнение идет в массиве haystack
		
		foreach ($haystack as $item) {
			$result = self::numeric($item, $min, $max);
			if ( ($and && !$result) || (!$and && $result) ) {
				break;
			}
		}
		unset($item);
		
		return $result;
		
	}

	static public function equalOf($haystack, $needle, $and = true) {
		
		// функция задействует сравнение, приводя данные к строке
		// сравнение идет c массивом needle
		
		foreach ($needle as $item) {
			$result = self::equal($haystack, $item);
			if ( ($and && !$result) || (!$and && $result) ) {
				break;
			}
		}
		unset($item);
		
		return $result;
		
	}

	static public function stringOf($haystack, $needle, $and = true) {
		
		// функция проверяет наличие строки
		// сравнение идет c массивом needle
		
		foreach ($needle as $item) {
			$result = self::string($haystack, $item);
			if ( ($and && !$result) || (!$and && $result) ) {
				break;
			}
		}
		unset($item);
		
		return $result;
		
	}

	static public function numericOf($haystack, $minmax, $and = true) {
		
		// функция сравнивает число в диапазоне от мин до макс включительно
		// если мин/макс не заданы, то считаются минус/плюс бесконечностью
		// сравнение идет c массивом needle
		
		foreach ($minmax as $item) {
			$result = self::numeric($haystack, Objects::first($item, 'value'), Objects::last($item, 'value'));
			if ( ($and && !$result) || (!$and && $result) ) {
				break;
			}
		}
		unset($item);
		
		return $result;
		
	}

	static public function common($name, $data) {
		
		// ФУНКЦИЯ ТОЛЬКО ДЛЯ ТЕСТИРОВАНИЯ, ПОДЛЕЖИТ УДАЛЕНИЮ
		return call_user_func_array('self::' . $name, $data);
		
	}

}

?>