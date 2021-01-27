
is.Helpers.Strings = class {

	static match(haystack, needle) {
		
		return haystack.indexOf(needle) === -1 ? null : true;
		
	}

	static find(haystack, needle, position = null) {
		
		let system = is.Helpers.System;
		let pos = system.set(position);
		
		if (pos && position !== 'r') {
			let len = needle.length;
			position = + position;
			if (position < 0) {
				position = haystack.length + position;
			}
			let result = haystack.substring(position, position + len);
			return result === needle ? true : false;
		} else if (position === 'r') {
			return haystack.lastIndexOf(needle);
		} else {
			return haystack.indexOf(needle);
		}
		
	}

	static get(haystack, index, length = null, position = null) {
		
		let system = is.Helpers.System;
		
		index = + index;
		if (index < 0) {
			index = haystack.length + index;
		}
		
		if (length && !position) {
			length = + length;
			if (length < 0) {
				index += length + 1;
				length = index + Math.abs(length);
			} else {
				length = index + Math.abs(length);
			}
			return haystack.substring(index, length);
		} else if (length && position) {
			return haystack.substring(index, haystack.length - length);
		} else {
			return haystack.substring(index);
		}
		
	}

	static cut(haystack, index = -1, length = null, position = null) {
		
		let len = haystack.length;
		
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
		
		return haystack.substring(0, first) + haystack.substring(last);
		
	}

	static add(haystack, needle, reverse = null) {
		
		return reverse ? '' + needle + haystack : '' + haystack + needle;
		
	}

	static reverse(item) {
		
		return item.split("").reverse().join("");
		
	}

	static first(item) {
		
		return item[0];
		//return item.substring(0, 1);
		
	}

	static last(item) {
		
		return item.substring(item.length - 1);
		
	}

	static len(item) {
		
		let system = is.Helpers.System;
		let type = system.type(item);
		
		return type && type !== 'array' && type !== 'object' ? item.length : null;
		
	}

	static split(item = null, splitter = '\s,;', clear = null) {
		
		let system = is.Helpers.System;
		var data = is.Helpers.Objects;
		
		if (system.type(item) !== 'string') {
			return null;
		} else if (system.type(splitter) !== 'string') {
			return item;
		}
		
		item = item.split(new RegExp('[' + splitter + ']', 'u'));
		
		item = { ...item };
		
		if (system.set(clear)) {
			item = data.clear(item);
		}
		
		return item;
		
	}

	static join(item, splitter = ' ') {
		
		let type = is.Helpers.System.type(item);
		
		if (type !== 'array' && type !== 'object') {
			return item;
		}
		
		if (type === 'object') {
			item = Object.values(item);
		}
		
		return item.join(splitter);
		
	}

	static replace(item, search, replace) {
		
		let system = is.Helpers.System;
		
		if (system.type(search) === 'string') {
			return item.replaceAll(search, replace);
		} else if (system.type(search) === 'array') {
			let t = system.type(replace) === 'array';
			search.forEach(function (i, k) {
				item = item.replaceAll(i, t ? replace[k] : replace);
			});
			return item;
		}
		
	}

	static clear(item) {
		
		return item.replace(/(\s|\r|\n|\r\n)+/g, '');
		
	}

	static unique(item) {
		
		item = item.split(new RegExp('', 'u'));
		
		let str = '';
		let len = item.length;
		
		for (let i = 0; i < len; i++) {
			if (!this.match(str, item[i])) {
				str += item[i];
			}
		}
		
		return str;
		
	}

	static sort(haystack, reverse = false, register = true) {
		
		let objects = is.Helpers.Objects;
		let str = '';
		
		haystack = haystack.split(new RegExp('', 'u'));
		
		if (register) {
			haystack.sort();
		} else {
			let collator = new Intl.Collator(undefined, {numeric: true, sensitivity: 'base'});
			haystack.sort(collator.compare);
		}
		
		if (reverse) {
			haystack = objects.values(objects.reverse(haystack));
		}
		
		for (let k in haystack) {
			let i = haystack[k];
			str += i;
		}
		
		return str;
		
	}

	static difference(haystack, needle) {
		
		let a = [], diff = '';
		
		for (let i = 0; i < haystack.length; i++) {
			a[haystack[i]] = true;
		}
		
		for (let i = 0; i < needle.length; i++) {
			if (a[needle[i]]) {
				delete a[needle[i]];
			} else {
				a[needle[i]] = true;
			}
		}
		
		for (let k in a) {
			diff += k;
		}
		
		return diff;
		
	}

}
