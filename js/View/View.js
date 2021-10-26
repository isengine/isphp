is.View = class {
	
	static equal(haystack, needle) {
		return String(haystack) === String(needle) ? true : null;
	}
	
	static on(eventType, selector, callback) {
		document.body.addEventListener(eventType, function (event) {
			let len = event.path.length - 5;
			event.path.forEach(function(i, k){
				//это чтобы ограничить выход за рамки body
				if (k > len) {
					return;
				}
				if (i.matches(selector)) {
					//this - первый аргумент
					//i - это элемент, который был задан по селектору
					//event.target отправляет тот элемент, по которому произошел клик
					callback.call(i, event.target);
				}
			});
			//запись ниже не позволяет отследить родителей при клике на элемент
			//if (event.target.matches(selector)) {
			//	callback.call(event.target);
			//}
		});
	}
	
	static first(selector, callback) {
		// можно сделать this.querySelector
		// и чтобы this, если не задан, был document
		let elem = document.querySelector(selector);
		let i = {
			next : elem.nextElementSibling,
			prev : elem.previousElementSibling,
			parent : elem.parentElement,
			first : elem.firstElementChild,
			last : elem.lastElementChild
		};
		callback.call(elem, i);
	}
	
	static each(selector, callback) {
		//document.querySelectorAll(selector).forEach(i => callback.call(i));
		document.querySelectorAll(selector).forEach(function(elem){
			let i = {
				next : elem.nextElementSibling,
				prev : elem.previousElementSibling,
				parent : elem.parentElement,
				first : elem.firstElementChild,
				last : elem.lastElementChild
			};
			callback.call(elem, i);
		});
	}
	
}

/*
is.View.on('click', '#eshop-catalog .item-cart__block', function () {
	//console.log(i);
	console.log(this);
	//alert('Muaha!');
});

is.View.first('#eshop-catalog .item', function () {
	is.View.first('.item-cart__block', function (i) {
		console.log(i);
		
		i.next.style.display = "block";
		//this.style.display = "none";
		//console.log(this.classList);
		//alert('Muaha!');
	});
});

//is.View.each('#eshop-catalog .item-cart__block', function () {
//	console.log(this.classList);
//	alert('Muaha!');
//});

*/