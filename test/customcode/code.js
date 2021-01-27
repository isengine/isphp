document.addEventListener('DOMContentLoaded', function(){

//const urlbase = new URL('<?= $link; ?>');
const urlbase = new URL(window.location.href);

var url = {
	
	scheme: urlbase.protocol.replace(':', ''),
	host: urlbase.hostname,
	www: null,
	
	user: urlbase.username,
	password: urlbase.password,
	port: urlbase.port,
	
	path: urlbase.pathname,
	query: urlbase.search,
	fragment: urlbase.hash,
	
	domain: null,
	url: urlbase.href,
	previous: null,
	
	refresh: null
	
}

console.log(url);

let a = {"a":"3", "b":"2", "c":"1"};
let b = is.Helpers.Objects.sort(a);
let ca = is.Helpers.Objects.first(a);
let cb = is.Helpers.Objects.first(b);


console.log(a);
console.log(b);
console.log(ca);
console.log(cb);


});