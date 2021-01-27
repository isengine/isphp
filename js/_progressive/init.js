/*
*  это не работает так, как нужно
*  то грузится, то нет
*/

var is = {};
is.Helpers = {};

is.Init = class {
	static include(src) {
		
		src = src.replace(/(\.\.)|(\/)|(\\)|(\.)+/g, '');
		src = src.replaceAll(':', '/');
		
		var s = document.createElement('script');
		//s.defer = true;
		s.src = '//' + window.location.host + '/vendor/isengine/framework/js/' + src + '.js';
		s.async = false;
		//s.type = 'text/javascript';
		s.onload = arguments[1] || null;
		s.onreadystatechange = function() {
			if (this.readyState == 'complete' && typeof(this.onload) == 'function') {
				this.onload();
			}
		};
		
		document.getElementsByTagName('head')[0].appendChild(s);
		return s;
		
	}
};

is.Init.include('helpers:system');
is.Init.include('helpers:strings');
is.Init.include('helpers:objects');
is.Init.include('helpers:match');
is.Init.include('helpers:parser');
