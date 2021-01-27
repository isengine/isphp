<?php

namespace is\Helpers;

class Log {

	static public function log($data = null, $name = null) {
		
		/* ФУНКЦИЯ ЛОГИРОВАНИЯ СОБЫТИЙ */
		
		$remote_addr = str_replace('.', '-', $_SERVER['REMOTE_ADDR']);
		$request_time = $_SERVER['REQUEST_TIME'];
		$memory_usage = memory_get_peak_usage() / 1024;
		$microtime = microtime();
		$microtime = substr($microtime, strpos($microtime, ' ') + 1) . substr($microtime, 1, 4);
		$request_microtime = !empty($_SERVER['REQUEST_TIME_FLOAT']) && !empty($microtime) ? number_format($microtime - $_SERVER['REQUEST_TIME_FLOAT'], 3, null, null) : null;
		
		$folder = LOG_MODE . '_by_' . LOG_SORT . DS;
		
		if (!file_exists(PATH_LOG . htmlentities($folder))) {
			mkdir(PATH_LOG . htmlentities($folder));
		}
		
		if (!$name || LOG_MODE === 'panic') {
			if (LOG_MODE === 'warning') {
				$name = htmlentities($data);
			} else {
				$name = str_replace('.', '-', $microtime) . '_' . $remote_addr . '_' . mt_rand();
			}
		}
		
		if (
			LOG_MODE === 'panic' ||
			LOG_MODE === 'warning'
		) {
			
			$data = '{' . "\r\n" . '"information":"' . htmlentities($data) . '",';
			
			foreach ([
				'request' => 'REQUEST_URI',
				'method' => 'REQUEST_METHOD',
				'port' => 'REMOTE_PORT',
				'ip' => 'REMOTE_ADDR',
				'protocol' => 'SERVER_PROTOCOL',
				'referrer' => 'HTTP_REFERER'
			] as $k => $i) {
				if (LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, $k) !== false)) {
					$data .= "\r\n" . '"' . $k . '" : "' . (!empty($_SERVER[$i]) ? htmlentities($_SERVER[$i]) : null) . '",';
				}
			}
			unset($k, $i);
			
			global $uri;
			global $user;
			
			$data .=
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'agent') !== false) ? "\r\n" . '"agent" : "' . htmlentities(str_replace(['\\', '/'], '-', USER_AGENT)) . '",' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'speed') !== false) ? "\r\n" . '"speed" : "' . htmlentities(!empty($request_microtime) ? $request_microtime : time() - $request_time) . ' sec",' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'date') !== false) ? "\r\n" . '"date" : "' . htmlentities(date('Y-m-d H:i:s', $request_time)) . '",' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'time') !== false) ? "\r\n" . '"time" : "' . $microtime . '",' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'memory') !== false) ? "\r\n" . '"memory" : "' . number_format($memory_usage, 3, null, ' ') . ' Kb",' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'defines') !== false) ? "\r\n" . '"defines" : "isREQUEST' . (defined('isREQUEST') && isREQUEST ? '+' : '-') . ' isORIGIN' . (defined('isORIGIN') && isORIGIN ? '+' : '-') . ' isALLOW' . (defined('isALLOW') && isALLOW ? '+' : '-') . '",' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'session') !== false) ? "\r\n" . '"session" : ' . json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ',' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'cookies') !== false) ? "\r\n" . '"cookies" : ' . json_encode($_COOKIE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ',' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'get') !== false) ? "\r\n" . '"get" : ' . json_encode($_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ',' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'post') !== false) ? "\r\n" . '"post" : ' . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ',' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'uri') !== false) ? "\r\n" . '"uri" : ' . json_encode($uri, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ',' : null) .
					(LOG_DATA && (LOG_DATA === true || strpos(LOG_DATA, 'user') !== false) ? "\r\n" . '"user" : ' . json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ',' : null)
			;
			
			if (mb_substr($data, -1) === ',') {
				$data = mb_substr($data, 0, -1);
			}
			
			$data .= "\r\n" . '}';
			
		} elseif (LOG_MODE === 'server') {
			$data = json_encode($_SERVER, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		} else {
			$data = htmlentities($data);
		}
		
		if (LOG_SORT) {
			switch (LOG_SORT) {
				case 'ip'      : $folder .= $remote_addr; break;
				case 'agent'   : $folder .= str_replace(['\\', '/'], '-', USER_AGENT); break;
				case 'request' : $folder .= (defined('isREQUEST') && isREQUEST ? 'good_request' : 'bad_request'); break;
				case 'origin'  : $folder .= (defined('isORIGIN') && isORIGIN ? 'good_origin' : 'bad_origin'); break;
				case 'speed'   : $folder .= round(time() - $request_time); break;
				case 'time'    : $folder .= $request_time; break;
				case 'minute'  : $folder .= floor($request_time / TIME_MINUTE) * TIME_MINUTE; break;
				case 'hour'    : $folder .= floor($request_time / TIME_HOUR) * TIME_HOUR; break;
				case 'day'     : $folder .= floor($request_time / TIME_DAY) * TIME_DAY; break;
				case 'week'    : $folder .= floor($request_time / TIME_WEEK) * TIME_WEEK; break;
				case 'month'   : $folder .= floor($request_time / TIME_MONTH) * TIME_MONTH; break;
				case 'memory'  :
					$m = round($memory_usage);
					$folder .= (strlen($m) <= 3) ? substr($m, 0, 1) . '00K' : floor($m / 1000) . 'M';
					unset($m);
					break;
				case 'name'    :
					$folder .= LOG_MODE === 'warning' ? $name : $remote_addr;
					if (LOG_MODE === 'warning') { $name = $remote_addr; }
					break;
				default :
					$folder .= LOG_SORT;
					break;
			}
			$folder .= DS;
		}
		
		if (!file_exists(PATH_LOG . htmlentities($folder))) {
			mkdir(PATH_LOG . htmlentities($folder));
		}
		
		if (LOG_MODE === 'warning' && file_exists(PATH_LOG . htmlentities($folder) . htmlentities($name) . '.ini')) {
			unlink(PATH_LOG . htmlentities($folder) . htmlentities($name) . '.ini');
		}
		
		file_put_contents(PATH_LOG . htmlentities($folder) . htmlentities($name) . '.ini', $data);
		
	}

}

?>