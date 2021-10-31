
is.Helpers.Sessions = class {

	static setCookie(name, set) {
		document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + "; path=/";
	}

	static getCookie(name = null){
		let array = {};
		document.cookie.split(";").forEach(function(item){
			let key;
			item.split("=").forEach(function(i, k){
				i = decodeURIComponent(i.trim());
				if (k === 0) {
					key = i;
				} else if (
					(name && key === name) ||
					!name
				) {
					array[key] = i;
				}
			});
		});
		return name ? array[name] : array;
	}

	static unCookie(name) {
		document.cookie = encodeURIComponent(name) + "=; max-age=0";
	}

}
