<?php
namespace is\Helpers;

class Sessions {

	static public function cookie($name, $set = false){
		
		/*
		*  Обобщенная функция регистрации/удаления куки
		*  на входе нужно указать имя куки
		*  второй необязательный параметр служит триггером, меняющим поведение функции
		*  
		*  false или не указан - стереть куки, если вместо имени передан массив, будут удалены все указанные в нем куки
		*  true - проверка значения, если задано то возвращает его, если не задано, возвращает false
		*  если указано любое, кроме false и true - присвоить это значение куки
		*  
		*  Функция удобна тем, что сразу присваивает значение куки, без необходимости перезагружать страницу,
		*  а также выполняет все необходимые проверки
		*/
		
		if (is_array($name)) {
			// un
			foreach ($name as $item) {
				setcookie($item, '', time() - 3600, '/');
				unset($_COOKIE[$item]);
			}
			unset($item);
		} elseif ($set === false) {
			// un
			setcookie($name, '', time() - 3600, '/');
			unset($_COOKIE[$name]);
		} elseif ($set === true) {
			// get
			return $name === true ? $_COOKIE : (!empty($_COOKIE[$name]) ? $_COOKIE[$name] : null);
		} else {
			// set
			setcookie($name, $set, 0, '/');
			$_COOKIE[$name] = $set;
		}
		
	}

	static public function setCookie($name, $set){
		
		/*
		*  Функция регистрации куки
		*  на входе нужно указать имя куки
		*  второе значение - присвоить это значение куки
		*/
		
		setcookie($name, $set, 0, '/');
		$_COOKIE[$name] = $set;
		
	}

	static public function getCookie($name = null){
		
		/*
		*  Функция проверки куки
		*  если задано то возвращает значение, если не задано, возвращает null
		*  если ключ пустой, то возвращает весь массив кук
		*/
		
		return !$name ? $_COOKIE : (!empty($_COOKIE[$name]) ? $_COOKIE[$name] : null);
		
	}
	
	static public function unCookie($name){
		
		/*
		*  Функция удаления куки
		*  на входе нужно указать имя куки
		*  если вместо имени передан массив, будут удалены все указанные в нем куки
		*/
		
		if (is_array($name)) {
			foreach ($name as $item) {
				setcookie($item, '', time() - 3600, '/');
				unset($_COOKIE[$item]);
			}
			unset($item);
		} else {
			setcookie($name, '', time() - 3600, '/');
			unset($_COOKIE[$name]);
		}
		
	}

}

?>