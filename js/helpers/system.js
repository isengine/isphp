
is.Helpers.System = class {

	static print(item = null) {
		console.log(item === null ? 'null' : item);
	}

	static console(item = null, title = null) {
		if (title) {
			console.log('// ' + title);
		}
		console.log(item === null ? 'null' : item);
	}

	static include(src) {
		
		src = src.replace(/(\.\.)|(\/)|(\\)|(\.)+/g, '');
		src = src.replaceAll(':', '/');
		
		var s = document.createElement('script');
		s.src = 'http://' + window.location.host + '/vendor/isengine/framework/js/' + src + '.js';
		s.async = false;
		s.type = 'text/javascript';
		s.onload = arguments[1] || null;
		s.onreadystatechange = function() {
			if (this.readyState == 'complete' && typeof(this.onload) == 'function') {
				this.onload();
			}
		};
		
		document.getElementsByTagName('head')[0].appendChild(s);
		return s;
		
	}

	static isset(item = null) {
		return typeof(item) != "undefined" && item !== null;
	}

	static set(item = null, yes = null, no = '') {
		
		// здесь, как и в php, не проходят проверку строки с пробелами и переносами
		// возможно, нужно добавить больше опций на более точные проверки
		
		if (yes) {
			return this.set(item) ? (yes === true ? item : yes) : no;
		}
		
		let type = typeof item;
		
		if (
			type === 'undefined' ||
			item === false ||
			item === null ||
			item === '' ||
			Number.isNaN(item)
		) {
			return null;
		} else if (
			type === 'boolean' &&
			item === true
		) {
			return true;
		} else if (
			type === 'object' || type === 'array'
		) {
			if (type === 'object') {
				item = Object.values(item);
			}
			if (
				Array.isArray(item) &&
				item.length !== 0
			) {
				// здесь, в отличие от php, нет рекурсии
				// на проверку вложенных массивов или объектов
				// больше, чем на 1 уровень
				
				return item.filter(function(e){ return e === 0 || e }).length > 0 ? true : null;
				
			} else {
				return null;
			}
		} else if (
			type === 'string' &&
			(item.indexOf(' ') > -1 || item.indexOf('	') > -1)
		) {
			return item.replace(/[\s]+/g, '') ? true : null;
		}
		
		return true;
		
	}

	static type(item = null, compare = null) {
		
		let type = null;
		let data = null;
		let set = this.set(item);
		
		if (Array.isArray(item)) {
			type = 'array';
		} else if (typeof item === 'object' && item) {
			type = 'object';
		} else if (!set) {
			type = null;
		} else if (Number.isNaN(item)) {
			type = null;
		} else if (typeof item === 'boolean') {
			type = 'true';
		} else if (typeof item === 'string') {
			
			item = item.replace(/\s/g, '');
			item = item.replace(/\,/g, '.');
			set = this.set(item);
			
			if (!set) {
				type = null;
			} else if (!isNaN(item)) {
				type = 'numeric';
			} else {
				type = typeof item;
			}
			
		} else if (!isNaN(item)) {
			type = 'numeric';
		} else {
			type = typeof item;
		}
		
		if (compare) {
			return compare === type ? true : null;
		}
		
		return type;
		
	}

	static typeOf(item = null, compare = null) {
		
		let set = this.set(item);
		let type = null;
		let itype = typeof item;
		
		if (itype === 'string' || itype === 'numeric') {
			type = 'scalar';
		} else if (itype === 'array' || itype === 'object') {
			type = 'iterable';
		} else if (!set || item === true) {
			return null;
		}
		
		if (compare) {
			return compare === type ? true : null;
		}
		
		return type;
		
	}

	static typeData(item = null, compare = null) {
		
		// Внимание! Здесь намеренное различие типов с версией для php
		
		// Objects.is(item) => System.typeData(item, 'object')
		
		let type = this.type(item);
		let result = null;
		
		if (type === 'string') {
			let first = item[0];
			let last = item.substring(item.length - 1);
			if (
				(first === '{' && last === '}') ||
				(first === '[' && last === ']')
			) {
				result = 'json';
			} else if (item.indexOf(':') > -1 || item.indexOf('|') > -1) {
				result = 'string';
			}
		} else if (type === 'array') {
			
			result = 'array';
			
		} else if (type === 'object') {
			
			let fl = Object.getOwnPropertyNames(item).filter((key) => typeof item[key] === 'function').length;
			let kl = Object.keys(item).length;
			
			if (fl == 0 && kl > 0) {
				result = 'object';
			}
			
		}
		
		if (compare) {
			return compare === type ? true : null;
		}
		
		return type;
		
	}

	static refresh(path = '/', code = null, data = null) {
		
		/*
		
		в js нельзя оперировать серверными данными так же как и в php
		поэтому данная возможность функции разрабатывается
		
		var request = new XMLHttpRequest();
		request.open('GET', document.location, false);
		request.setRequestHeader('Accept', 'text/plain');
		request.setRequestHeader('Content-Type', 'text/plain'); 
		request.setRequestHeader('Content-Language', 'en-US');
		request.send(null); 
		request.getAllResponseHeaders().toLowerCase();
		
		if (!empty($data) && is_array($data)) {
			foreach ($data as $key => $item) {
				header($key . ': ' . $item);
			}
			unset($key, $item);
		}
		
		if (!empty($code)) {
			$list = self::errorlist($code);
			header($_SERVER['SERVER_PROTOCOL'] . ' ' . $list['code'] . ' ' . $list['status'], true, $list['code']);
		}
		
		*/
		
		Location.replace(path);
		
	}

	static code(code = 200) {
		
		/*
		здесь должно быть соответствие кодов, как в php
		*/
		
		//Location.replace(path);
		
	}

}
