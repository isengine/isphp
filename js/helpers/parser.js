
is.Helpers.Parser = class {

	static fromString(item = null, parameters = {'key': null, 'clear': null, 'simple': null}) {
		
		var system = is.Helpers.System;
		var strings = is.Helpers.Strings;
		var self = is.Helpers.Objects;
		var key = system.set(parameters['key']);
		
		if (system.type(item) !== 'string') {
			return item;
		} else if (
			item.indexOf(':') < 0 &&
			item.indexOf('|') < 0
		) {
			if (item.indexOf('!') === 0) {
				return null;
			} else {
				return key ? {item : null} : {0: item};
			}
		} else {
			
			var split = strings.split(item, '|', true);
			
			// сразу же делаем проверку и разбиваем два действия по условию,
			// чтобы не проверять потом при каждой итерации
			
			if (key) {
				
				// это код для разбивки массива с ключами
				split = self.each(split, {}, function(i, k, r) {
					if (!system.set(i)) {
						return null;
					} else if (i.indexOf(':') < 0) {
						r[i] = null;
					} else {
						
						let spliti = strings.split(i, ':', null);
						
						let splitk = spliti[0];
						delete spliti[0];
						
						if (splitk.indexOf('!') === 0) {
							return null;
						} if (!system.set(spliti)) {
							r[splitk] = null;
						} else {
							r[splitk] = self.each(spliti, {}, function(i, k, a) {
								//console.log(parameters);
								// этот код вместо вызова array_clear
								// не пробегает лишний раз по массиву и экономит ресурсы
								let result = i.indexOf('!') !== 0 ? system.set(i, true) : null;
								if (system.type(result) === 'numeric') { result = +result; } // сразу приведение типов
								if (!parameters['clear'] || parameters['clear'] && result) {
									a[k - 1] = result;
								}
							});
							
							// этот код вместо вызова array_simple
							// не пробегает лишний раз по массиву и экономит ресурсы
							if (parameters['simple']) {
								let v = Object.values(r[splitk]);
								if (v.length === 1) {
									r[splitk] = v[0];
								}
							}
						}
						
						return null;
						
					}
				});
				
			} else {
				
				// это код для разбивки массива без ключей
				split = self.each(split, false, function(i) {
					if (!system.set(i)) {
						return null;
					} else if (i.indexOf(':') < 0) {
						if (system.type(i) === 'numeric') { i = +i; } // сразу приведение типов
						//return {0: i};
						return parameters['simple'] ? $i : {0: i};
					} else {
						
						let spliti = strings.split(i, ':', null);
						
						let a = self.each(spliti, {}, function(i, k, a) {
							//console.log(parameters);
							// этот код вместо вызова array_clear
							// не пробегает лишний раз по массиву и экономит ресурсы
							let result = i.indexOf('!') !== 0 ? system.set(i, true) : null;
							if (system.type(result) === 'numeric') { result = +result; } // сразу приведение типов
							if (!parameters['clear'] || parameters['clear'] && result) {
								a[k] = result;
							}
						});
						
						// этот код вместо вызова array_simple
						// не пробегает лишний раз по массиву и экономит ресурсы
						if (parameters['simple']) {
							let v = Object.values(a);
							if (v.length === 1) {
								a = v[0];
							}
						}
						
						return a;
						
					}
				});
				
				if (parameters['simple']) {
					let v = Object.values(split);
					if (v.length === 1) {
						split = self.first(split, 'value');
					}
				}
				
			}
			
			// этот код выключен, т.к. он оказался слишком неэкономный по ресурсам
			//if (parameters['clear']) {
			//	split = self.array_clear(split);
			//}
			//if (parameters['simple']) {
			//	split = self.array_simple(split);
			//}
			
			return split;
			
		}
	}

	static toString(item, parameters = {'key': null, 'clear': null, 'simple': null}) {
		
		let system = is.Helpers.System;
		let self = is.Helpers.Objects;
		
		item = self.convert(item);
		
		let key = parameters['key'] ? true : null;
		let clear = parameters['clear'] ? true : null;
		let levels = self.levels(item, 2);
		let str = '';
		
		let first = self.first(item);
		item = self.unfirst(item);
		
		if (levels === 1) {
			
			if (clear) {
				str += key ? first['key'] + system.set(first['value'], ':') : '';
				str += system.set(first['value'], true);
			} else {
				str += key ? first['key'] + ':' : '';
				str += first['value'];
			}
			
			if (system.typeData(item, 'object')) {
				for (let k in item) {
					let i = item[k];
					str += key ? '|' + k : '';
					str += clear ? system.set(i, ':' + i) : ':' + i;
				}
			}
			
		} else {
			
			parameters['key'] = null;
			
			str += key ? first['key'] + ':' : '';
			str += this.toString(first['value'], parameters);
			
			if (system.typeData(item, 'object')) {
				for (let k in item) {
					let i = item[k];
					str += '|' + (key ? k + (clear ? system.set(i, ':') : ':') : '');
					str += this.toString(i, parameters);
				}
			}
			
		}
		
		return str;
		
	}

	static fromJson(item, format = true) {
		return JSON.parse(item);
	}

	static toJson(item, format = null) {
		return JSON.stringify(item, false, format ? '\t' : null);
	}

	// НУЖНО ДОБАВИТЬ ФУНКЦИЮ prepare

	static prepare(data, up = null) {
		
		for (let key in data) {
			let item = data[key];
			//...
		}
		
		/*
			if (
				mb_strpos($key, '!') === 0 ||
				!is_array($item) && strpos($item, '!') === 0
			) {
				unset($data[$key]);
			} elseif (!empty($up) && is_array($up) && in_array($key, $up)) {
				$data = Objects::merge($data, $data[$key]);
				unset($data[$key]);
			} elseif (is_array($item)) {
				$item = self::prepare($item);
			}
		*/
		
		return data;
		
	}

}
