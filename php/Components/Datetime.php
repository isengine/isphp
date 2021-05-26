<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Parents\Globals;

/*
https://www.php.net/manual/ru/datetime.format.php
N	Порядковый номер дня недели в соответствии со стандартом ISO-8601	от 1 (понедельник) до 7 (воскресенье)
w	Порядковый номер дня недели	от 0 (воскресенье) до 6 (суббота)
z	Порядковый номер дня в году (начиная с 0)	От 0 до 365
W	Порядковый номер недели года в соответствии со стандартом ISO-8601; недели начинаются с понедельника	Например: 42 (42-я неделя года)
t	Количество дней в указанном месяце	от 28 до 31
a	Ante meridiem (лат. "до полудня") или Post meridiem (лат. "после полудня") в нижнем регистре	am или pm
A	Ante meridiem или Post meridiem в верхнем регистре	AM или PM
g	Часы в 12-часовом формате без ведущего нуля	от 1 до 12
G	Часы в 24-часовом формате без ведущего нуля	от 0 до 23
h	Часы в 12-часовом формате с ведущим нулём	от 01 до 12
H	Часы в 24-часовом формате с ведущим нулём	от 00 до 23
Z	Смещение временной зоны в секундах. Для временных зон, расположенных западнее UTC возвращаются отрицательные числа, а расположенных восточнее UTC - положительные.	от -43200 до 50400
(z / 3600) и наоборот - смешение в часах
*/

class Datetime extends Globals {
	
	public $convert;
	
	public $date;
	
	public $format;
	public $format_php;
	public $timestamp;
	
	public function init() {
		
		$this -> date = new \DateTime;
		
		$this -> convert = [
			
			'yy' => 'Y',
			'mm' => 'm',
			'dd' => 'd',
			
			'y' => 'y',
			'm' => 'n',
			'd' => 'j',
			
			'hh' => 'H',
			'h' => 'G',
			
			'hour' => 'H',
			'min' => 'i',
			'sec' => 's',
			
			'absolute' => 'U',
			'abs' => 'U',
			'a' => 'U'
			
		];
		
		foreach ($this -> convert as $key => $item) {
			$this -> addData($key, $this -> convertFromSystem($item));
		}
		unset($key, $item);
		
	}
	
	public function getDatetime($format, $time) {
		return $this -> convertFromSystem($this -> convert[$format], $time);
	}
	
	public function convertFromSystem($format, $time = null) {
		return date($format, System::set($time) ? $time : time());
	}
	
	// format datetime
	
	public function getFormat() {
		return $this -> format;
	}
	
	public function setFormat($format) {
		$this -> format = $format;
		$this -> format_php = '!' . $this -> convertFormat();
	}
	
	public function convertFormat($format = null) {
		$format = $format ? $format : $this -> format;
		$format = preg_replace('/(\w(?!\w*\}))/ui', '\\\\$1', $format);
		$format = preg_replace_callback('/\{\w+\}/ui', function($match){
			$item = reset($match);
			$item = Strings::get($item, 1, 1, true);
			return $this -> convert[$item];
		}, $format);
		return $format;
	}
	
	// timestamp
	
	public function getTimestamp() {
		// возвращаем всегда одно значение, чтобы было меньше вычислений
		return $this -> timestamp;
	}
	
	public function setTimestamp($timestamp = null) {
		// вычисляем результат один раз, а потом будем только его возвращать
		$timestamp = $timestamp ? (int) $timestamp : time();
		$this -> date -> setTimestamp($timestamp);
		$this -> timestamp = $this -> date -> getTimestamp();
	}
	
	// setDatetime
	
	public function setDate($string, $format = null) {
		
		if (System::type($format, 'string')) {
			$this -> setFormat($format);
		}
		
		if (System::type($string, 'numeric')) {
			$this -> setTimestamp($string);
			return;
		}
		
		if (!System::typeOf($string, 'scalar')) {
			return;
		}
		
		$this -> date = $this -> date -> createFromFormat($this -> format_php, $string);
		$this -> timestamp = $this -> date -> getTimestamp();
		
	}
	
	public function getDate($format = null) {
		
		$format_php = $this -> convertFormat($format);
		return $this -> date -> Format($format_php);
		
	}
	
	public function convertDate($string, $input = null, $output = null) {
		
		$format_in = $this -> convertFormat($input);
		$format_out = $this -> convertFormat($output);
		
		$date = $this -> date -> createFromFormat('!' . $format_in, $string);
		return $date -> Format($format_out);
		
	}
	
}

?>