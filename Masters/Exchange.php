<?php

namespace is\Masters;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Match;
use is\Helpers\Paths;
use is\Helpers\Prepare;

use is\Parents\Data;

class Exchange extends Data {
	
	// не кэшируется
	// дочерняя группа классов называется каналами
	// и находится в пространстве имен channels
	// 
	// мастер работает со следующими основными методами
	// - get, запрос на получение данных у стороннего сервиса
	// - send, отправка данных стороннему сервису
	// - connect, проверка подключения
	// это абстрактные методы, которые должны быть реализованы
	// при реализации, эти методы должны передавать сведения о выполнении,
	// статусы и ошибки, и записывать их в соответствующие
	// 
	// также есть уже реализованные методы проверки
	// - status, отправка статуса, статусы разные для каждого сервиса
	// - error, отправка ошибок, ошибки разные для каждого сервиса
	// - success, отправка проверки на успешность выполненного действия
	// в случае наличия ошибок, будет false, иначе - true
	// 
	// мастер также имеет следующие свойства
	// - error, уведомление об ошибке при обмене с сервисом
	// в общем случае проверяется только сама ошибка,
	// но конкретно для каждого класса статус ошибки может быть разным
	// - data, полученные или подготовленные к отправке данные
	// размещаются в данных
	// каждый канал создается с переданным массивом данных
	// 
	// пример вызова
	// $a = new Exchange('email');
	// $a -> init($data);
	// $a -> channel -> connect();
	// $a -> channel -> send();
	// $a -> channel -> success();
	
	public $channel;
	
	public function __construct($name) {
		$n = Prepare::upperFirst($name);
		$ns = __NAMESPACE__ . '\\Channels\\' . $n;
		$this -> channel = new $ns($this -> getData());
	}
	
	// синтаксический сахар для более простого вызова
	// стандартных методов дочернего класса
	// например, вместо
	// $a -> channel -> connect();
	// будет
	// $a -> connect();
	public function receive() {
		return $this -> channel -> receive();
	}
	public function send() {
		return $this -> channel -> send();
	}
	public function connect() {
		return $this -> channel -> connect();
	}
	public function status() {
		return $this -> channel -> status();
	}
	public function error() {
		return $this -> channel -> error();
	}
	public function success() {
		return $this -> channel -> success();
	}
	public function set($first, $second = null) {
		return $this -> channel -> set($first, $second);
	}
	
}

// now use
// echo $view -> get('lang') -> get('information:work:0');
// echo $view -> get('lang|information:work:0');
// echo $view -> call('lang:get', 'information:work:0');


?>