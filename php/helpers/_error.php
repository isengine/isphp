
<?php

namespace is\Helpers;

class Error {

	static public function errorlist($code){
		
		/* ФУНКЦИЯ ВЫЗОВА ОШИБКИ */
		
		$code = (string) $code;
		$type = null;
		$error = null;
		
		switch ($code) {
			case '100' : $type = 'Continue'; break;
			case '101' : $type = 'Switching Protocols'; break;
			case '102' : $type = 'Processing'; break;
			case '200' : $type = 'OK'; break;
			case '201' : $type = 'Created'; break;
			case '202' : $type = 'Accepted'; break;
			case '203' : $type = 'Non-Authoritative Information'; break;
			case '204' : $type = 'No Content'; break;
			case '205' : $type = 'Reset Content'; break;
			case '206' : $type = 'Partial Content'; break;
			case '300' : $type = 'Multiple Choice'; break;
			case '301' : $type = 'Moved Permanently'; break;
			case '302' : $type = 'Found'; break;
			case '303' : $type = 'See Other'; break;
			case '304' : $type = 'Not Modified'; break;
			case '305' : $type = 'Use Proxy'; break;
			case '307' : $type = 'Temporary Redirect'; break;
			case '400' : $type = 'Bad Request'; break;
			case '401' : $type = 'Unauthorized'; break;
			case '402' : $type = 'Payment Required'; break;
			case '403' : $type = 'Forbidden'; break;
			case '404' : $type = 'Not Found'; break;
			case '405' : $type = 'Method Not Allowed'; break;
			case '406' : $type = 'Not Acceptable'; break;
			case '407' : $type = 'Proxy Authentication Required'; break;
			case '408' : $type = 'Request Timeout'; break;
			case '409' : $type = 'Conflict'; break;
			case '410' : $type = 'Gone'; break;
			case '411' : $type = 'Length Required'; break;
			case '412' : $type = 'Precondition Failed'; break;
			case '413' : $type = 'Payload Too Large'; break;
			case '414' : $type = 'URI Too Long'; break;
			case '415' : $type = 'Unsupported Media Type'; break;
			case '416' : $type = 'Range Not Satisfiable'; break;
			case '417' : $type = 'Expectation Failed'; break;
			case '500' : $type = 'Internal Server Error'; break;
			case '501' : $type = 'Not Implemented'; break;
			case '502' : $type = 'Bad Gateway'; break;
			case '503' : $type = 'Service Unavailable'; break;
			case '504' : $type = 'Gateway Timeout'; break;
			case '505' : $type = 'HTTP Version Not Supported'; break;
			case 'php'       : $error = 'Your host needs to use PHP ' . CMS_MINIMUM_PHP . ' or higher to run this version of isENGINE'; break;
			case 'blockip'   : $error = 'Blocking for ip is set, but blacklist or whitelist not found'; break;
			case 'update'    : $error = 'The site is undergoing technical work. Come back later'; break;
			case 'system'    : $error = 'One or more of system components not defined or not found'; break;
			case 'db_driver' : $error = 'Driver for database \'' . DB_TYPE . '\' not found'; break;
			case 'db_noset'  : $error = 'Database type not set on configuration file'; break;
			default :
				$code = '500';
				$type = 'Internal Server Error';
				break;
		}
		
		if (!empty($error)) {
			$code = '503';
			$type = 'Service Unavailable';
		}
		
		return [
			'code' => (int) $code,
			'status' => $type,
			'error' => $error
		];
		
	}

	static public function error($code, $refresh = true, $log = false){
		
		$list = self::errorlist($code);
		$error = &$list['error'];
		
		if (
			!empty($log) ||
			LOG_MODE === 'panic' ||
			LOG_MODE === 'warning' && !empty($error)
		) {
			if (!empty($error)) {
				$info = $error;
			} elseif ($code === '404') {
				$info = $code . ' from ' . str_replace(['/', '?', ':'], '_', $_SERVER['REQUEST_URI']);
			} else {
				$info = $code . ' ' . $list['status'];
			}
			self::log(self::set($log, true), $info);
			unset($info);
		}
		if (DEFAULT_MODE === 'develop') {
			header('Error-Code: ' . $code);
			header('Error-Reason: ' . (!empty($error) ? $error : $status));
		}
		
		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $list['code'] . ' ' . $list['status'], true, $list['code']);
		
		if (defined('isORIGIN') && isORIGIN) {
			if ($refresh) {
				self::reload(
					'/' . DEFAULT_ERRORS . '/' . $list['code'] . (!empty($error) ? '/' . $code : null) . '/',
					null,
					['Content-Type' => 'text/html; charset=UTF-8']
				);
				//header('Content-Type: text/html; charset=UTF-8');
				//header('Location: /' . DEFAULT_ERRORS . '/' . $list['code'] . (!empty($error) ? '/' . $code : null) . '/');
			} else {
				define('isERROR', $list['code'] . (!empty($error) ? ':' . $code : null));
				require_once PATH_TEMPLATES . DEFAULT_ERRORS . DS . 'template.php';
			}
		}
		
		exit;
		
	}

}

?>