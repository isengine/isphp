
class isView {
	
	constructor(name = null) {
		
		let items = {};
		
		this.each = $("[is-parent" + (name !== null ? "=\"" + name + "\"" : "") + "]");
		
		this.each.each(function(i){
			let attr = $(this).attr('is-name');
			items[attr !== null ? attr : i] = $(this);
		});
		
		this.items = items;
		
		console.log(this.items);
		
	}
	
	say() {
		//alert(this.name ? this.name : "asdf");
	}
	
}
