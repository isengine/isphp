
is.Helpers.Match = class {

	static equal(haystack, needle) {
		return String(haystack) === String(needle) ? true : null;
	}

	static string(haystack, needle) {
		return is.Helpers.Strings.match(haystack, needle);
	}

	static numeric(haystack, min = null, max = null) {
		
		haystack = Number(haystack);
		// ЗАМЕНИТЬ НА МЕТОД ПРЕОБРАЗОВАНИЯ ЛЮБОЙ СТРОКИ В ЧИСЛО
		
		min = is.Helpers.System.set(min) ? Number(min) : false;
		max = is.Helpers.System.set(max) ? Number(max) : false;
		
		let rmin = min === false ? true : haystack >= min;
		let rmax = max === false ? true : haystack <= max;
		
		return rmin && rmax ? true : null;
		
	}

	static equalIn(haystack, needle, and = true) {
		
		let self = is.Helpers.Match;
		let result;
		
		for (let k in haystack) {
			let item = haystack[k];
			result = self.equal(item, needle);
			if ( (and && !result) || (!and && result) ) {
				break;
			}
		}
		
		return result;
		
	}

	static stringIn(haystack, needle, and = true) {
		
		let self = is.Helpers.Match;
		let result;
		
		for (let k in haystack) {
			let item = haystack[k];
			result = self.string(item, needle);
			if ( (and && !result) || (!and && result) ) {
				break;
			}
		}
		
		return result;
		
	}

	static numericIn(haystack, min = null, max = null, and = true) {
		
		let self = is.Helpers.Match;
		let result;
		
		for (let k in haystack) {
			let item = haystack[k];
			result = self.numeric(item, min, max);
			if ( (and && !result) || (!and && result) ) {
				break;
			}
		}
		
		return result;
		
	}

	static equalOf(haystack, needle, and = true) {
		
		let self = is.Helpers.Match;
		let result;
		
		for (let k in needle) {
			let item = needle[k];
			result = self.equal(haystack, item);
			if ( (and && !result) || (!and && result) ) {
				break;
			}
		}
		
		return result;
		
	}

	static stringOf(haystack, needle, and = true) {
		
		let self = is.Helpers.Match;
		let result;
		
		for (let k in needle) {
			let item = needle[k];
			result = self.string(haystack, item);
			if ( (and && !result) || (!and && result) ) {
				break;
			}
		}
		
		return result;
		
	}

	static numericOf(haystack, minmax, and = true) {
		
		let self = is.Helpers.Match;
		let objects = is.Helpers.Objects;
		let result;
		
		for (let k in minmax) {
			let item = minmax[k];
			result = self.numeric(haystack, objects.first(item, 'value'), objects.last(item, 'value'));
			if ( (and && !result) || (!and && result) ) {
				break;
			}
		}
		
		return result;
		
	}

	static common(name, data) {
		
		let fname = is.Helpers.Match[name];
		return fname.call(false, data[0], data[1], data[2], data[3]);
		
	}

}
