<?php

namespace is\Components;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Match;
use is\Helpers\Sessions;
use is\Helpers\Paths;
use is\Helpers\Prepare;
use is\Helpers\Datetimes;
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

!NOW as DATE_... for example: DATE_ATOM

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
	
	public function init($format = null) {
		
		$this -> date = new \DateTime;
		$this -> setTimestamp();
		
		if ($format) {
			$this -> setFormat($format);
		}
		
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
	//	return date($format, System::set($time) ? $time : Datetimes::mtime());
	//}
	
	// format datetime
	
	public function getFormat() {
		return $this -> format;
	}
	
	public function setFormat($format) {
		$this -> format = $format;
		$this -> format_php = Datetimes::convertFormat($this -> format);
	}
	
	// timestamp
	
	public function getTimestamp() {
		// возвращаем всегда одно значение, чтобы было меньше вычислений
		return $this -> timestamp;
	}
	
	public function setTimestamp($timestamp = null) {
		// вычисляем результат один раз, а потом будем только его возвращать
		$timestamp = $timestamp ? (int) $timestamp : Datetimes::mtime();
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
		
		$this -> date = Datetimes::create($string, $this -> format_php);
		$this -> timestamp = $this -> date -> getTimestamp();
		
	}
	
	public function getDate($format = null) {
		$format_php = Datetimes::convertFormat($format ? $format : $this -> format);
		return $this -> date -> format($format_php);
	}
	
	public function convertDate($string = null, $input = null, $output = null) {
		return Datetimes::convert(
			$string,
			$input ? $input : $this -> format_php,
			$output ? $output : $this -> format_php
		);
	}
	
	public function compareDate($min = null, $max = null, $format = null) {
		return !Datetimes::compare(null, $min, $max, $format ? $format : $this -> format);
	}
	
}

?>