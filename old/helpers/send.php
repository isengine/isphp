<?php
namespace is\Helpers;

class Send {

	static public function send($arr, $message, $subject = null, $data = [], $clear = null) {
		
		/*
		*  функция принимает данные и отправляет сообщения
		*  на данный момент реализована отправка email, vk, whatsapp, sms
		*  обработки и проверки данных пока нет
		*  
		*  на входе нужно указать:
		*    arr - массив данных (напр. "type" : "mail", "param" : "", "id" : "mail@mail.com", "key" : "")
		*    subject - тема сообщения
		*    data - массив данных [key => item], где key - название, item - значение
		*    message - текстовое сообщение
		*    clear - параметры очистки
		*    template - разрешен ли шаблон и указываем здесь имя шаблона
		*/
		
		if (empty($arr) || !is_object($arr) && !is_array($arr)) {
			return false;
		} elseif (is_array($arr)) {
			$arr = (object) $arr;
		}
		
		$message = clear($message, $clear, false);
		$subject = clear($subject);
		
		if ($arr -> type === 'mail') {
			
			// отправка сообщений по электронной почте
			
			$headers  = "Content-type: text/html; charset=utf-8 \r\n" . 
						"From: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n" . 
						"Reply-To: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n" . 
						USER_SENDER;
			
			if (empty($arr -> id)) { $arr -> id = USERS_EMAIL; }
			
			// проверка на наличие шаблона
			
			if (!empty($arr -> template)) {
				$template = PATH_CUSTOM . 'send' . DS . 'templates' . DS . $arr -> template . '.php';
			}
			
			if (file_exists($template)) {
				$text = null;
				require $template;
				$message = $text;
			} else {
				$message = '<p>' . $message . '</p>';
				if (!empty($data)) {
					$message = '<p></p>';
					foreach ($data as $key => $item) {
						$message .= '<p>' . $key . ': ' . print_r($item, true) . '</p>';
					}
					unset($key, $item);
				}
			}
			
			$result = mail($arr -> id, $subject, $message, $headers);
			
		} elseif ($arr -> type === 'sms') {
			
			// отправка сообщений по СМС
			
			if (!empty($subject)) {
				$message = '[' . $subject . '] ' . $message;
			}
			
			if (!empty($data)) {
				$message .= ' | ' . key($data) . ': ' . array_shift($data);
				foreach ($data as $key => $item) {
					$message .= ', ' . $key . ': ' . $item;
				}
				unset($key, $item);
			}
			
			$newarr = (object) [
				'key' => json_decode('{"id":"' . $arr -> id . '","message":"' . $message . '",' . ((in_array('mbstring', get_loaded_extensions())) ? mb_substr(json_encode($arr -> key), 1) : substr(json_encode($arr -> key), 1)), true), // EDITED FOR NOT PHP MODULE
				'param' => $arr -> param
			];
			
			foreach ($newarr -> key as $k => $i) {
				$newarr -> param = str_replace( '{' . $k . '}', $i, $newarr -> param);
			}
			
			$result = file_get_contents($arr -> param);
			
		} elseif ($arr -> type === 'whatsapp' || $arr -> type === 'whatsappget') {
			
			// отправка сообщений по WhatsApp
			
			if (!empty($subject)) {
				$message = "[" . $subject . "]\r\n" . $message;
			}
			
			$message .= "\r\n";
			
			if (!empty($data)) {
				foreach ($data as $key => $item) {
					$message .= $key . ': ' . $item . "\r\n";
				}
				unset($key, $item);
			}
			
			if ($arr -> type === 'whatsappget') {
				
				$content = $arr -> param .
					'?' . $arr -> key -> token . '=' . $arr -> key -> key .
					'&' . $arr -> key -> id . '=' . $arr -> id .
					'&' . $arr -> key -> message . '=' . urlencode($message);
				$result = file_get_contents($content);
				
			} else {
				
				$result = file_get_contents($arr -> param . '?' . $arr -> key -> token . '=' . $arr -> key -> key, false, stream_context_create([
					'http' => [
						'method'  => 'POST',
						'header'  => 'Content-type: application/json',
						'content' => json_encode([
							$arr -> key -> id => $arr -> id,
							$arr -> key -> message => $message,
						])
					]
				]));
				
			}
			
		} elseif ($arr -> type === 'vk' || $arr -> type === 'vkontakte') {
			
			// отправка сообщений для вконтакте
			
			if (!empty($subject)) {
				$message = $subject . "\r\n\r\n" . $message;
			}
			
			$message .= "\r\n\r\n";
			
			if (!empty($data)) {
				foreach ($data as $key => $item) {
					$message .= $key . ': ' . $item . "\r\n";
				}
				unset($key, $item);
			}
			
			$result = file_get_contents('https://api.vk.com/method/messages.send', false, stream_context_create([
				'http' => [
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => http_build_query(
						[
							$arr -> param => $arr -> id,
							'message' => $message,
							'access_token' => $arr -> key,
							'v' => '5.37'
						]
					)
				]
			]));
			
		}
		
		// логирование результата
		
		$result = [
			'status' => $result === true ? 'ok' : 'error',
			'sets' => $arr,
			'message' => $message,
			'subject' => $subject,
			'data' => $data,
			'errors' => $result === true ? null : $result
		];
		
		if (!file_exists(PATH_LOG . 'send')) { mkdir(PATH_LOG . 'send'); }
		
		if (
			defined('LOG_MODE') &&
			(LOG_MODE === 'panic' || LOG_MODE === 'warning')
		) {
			if (!file_exists(PATH_LOG . 'send' . DS . 'log_' . $arr -> type)) { mkdir(PATH_LOG . 'send' . DS . 'log_' . $arr -> type); }
			file_put_contents(PATH_LOG . 'send' . DS . 'log_' . $arr -> type . DS . date('Ymd.His') . '.' . mt_rand(1000, 9999) . '.' . str_replace(':', '.', $_SERVER['REMOTE_ADDR']) . '.ini', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		}
		if (
			$result['status'] === 'error' &&
			defined('LOG_MODE') && LOG_MODE !== 'warning'
		) {
			if (!file_exists(PATH_LOG . 'send' . DS . 'log_' . $arr -> type)) { mkdir(PATH_LOG . 'send' . DS . 'log_' . $arr -> type); }
			file_put_contents(PATH_LOG . 'send' . DS . 'log_' . $arr -> type . DS . date('Ymd.His') . '.' . mt_rand(1000, 9999) . '.' . str_replace(':', '.', $_SERVER['REMOTE_ADDR']) . '.ini', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		}
		
		// возврат результата
		
		return $result;
		
	}

}

?>