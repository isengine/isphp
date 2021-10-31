
class isView {
	
	constructor(name = null) {
		
		// задаем свойства
		
		this.items = {};
		
		if (name === null) {
			
			let items = {};
			
			$("[is-parent]").each(function(){
				let name = $(this).attr("is-parent");
				if (!items[name]) {
					items[name] = new isView(name);
				}
			});
			
			this.items = items;
			
			return;
		}
		
		this._data = {};
		//this._items = $("[is-parent" + (name !== null ? "=\"" + name + "\"" : "") + "]");
		this._items = $("[is-parent=\"" + name + "\"]");
		
		// заполняем объект данных со всех родителей
		
		let data = {};
		this._items.each(function(){
			
			// здесь должны находить родителя
			// и по родителю записывать в this.items
			// но кроме того, должен быть туда доступ
			// для этого проще создавать внутри items
			// по ключая parent новые экземпляры данного класса
			
			// вложенные данные у родителя
			
			if ($(this).is("[is-data]")) {
				let name = $(this).attr("is-data");
				let value = $(this).val();
				if (!value && value !== 0) {
					value = $(this).html().trim();
				}
				data[name] = value;
			}
			
			// вложенные данные
			
			$(this).find("[is-data]").each(function(){
				let name = $(this).attr("is-data");
				let value = $(this).val();
				if (!value && value !== 0) {
					value = $(this).html().trim();
				}
				data[name] = value;
			});
			
			// данные из атрибутов у родителя
			
			if ($(this).is("[is-data-from]")) {
				$.each(this.attributes, function(index, attribute) {
					if (attribute.name.indexOf("data-") === 0) {
						let name = attribute.name.substr(5);
						data[name] = attribute.value;
					}
				});
			}
			
			// данные из атрибутов у вложенных элементов
			
			$(this).find("[is-data-from]").each(function(){
				$.each(this.attributes, function(index, attribute) {
					if (attribute.name.indexOf("data-") === 0) {
						let name = attribute.name.substr(5);
						data[name] = attribute.value;
					}
				});
			});
			
		});
		
		this._data = data;
		
		//console.log(this._data);
		
	}
	
	data(name = null, value = null) {
		
		// получить данные
		
		if (value !== null) {
			// если value задан, значит мы устанавливаем значения в объекты
			// name должен быть задан
			if (name !== null) {
				this._data[name] = value;
				this.refresh(name);
			}
		} else {
			// если value не задан, значит мы возвращаем значение из объекта
			// если name не задан, значит мы возвращаем все данные
			if (name === null) {
				return this._data;
			}
			// если name задан, возвращаем значение по ключу name
			return this._data[name];
		}
		
	}
	
	value(name, callback) {
		
		// обработать значение данных
		
		let current = this._data[name];
		let result = callback.call(this, current);
		this._data[name] = result;
		this.refresh(name);
		
	}
	
	action(name, type, callback) {
		
		// задать событие
		
		let selector = "[is-action=\"" + name + "\"]";
		
		//$("body " + selector).each(function(){
		//this._items.find(selector).each(function(){
		//$("body").find(this._items).find(selector).each(function(){
		//	$(this).on(type, callback);
		//});
		
		$("body").find(this._items).find(selector).on(type, callback);
		
	}
	
	refresh(name = null, obj = this) {
		
		// функция обновляет данные
		// если name не задан, значит все данные
		// если name задан, значит данные по ключу name
		
		if (name === null) {
			let parents = this;
			Object.entries(this._data).forEach(function(i){
				parents.refresh(i[0], parents);
			});
			return;
		}
		
		let value = obj.data(name);
		let selector = "[is-data=\"" + name + "\"]";
		
		obj._items.each(function(){
			$(this).find(selector).each(function(){
				if ($(this).is("[value]")) {
					$(this).val(value);
					$(this).attr("value", value);
				} else {
					$(this).html(value);
				}
			});
		});
		
	}
	
	say() {
		//alert(this.name ? this.name : "asdf");
	}
	
}
