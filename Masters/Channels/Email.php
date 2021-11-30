<?php

namespace is\Masters\Channels;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Paths;
use is\Helpers\Prepare;

class Email extends Master {
	
	// - from
	// необязательно, по-умолчанию будет подставлен адрес сайта и 'no-reply'
	// email адрес отправителя, который будет подставлен в поле, от кого отправлено сообщение
	// может быть как в виде 'name@mail.com' или '<name@mail.com>', так и в виде 'Title <name@mail.com>'
	// - to
	// email адрес получателя должен быть представлен обычным адресом вида 'name@mail.com'
	// - subject
	// тема письма
	// - message
	// сообщение
	// - files
	// ссылки на файлы, относительные, в массиве
	// - dkim
	// dkim-подпись, пока функционал не реализован
	// - template
	// адрес шаблона оформления письма
	// - plain
	// разрешает отправлять кроме форматированного сообщения еще и обычный текст
	// - break
	// можно назначить перенос принудительно
	// некоторые почтовые серверы корректно отрабатывают "\n" вместо "\r\n"
	// причем константа EOL не поможет, т.к. она отвечает за перенос
	// на сервере текущего хоста, а не на почтовом сервере, с которым вы работаете
	
	public $from;
	public $to;
	public $subject;
	public $message;
	public $files;
	public $dkim;
	public $template;
	public $plain;
	public $break = "\r\n";
	
	public $header;
	public $body;
	public $boundary;
	
	public function receive() {
		
	}
	
	public function send() {
		
		if (!$this -> to) {
			$this -> setError('destination address not set');
			return;
		}
		
		// формирование от кого
		
		$fromName = $this -> from ? null : System::server('host');
		$fromAddr = $this -> from ? null : '<no-reply@' . System::server('host') . '>';
		if (Strings::match($this -> from, '<') && Strings::match($this -> from, '>')) {
			$fromName = trim(Strings::before($this -> from, '<'));
			$fromAddr = Strings::after($this -> from, '<', true);
		} elseif (Strings::match($this -> from, '@')) {
			$fromAddr = '<' . $this -> from . '>';
		}
		$from = ($fromName ? "=?UTF-8?B?" . base64_encode($fromName) . "?= " : null) . $fromAddr;
		unset($fromName, $fromAddr);
		
		// базовые заголовки
		
		$this -> setHeader('Date', date('r'));
		$this -> setHeader('From', $from);
		$this -> setHeader('Reply-To', $from);
		$this -> setHeader('MIME-Version', '1.0');
		$this -> setHeader('Message-ID', '<' . sha1(microtime(true)) . '@' . System::server('host') . '>');
		$this -> setHeader('X-Mailer', 'PHP/' . phpversion()); //является необязательным
		
		// dkim
		
		if ($this -> dkim) {
			$this -> setHeader('DKIM-Signature', $this -> dkim);
		}
		
		// проверка на наличие шаблона
		
		if (!empty($this -> template)) {
			$template = DR . Paths::toReal($this -> template) . '.php';
			if (file_exists($template)) {
				require $template;
			}
		}
		
		// тело письма
		
		if ($this -> files || $this -> plain) {
			$this -> openMultipart();
			$this -> setMultipart();
			$this -> setFiles();
			$this -> closeMultipart();
		} else {
			$this -> setHeader('Content-Type', 'text/html; charset=UTF-8');
			$this -> setHeader('Content-Transfer-Encoding', '8bit');
			$this -> setMessage();
		}
		
		if (!$this -> body) {
			$this -> setError('message not set');
			return;
		}
		
		// отправка письма
		
		$result = mail(
			$this -> to,
			$this -> subject ? "=?UTF-8?B?" . base64_encode($this -> subject) . "?=" : null,
			$this -> body,
			$this -> header
		);
		
		//System::debug($result);
		//echo '<hr>*******************************<hr>';
		
	}
	
	public function connect() {
		
	}
	
	public function setHeader($name, $value) {
		$this -> header .= $name . ": " . $value . $this -> break;
	}
	
	public function setBody($data) {
		$this -> body .= $data . $this -> break;
	}
	
	public function setMessage($plain = null) {
		if (!$plain && !Strings::match($this -> message, '<html>')) {
			$this -> body .= '<html>' . $this -> break;
		}
		$this -> body .= wordwrap($plain ? strip_tags($this -> message) : $this -> message, 70, $this -> break);
		if (!$plain && !Strings::match($this -> message, '</html>')) {
			$this -> body .= $this -> break . '</html>';
		}
	}
	
	public function openMultipart() {
		$this -> boundary = md5(time());
		$this -> setHeader('Content-Type', 'multipart/mixed; boundary="' . $this -> boundary . '"');
	}
	
	public function closeMultipart() {
		$this -> setBody('');
		$this -> setBody('--' . $this -> boundary . '--');
	}
	
	public function setMultipart() {
		
		$this -> setBody('');
		$this -> setBody('--' . $this -> boundary);
		$this -> setBody('Content-Type: text/html; charset=UTF-8');
		$this -> setBody('Content-Transfer-Encoding: 8bit');
		$this -> setBody('');
		
		$this -> setMessage();
		
		if ($this -> plain) {
			$this -> setBody('');
			$this -> setBody('--' . $this -> boundary);
			$this -> setBody('Content-Type: text/plain; charset=UTF-8');
			$this -> setBody('Content-Transfer-Encoding: 8bit');
			$this -> setBody('');
			
			$this -> setMessage('plain');
		}
		
	}
	
	public function setFiles() {
		
		$this -> files = is_string($this -> files) ? [$this -> files] : $this -> files;
		
		if (!System::typeIterable($this -> files)) {
			return;
		}
		
		foreach ($this -> files as $item) {
			$name = Prepare::urlencode(Strings::after(Paths::toUrl($item), '/', null, true));
			$real = DI . Paths::toReal($item);
			
			if (!file_exists($real)) {
				continue;
			}
			
			$this -> setBody('');
			$this -> setBody('--' . $this -> boundary);
			$this -> setBody('Content-Type: application/octet-stream');
			$this -> setBody('Content-Transfer-Encoding: base64');
			$this -> setBody('Content-Disposition: attachment; filename*=UTF-8\'\'' . $name);
			$this -> setBody('');
			$this -> body .= chunk_split(base64_encode(file_get_contents($real)));
			
		}
		unset($item);
		
	}
	
}

?>