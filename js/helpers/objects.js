
is.Helpers.Objects = class {
	
	static associate(item) {
		
		let system = is.Helpers.System;
		let result = null;
		
		if (system.typeData(item, 'object')) {
			for (let k in item) {
				if (!item.hasOwnProperty(k)) continue;
				if (system.type(k) !== 'numeric') {
					result = true;
					break;
				}
			}
		}
		
		return result;
		
	}

	static numeric(item) {
		
		let system = is.Helpers.System;
		let result = true;
		
		if (system.typeData(item, 'object')) {
			for (let k in item) {
				if (!item.hasOwnProperty(k)) continue;
				if (system.type(item[k]) !== 'numeric') {
					result = null;
					break;
				}
			}
		}
		
		return result;
		
	}

	static convert(item) {
		
		let type = is.Helpers.System.typeData(item);
		
		if (type === 'string') {
			item = is.Helpers.Parser.fromString(item);
		} else if (type === 'array') {
			item = { ...item };
		} else if (type === 'json') {
			item = is.Helpers.Parser.fromJson(item);
		} else if (!type) {
			item = { 0: item };
		}
		
		return item;
		
	}

	static match(haystack, needle) {
		
		let self = is.Helpers.Objects;
		let system = is.Helpers.System;
		
		if (system.type(haystack) === 'object') {
			haystack = self.values(haystack);
		}
		
		let result = 
			system.type(needle) === 'numeric'
			? haystack.indexOf( + needle) && haystack.indexOf('' + needle)
			: haystack.indexOf(needle);
		
		return !is.Helpers.System.set(result) || result === -1 ? null : true;
		
	}

	static find(haystack, needle, position = null) {
		
		let self = is.Helpers.Objects;
		let system = is.Helpers.System;
		
		let find = self.keys(self.filter(haystack, needle));
		let pos = system.set(position);
		
		if (pos && position !== 'r') {
			if (position < 0) {
				position = self.len(haystack) + position;
			}
			return self.match(find, position);
		} else if (position === 'r') {
			return system.set(self.last(find, 'value'), true, null);
		} else {
			return system.set(self.first(find, 'value'), true, null);
		}
		
	}

	static get(haystack, index, length = null, position = null) {
		
		let system = is.Helpers.System;
		
		let keys = Object.keys(haystack);
		let values = Object.values(haystack);
		let result = {};
		
		index = + index;
		if (index < 0) {
			index = keys.length + index;
		}
		
		if (length && !position) {
			length = + length;
			if (length < 0) {
				index += length + 1;
				length = index + Math.abs(length);
			} else {
				length = index + Math.abs(length);
			}
			
			keys = keys.slice(index, length);
			values = values.slice(index, length);
			
			//return haystack.substring(index, length);
			
		} else if (position) {
			keys = keys.slice(index, keys.length - length);
			values = values.slice(index, values.length - length);
			//return haystack.substring(0, index + 1);
		} else {
			keys = keys.slice(index);
			values = values.slice(index);
			//return haystack.substring(index);
		}
		
		keys.forEach((key, i) => result[key] = values[i]);
		return result;
		
	}

	static cut(haystack, index = -1, length = null, position = null) {
		
		let keys = Object.keys(haystack);
		let values = Object.values(haystack);
		
		let len = keys.length;
		
		if (!length) {
			length = position ? 0 : len;
		}
		
		let first = index < 0 ? len + index : index;
		let last = first + (length < 0 ? length + 1 : length);
		
		if (position) {
			last = len - Math.abs(length);
		}
		
		if (first > last) {
			let point = first + 1;
			first = last;
			last = point;
		}
		
		if (first < 0) {
			first = 0;
		} else if (first > len) {
			first = len;
		}
		if (last < 0) {
			last = 0;
		} else if (last > len) {
			last = len;
		}
		
		let o1keys = keys.slice(0, first);
		let o1values = values.slice(0, first);
		let o2keys = keys.slice(last);
		let o2values = values.slice(last);
		let result = {};
		
		o1keys.forEach((key, i) => result[key] = o1values[i]);
		o2keys.forEach((key, i) => result[key] = o2values[i]);
		
		return result;
		
	}

	static add(haystack, needle, recursive = null) {
		
		haystack = this.convert(haystack);
		needle = this.convert(needle);
		
		if (
			!this.associate(haystack) &&
			!this.associate(needle)
		) {
			haystack = Object.values(haystack);
			needle = Object.values(needle);
			let result = reverse ? needle.concat(haystack) : haystack.concat(needle);
			return this.convert(result);
		}
		
		// ПОМЕНЯЛОСЬ ПОВЕДЕНИЕ С РЕВЕРСА (КОТОРЫЙ НЕ НУЖЕН, т.к. можно просто поменять аргументы местами)
		// НА РЕКУРСИЮ, НО ЗДЕСЬ ОНА НЕ РЕАЛИЗОВАНА!!!
		return recursive ? Object.assign({}, needle, haystack) : Object.assign({}, haystack, needle);
		
	}

	static reverse(item) {
		
		// данная функция работает немного не так, как на php
		// отличие в том, что для массивов и неассоциативных объектов ключи сбрасываются
		
		let type = is.Helpers.System.type(item);
		
		item = this.convert(item);
		
		let keys = Object.keys(item).reverse();
		let values = Object.values(item).reverse();
		
		let result = {};
		
		if (type === 'array' || !this.associate(item)) {
			result = this.convert(values);
		} else {
			keys.forEach((key, i) => result[key] = values[i]);
		}
		
		//keys.forEach(function (i, k) {
		//	result[i] = values[k];
		//	console.log(i, k, values[k]);
		//});
		
		return result;
		
	}

	static first(item, result = null) {
		
		item = this.convert(item);
		
		let key = Object.keys(item)[0];
		let val = result !== 'key' ? item[key] : null;
		
		if (result === 'key') {
			return key;
		} else if (result === 'value') {
			return val;
		} else {
			return {'key': key, 'value': val};
		}
		
	}

	static last(item, result = null) {
		
		item = this.convert(item);
		
		let key = Object.keys(item).splice(-1)[0];
		let val = result !== 'key' ? item[key] : null;
		
		if (result === 'key') {
			return key;
		} else if (result === 'value') {
			return val;
		} else {
			return {'key': key, 'value': val};
		}
		
	}

	static n(item, n, result = null) {
		
		let self = is.Helpers.Objects;
		let r = self.get(item, n, 1);
		r = self.first(r);
		
		if (result === 'key') {
			return r['key'];
		} else if (result === 'value') {
			return r['value'];
		} else {
			return r;
		}
		
	}

	static refirst(item, data) {
		
		let self = is.Helpers.Objects;
		let key = self.first(item, 'key');
		
		item[key] = data;
		
	}

	static relast(item, data) {
		
		let self = is.Helpers.Objects;
		let key = self.last(item, 'key');
		
		item[key] = data;
		
	}

	static ren(item, i, data) {
		
		let self = is.Helpers.Objects;
		let key = self.n(item, i, 'key');
		
		item[key] = data;
		
	}

	static unfirst(item) {
		
		let keys = Object.keys(item);
		let values = Object.values(item);

		keys.shift();
		values.shift();
		
		let result = {};
		
		keys.forEach((key, i) => result[key] = values[i]);
		
		return result;
		
	}

	static unlast(item) {
		
		let keys = Object.keys(item);
		let values = Object.values(item);
		
		keys.pop();
		values.pop();
		
		let result = {};
		
		keys.forEach((key, i) => result[key] = values[i]);
		
		return result;
		
	}

	static unn(item, n, result = null) {
		
		let self = is.Helpers.Objects;
		let r = self.cut(item, n, 1);
		
		if (result === 'key') {
			return self.keys(r);
		} else if (result === 'value') {
			return self.values(r);
		} else {
			return r;
		}
		
	}

	static len(item) {
		
		let system = is.Helpers.System;
		
		return system.typeData(item, 'object') ? Object.keys(item).length : Object.keys(this.convert(item)).length;
		
	}

	static levels(item, max = null) {
		
		let system = is.Helpers.System;
		let self = is.Helpers.Objects;
		let n = 0;
		
		if ( system.typeOf(item, 'iterable') ) {
			for (let k in item) {
				let i = item[k];
				if ( system.typeOf(i, 'iterable') ) {
					let c = self.levels(i, max);
					n = (n > c) ? n : c;
				}
				if (max && n + 1 >= max) {
					break;
				}
			}
			n = max && n >= max ? max : n + 1;
		}
		
		return n;
		
	}

	static keys(item) {
		
		return Object.keys(item);
		
	}

	static values(item) {
		
		return Object.values(item);
		
	}

	static combine(values, keys = null) {
		
		let self = is.Helpers.Objects;
		let system = is.Helpers.System;
		
		keys = self.convert(keys);
		keys = Object.values(keys);
		
		values = self.convert(values);
		values = Object.values(values);
		
		if (system.type(keys) !== 'array' || !keys.length) {
			return self.convert(values);
		}
		
		let lkeys = keys.length;
		let lvalues = values.length;
		
		if (lkeys > lvalues) {
			// СТАРОЕ ПОВЕДЕНИЕ
			keys = keys.slice(0, lkeys - 1);
			// НУЖНО НОВОЕ ПОВЕДЕНИЕ
			// итоговый массив создается по длине массива ключей
			// дополняясь элементами default
			//keys = keys.slice(0, lkeys - 1);
		} else if (lvalues > lkeys) {
			values = values.slice(0, lvalues - 1);
		}
		
		let result = {};
		
		keys.forEach((key, i) => result[key] = values[i]);
		
		return result;
		
	}

	static merge(item, merge, recursion = null) {
		
		let system = is.Helpers.System;
		
		if (System.typeData(merge, 'object')) {
			let keys = Object.keys(merge);
			let values = Object.values(merge);
			
			for (let k in keys) {
				let i = keys[k];
				item[i] = values[k];
				// if recursion ...
			}
		}
		
		return $item;
		
	}

	static each(item, parameters = null, callback = null) {
		
		let system = is.Helpers.System;
		let type = system.typeOf(parameters);
		
		if (type === 'iterable') {
			
			for (let k in item) {
				if (!item.hasOwnProperty(k)) continue;
				if (system.type(k) === 'numeric') { k = + k; }
				//if (!isNaN(k)) { k = + k; }
				callback.call(k, item[k], k, parameters);
			}
			
			return parameters;
			
		} else if (!type) {
			
			for (let k in item) {
				if (!item.hasOwnProperty(k)) continue;
				if (system.type(k) === 'numeric') { k = + k; }
				//if (!isNaN(k)) { k = + k; }
				item[k] = callback.call(k, item[k], k);
			}
			
		} else {
			
			for (let k in item) {
				if (!item.hasOwnProperty(k)) continue;
				if (system.type(k) === 'numeric') { k = + k; }
				//if (!isNaN(k)) { k = + k; }
				let i = callback.call(k, item[k], k);
				if (i === false) {
					if (parameters === 'filter') {
						delete item[k];
					} else if (parameters === 'break') {
						break; // or return item;
					} else if (parameters === 'continue') {
						continue; // or nothing
					}
				} else {
					item[k] = i;
				}
			}
			
		}
		
		return item;
		
	}

	static clear(item) {
		
		let system = is.Helpers.System;
		var self = is.Helpers.Objects;
		
		if (!system.typeOf(item, 'iterable')) {
			return item;
		}
		
		item = self.each(item, 'filter', function(i) {
			if (system.typeOf(i, 'iterable')) {
				i = self.clear(i);
			}
			return !system.set(i) ? false : i;
		});
		
		return item;
		
	}

	static unique(item) {
		
		let system = is.Helpers.System;
		var self = is.Helpers.Objects;
		
		let arr = new Array();
		let len = item.length;
		
		for (let i = 0; i < len; i++) {
			if (arr.indexOf(item[i]) == "-1") {
				arr.push(item[i]);
			}
		}
		
		return arr;
		
	}

	static filter(haystack, needle = null, notneedle = null) {
		
		let find = {};
		
		if (!needle && !notneedle) {
			return haystack;
		}
		
		for (let k in haystack) {
			let i = haystack[k];
			if (needle && i === needle) {
				find[k] = i;
			} else if (notneedle && i !== notneedle) {
				find[k] = i;
			}
		}
		
		return find;
		
	}

	static sort(haystack, reverse = false, keys = false) {
		
		let self = is.Helpers.Objects;
		let akeys = self.keys(haystack);
		let avalues = self.values(haystack);
		
		let associate = self.associate(haystack);
		let numeric = keys ? !associate : self.numeric(haystack);
		
		let array = keys ? akeys : avalues;
		
		if (numeric) {
			array.sort((a, b) => a - b);
		} else {
			let collator = new Intl.Collator(undefined, {numeric: true, sensitivity: 'base'});
			array.sort(collator.compare);
		}
		
		if (reverse) {
			array = self.values(self.reverse(array));
		}
		
		let result;
		if (associate) {
			result = {};
			if (keys) {
				for (let k in array) {
					let i = array[k];
					result[i] = haystack[i];
				}
			} else {
				let copy = Object.assign({}, haystack);
				for (let k in array) {
					let i = array[k];
					let x = self.find(copy, i, reverse ? 'r' : null);
					result[x] = i;
					delete copy[x];
				}
			}
			
		} else {
			result = {};
			for (let k in array) {
				let i = array[k];
				if (keys) {
					result[k] = haystack[i];
				} else {
					result[k] = i;
				}
			}
		}
		
		return result;
		
	}

	static randomize(haystack) {
		
		let self = is.Helpers.Objects;
		let associate = self.associate(haystack);
		let result = {};
		
		if (associate) {
			
			let keys = self.keys(haystack);
			keys.sort(() => Math.random() - 0.5);
			for (let k in keys) {
				let i = keys[k];
				result[i] = haystack[i];
			}
			
		} else {
			
			let values = self.values(haystack);
			values.sort(() => Math.random() - 0.5);
			for (let k in values) {
				let i = values[k];
				result[k] = i;
			}
			
		}
		
		return result;
		
	}

	static difference(haystack, needle) {
		
		var a = [], diff = [];
		
		for (var i = 0; i < haystack.length; i++) {
			a[haystack[i]] = true;
		}
		
		for (var i = 0; i < needle.length; i++) {
			if (a[needle[i]]) {
				delete a[needle[i]];
			} else {
				a[needle[i]] = true;
			}
		}
		
		for (var k in a) {
			diff.push(k);
		}
		
		return { ...diff };
		
		//console.log(arr_diff(['a', 'b'], ['a', 'b', 'c', 'd']));
		//console.log(arr_diff("abcd", "abcde"));
		//console.log(arr_diff("zxc", "zxc"));
		
	}

	static level(haystack, needle, value = null) {
		// НОВАЯ ФУНКЦИЯ !!!!
	}
	
	static extract(haystack, needle) {
		// НОВАЯ ФУНКЦИЯ !!!!
	}
	
	static array_simple(item) {
		
		let system = is.Helpers.System;
		var self = is.Helpers.Objects;
		
		if (!system.typeOf(item, 'iterable')) {
			return item;
		}
		
		self.each(item, false, function(i) {
			if (system.typeOf(i, 'iterable')) {
				let v = Object.values(i);
				if (v.length === 1) {
					i = v[0];
				}
			}
			return i;
		});
		
		return item;
		
	}

}
