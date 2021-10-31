<?php

namespace is;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Masters\View;

$view = View::getInstance();

?>

<div is-parent="asd" is-data="str">
	qwertyuiop
</div>
<div is-parent="asd" data-b="bbb" is-data-from="true">
	<input type="name1" value="1" is-data="max">
	<input type="name2" value is-data="max">
	<button is-action="dec">-</button>
	<button is-action="inc">+</button>
</div>

<div is-parent="asd">
	<div data-step="2" data-abc="055" is-data-from="true">
	<span class="" is-data="max">
		3
	</span>
	<button is-action="inc">*</button>
	<button is-action="dec">-</button>
	<button is-action="inc">+</button>
	</div>
</div>

<div is-parent="qwe">
	<input type="name1" value="1" is-data="max">
	<button is-action="dec">-</button>
	<button is-action="inc">+</button>
</div>

<div is-parent="qwe">
	<span class="" is-data="max">
		3
	</span>
	<button is-action="dec">-</button>
	<button is-action="inc">+</button>
</div>

<script>
	
	//let a = new isView("asd");
	//a.data("max", 54.2);
	//a.action("inc", "click", () => a.value("max", (i) => ++i));
	//a.action("dec", "click", () => a.value("max", (i) => --i));
	//
	//let q = new isView("qwe");
	//q.data("max", 5.2);
	//q.action("inc", "click", () => q.value("max", (i) => ++i));
	//q.action("dec", "click", () => q.value("max", (i) => --i));
	
	//let a = new isView();
	//a.items.asd.data("max", 54.2);
	//a.items.asd.action("inc", "click", () => a.items.asd.value("max", (i) => ++i));
	//a.items.asd.action("dec", "click", () => a.items.asd.value("max", (i) => --i));
	//a.items.qwe.data("max", 5.2);
	//a.items.qwe.action("inc", "click", () => a.items.qwe.value("max", (i) => ++i));
	//a.items.qwe.action("dec", "click", () => a.items.qwe.value("max", (i) => --i));
	
	let a = new isView();
	let start = {
		asd : 54.2,
		qwe : 5.8
	}
	$.each(
		a.items,
		function(i){
			this.data("max", start[i]);
			this.action("inc", "click", () => this.value("max", (i) => ++i));
			this.action("dec", "click", () => this.value("max", (i) => --i));
		}
	);
	
	//let a = new isView("asd");
	//a.data("max", 54.2);
	//a.action("inc", "click", () => a.value("max", (i) => ++i));
	//a.action("dec", "click", () => a.value("max", (i) => --i));
	//a.action("inc", "click", () => a.data("max", parseFloat(a.data("max")) + parseFloat(a.data("step"))));
	
	//a.refresh();
	//a.refresh("max");
	//console.log("---");
	//console.log(a);
	//console.log( a._data );
	//console.log( a.data() );
	//console.log( a.data("value") );
	//console.log( a.data().value );
	//console.log( a.data()["value"] );
	//console.log( a._data.value );
	//console.log( a._data["value"] );
	//a.each.addClass('d-none');
</script>