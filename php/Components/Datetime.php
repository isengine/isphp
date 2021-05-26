<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Helpers\Prepare;
use is\Parents\Globals;

/*
https://www.php.net/manual/ru/datetime.format.php
ATOM = "Y-m-d\TH:i:sP"
COOKIE = "l, d-M-Y H:i:s T"
ISO8601 = "Y-m-d\TH:i:sO"
RFC822 = "D, d M y H:i:s O"
RFC850 = "l, d-M-y H:i:s T"
RFC1036 = "D, d M y H:i:s O"
RFC1123 = "D, d M Y H:i:s O"
RFC7231 = "D, d M Y H:i:s \G\M\T"
RFC2822 = "D, d M Y H:i:s O"
RFC3339 = "Y-m-d\TH:i:sP"
RFC3339_EXTENDED = "Y-m-d\TH:i:s.vP"
RSS = "D, d M Y H:i:s O"
W3C = "Y-m-d\TH:i:sP"

//$dt = Datetime::getInstance();
//$dt -> setFormat('{yy}.{nn}.{dd} {hour}.{min}.{sec}');
//echo $dt -> getFormat() . '<br>';
//$d = 'year 2020,12 31st 12hours00min';
//$r = $dt -> setDate($d);
//$dt -> setTimestamp();
//$dt -> setTimestamp('1509405200');
//echo print_r($dt -> getDate(), 1) . '<br>';
//echo print_r($dt -> getDate('{hour}:{min}.{sec}/{y}-{nn}-{dd}'), 1) . '<br>';
//echo print_r($dt -> getDate('{absolute}'), 1) . '<br>';
//echo print_r($dt -> convertDate('2002-20-45', '{yy}-{hour}-{min}', '{a}'), 1) . '<br>';

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
			
			// ISO 8601
			
			'YYYY' => 'Y', // год, 4 знака: 2019
			'YY' => 'y', // год, 2 знака: 19
			'MM' => 'm', // месяц, 2 знака, с нулем: 01-12
			'M' => 'n', // месяц, 1-2 знака, без нуля: 1-12
			'DD' => 'd', // день, 2 знака, с нулем: 01-31
			'D' => 'j', // день, 1-2 знака, без нуля: 1-31
			
			'hh' => 'H', // часы, 24-часовой формат, с нулем: 01-24
			'h' => 'G', // часы, 24-часовой формат, без нуля: 1-24
			'gg' => 'h', // часы, 12-часовой формат, с нулем: 01-12
			'g' => 'g', // часы, 12-часовой формат, без нуля: 1-12
			'mm' => 'i', // минуты, с нулем: 00-59
			'm' => 'i', // минуты, с нулем: 00-59
			'ss' => 's', // секунды, с нулем: 00-59
			's' => 's', // секунды, с нулем: 00-59
			
			// дополнительные значения
			
			'yy' => 'Y', // год, 4 знака: 2019
			'y' => 'y', // год, 2 знака: 19
			'nn' => 'm', // месяц, 2 знака, с нулем: 01-12
			'n' => 'n', // месяц, 1-2 знака, без нуля: 1-12
			'dd' => 'd', // день, 2 знака, с нулем: 01-31
			'd' => 'j', // день, 1-2 знака, без нуля: 1-31
			
			'ww' => 'z', // день в году, 0-365
			'w' => 'N', // день недели, 1-7
			
			'p' => 'a', // префикс: am/pm
			'z' => 'Z', // временная зона, в миллисекундах: от -43200 до 50400
			
			'aa' => 'U.v', // абсолютное время, число секунд и миллисекунд с эпохи unix
			'a' => 'U', // абсолютное время, число секунд с эпохи unix
			
			// именованные значения
			
			'year' => 'Y', // год, 4 знака: 2019
			'month' => 'm', // месяц, 2 знака, с нулем: 01-12
			'day' => 'd', // день, 2 знака, с нулем: 01-31
			'hour' => 'H', // часы, 24-часовой формат, с нулем: 01-24
			'min' => 'i', // минуты, с нулем: 00-59
			'sec' => 's', // секунды, с нулем: 00-59
			'msec' => 'v', // миллисекунды, с нулем: 000-999
			
			'ampm' => 'a', // префикс: am/pm
			'week' => 'W', // номер недели в году, 1-42
			'days' => 't', // число дней в месяце: 28-31
			'zone' => 'P', // временная зона, двоеточие между часами и минутами: +02:00
			
			'abs' => 'U' // абсолютное время, число секунд с эпохи unix
			
		];
		
		//foreach ($this -> convert as $key => $item) {
		//	$this -> addData($key, $this -> convertFromSystem($item));
		//}
		//unset($key, $item);
		
	}
	
	//public function getDatetime($format, $time) {
	//	return $this -> convertFromSystem($this -> convert[$format], $time);
	//}
	//
	//public function convertFromSystem($format, $time = null) {
	//	return date($format, System::set($time) ? $time : $this -> mtime());
	//}
	
	// format datetime
	
	public function getFormat() {
		return $this -> format;
	}
	
	public function setFormat($format) {
		$this -> format = $format;
		$this -> format_php = $this -> convertFormat();
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
		$timestamp = $timestamp ? (int) $timestamp : $this -> mtime();
		$this -> date -> setTimestamp($timestamp);
		$this -> timestamp = $this -> date -> getTimestamp();
	}
	
	// setDatetime
	
	public function setDate($string, $format = null) {
		
		if (System::type($string, 'numeric')) {
			$this -> setTimestamp($string);
			return;
		}
		
		if (!System::typeOf($string, 'scalar')) {
			return;
		}
		
		if (System::type($format, 'string')) {
			$this -> setFormat($format);
		}
		
		$this -> date = $this -> createDate($string);
		$this -> timestamp = $this -> date -> getTimestamp();
		
	}
	
	public function createDate($string = null, $format = null) {
		
		$format_in = !$format ? $this -> format_php : ( Strings::match($format, '{') ? $this -> convertFormat($format) : constant('\DateTimeInterface::' . Prepare::upper($format)) );
		
		$string = $string ? $string : ( Strings::match($format, '{aa}') ? $this -> mtime() : time() );
		
		$date = $this -> date -> createFromFormat('!' . $format_in, $string);
		
		if (!$date) {
			
			$pos_last = Strings::find($format, '}', 'r');
			$len = Strings::len($format) - 1;
			
			if (!System::set($pos_last)) {
				return;
			}
			
			$format = Strings::get(
				$format,
				0,
				$len > $pos_last ? $len : Strings::find($format, '{', 'r')
			);
			
			$date = $this -> createDate($string, $format);
			
		}
		
		return $date;
		
	}
		
	public function getDate($format = null) {
		
		$format_php = $this -> convertFormat($format);
		return $this -> date -> Format($format_php);
		
	}
	
	public function convertDate($string = null, $input = null, $output = null) {
		
		$date = $this -> createDate($string, $input);
		
		if (!$date) {
			return;
		}
		
		$format_out = !$output ? $this -> format_php : ( Strings::match($output, '{') ? $this -> convertFormat($output) : constant('\DateTimeInterface::' . Prepare::upper($output)) );
		
		return $date -> format($format_out);
		
	}
	
	public function compareDate($now, $min = null, $max = null, $format = null) {
		
		$result = 0;
		
		$now = $now ? $this -> convertDate($now, $format, '{abs}') : time();
		$min = $min ? $this -> convertDate($min, $format, '{abs}') : null;
		$max = $max ? $this -> convertDate($max, $format, '{abs}') : null;
		
		if ($min && $now < $min) {
			$result = -1;
		} elseif ($max && $now > $max) {
			$result = 1;
		}
		
		return $result;
		
		// если результат 0, т.е. !$result
		// это значит, что указанная дата находится в допустимом диапазоне
		
	}
	
	public function mtime() {
		return round(microtime(true)*1000)/1000;
	}
	
	public function milliseconds() {
		return Strings::get(microtime(), 2, 3);
	}
	
}

?>