<script>

// ТЕСТЫ

/*
//var $var = NaN;
//alert(Number.isNaN($var));

//a = [10,20,30,40];
a = {"a" : 10, "b" : 20, "c" : 30, "d" : 40};

b = is.Helpers.System.each(a, 'filter', function(i, k){
  //return k + '=>' + i;
  if (k === 'a') {
	return false;
  } else {
	return k + '=>' + i;
  }
  //console.log(k + '=>' + i);
});

console.log(b);
*/

a = is.Helpers.Data;

c = [];
c = {};
c = 'asd';
//c = ['123', '123', '222'];
//c = {0: '123', 1: '123'};
//c = {'c': '123', 'a': '123'};
//c = 'c:123|a:123';
//c = '';
//c = is.Helpers.System.parse(c, {'key': true, 'simple': true, 'clear': true});
//console.log(is.Helpers.System.type(c));
console.log(is.Helpers.Data.convert(c));

console.log( 'a' );
console.log( is.Helpers.Data.is(a) );

console.log( 'c' );
console.log( is.Helpers.Data.is(c) );

a = 'a123:!123:123|!b123:123|c123:123::1';
//console.log(is.Helpers.System.parse(a, {'key': true, 'simple': true, 'clear': true}));
//console.log(is.Helpers.System.parse(a, {'key': false, 'simple': true, 'clear': true}));

// ТЕСТЫ

//a = [10,20,30,40];
a = {"a" : 10, "b" : 20, "c" : 30, "d" : 40};
//console.log(a);

b = is.Helpers.Data.each(a, [1], function(i, k, c){
	
	c[k + 1] = i + 10;
	
});

a = '0123456789abcdef';
a = 'А роза упала на лапу Азора ウィキ';

//b = is.Helpers.Strings.replace(a, ['ла', 'А'], ['... ля-ля-ля', 'Ан']);
//b = is.Helpers.Strings.replace(a, ['ла', 'А'], '...');
b = is.Helpers.Strings.replace(a, ['А', 'а'], ['ан', '#']);
//b = is.Helpers.Strings.find(a, 'we', -2);
//b = is.Helpers.Strings.cut(a, 2);
console.log(b);
//b = is.Helpers.Strings.cut(a, -2);
//console.log(b);

a = ['11','22','33','44','55','66'];
a = {e : 10, b : 20, c : 30, d : 40};
a = {"1e" : 10, "2b" : 20, "3c" : 30, "4d" : 40};
a = {"1" : 10, "2" : 20, "3" : 30, "4" : 40};

console.log('---');
console.log(is.Helpers.Data.associate(a));
console.log(is.Helpers.Data.len(a));
console.log(is.Helpers.Data.first(a));
console.log(is.Helpers.Data.last(a));
console.log(is.Helpers.Data.reverse(a));


a = 'positionare';
console.log('REVERSE');
console.log(is.Helpers.Strings.cut(a, 1, 0, true));
console.log(is.Helpers.Strings.cut(a, 1, 1, true));
console.log(is.Helpers.Strings.cut(a, 1, 2, true));
console.log('DEF');
console.log(is.Helpers.Strings.cut(a, 1));
console.log(is.Helpers.Strings.cut(a, 3));
console.log(is.Helpers.Strings.cut(a, 6));
console.log(is.Helpers.Strings.cut(a, -1));
console.log(is.Helpers.Strings.cut(a, -3));
console.log(is.Helpers.Strings.cut(a, -6));
console.log('CUT');
console.log(is.Helpers.Strings.cut(a, 0, 1));
console.log(is.Helpers.Strings.cut(a, 3, 1));
console.log(is.Helpers.Strings.cut(a, 6, 1));
console.log(is.Helpers.Strings.cut(a, 0, 3));
console.log(is.Helpers.Strings.cut(a, 3, 3));
console.log(is.Helpers.Strings.cut(a, 6, 3));
console.log(is.Helpers.Strings.cut(a, 6, -3));
console.log(is.Helpers.Strings.cut(a, -1, 1));
console.log(is.Helpers.Strings.cut(a, -3, 1));
console.log(is.Helpers.Strings.cut(a, -6, 1));
console.log(is.Helpers.Strings.cut(a, -6, 3));
console.log(is.Helpers.Strings.cut(a, -6, -3));
console.log('---');
console.log('+', is.Helpers.Strings.cut(a, 6, 30));
console.log('+', is.Helpers.Strings.cut(a, 6, -30));
console.log('+', is.Helpers.Strings.cut(a, -6, 30));
console.log('+', is.Helpers.Strings.cut(a, -6, -30));
console.log('+', is.Helpers.Strings.cut(a, 30, 30));
console.log('+', is.Helpers.Strings.cut(a, 30, -30));
console.log('+', is.Helpers.Strings.cut(a, -30, 30));
console.log('+', is.Helpers.Strings.cut(a, -30, -30));

//console.log('STRING');
//console.log(is.Helpers.Strings.get(a, 0));
//console.log(is.Helpers.Strings.get(a, 3));
//console.log(is.Helpers.Strings.get(a, 6));
//console.log(is.Helpers.Strings.get(a, 0, 3));
//console.log(is.Helpers.Strings.get(a, 3, 3));
//console.log(is.Helpers.Strings.get(a, 6, 3));
//console.log(is.Helpers.Strings.get(a, 6, -3));
//console.log(is.Helpers.Strings.get(a, -3));
//console.log(is.Helpers.Strings.get(a, -6));
//console.log(is.Helpers.Strings.get(a, -6, 3));
//console.log(is.Helpers.Strings.get(a, -6, -3));
//console.log('---');
//console.log('r', is.Helpers.Strings.get(a, 0, 0, 'r'));
//console.log('r', is.Helpers.Strings.get(a, 1, 1, 'r'));
//console.log('r', is.Helpers.Strings.get(a, 2, 2, 'r'));
//console.log('---');
//console.log('+', is.Helpers.Strings.get(a, 6, 30)); // onare
//console.log('+', is.Helpers.Strings.get(a, 6, -30)); // positio
//console.log('+', is.Helpers.Strings.get(a, -6, 30)); // ionare
//console.log('+', is.Helpers.Strings.get(a, -6, -30)); // positi
//console.log('+', is.Helpers.Strings.get(a, 30, 30)); // -
//console.log('+', is.Helpers.Strings.get(a, 30, -30)); // ositionare
//console.log('+', is.Helpers.Strings.get(a, -30, 30)); // positionare
//console.log('+', is.Helpers.Strings.get(a, -30, -30)); // -

a = ['p','o','s','i','t','i','o','n','a','r','e'];
b = {'p1':1,'o2':2,'s3':3,'i4':4,'t5':5,'i6':6,'o7':7,'n8':8,'a9':9,'r10':10,'e11':11};
console.log('REVERSE');
console.log(is.Helpers.Data.cut(a, 1, 0, true));
console.log(is.Helpers.Data.cut(a, 1, 1, true));
console.log(is.Helpers.Data.cut(a, 1, 2, true));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 1, 0, true)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 1, 1, true)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 1, 2, true)));
console.log('DEF');
console.log(is.Helpers.Data.cut(a, 1));
console.log(is.Helpers.Data.cut(a, 3));
console.log(is.Helpers.Data.cut(a, 6));
console.log(is.Helpers.Data.cut(a, -1));
console.log(is.Helpers.Data.cut(a, -3));
console.log(is.Helpers.Data.cut(a, -6));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 3)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 6)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -3)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -6)));
console.log('CUT');
console.log(is.Helpers.Data.cut(a, 0, 1));
console.log(is.Helpers.Data.cut(a, 3, 1));
console.log(is.Helpers.Data.cut(a, 6, 1));
console.log(is.Helpers.Data.cut(a, 0, 3));
console.log(is.Helpers.Data.cut(a, 3, 3));
console.log(is.Helpers.Data.cut(a, 6, 3));
console.log(is.Helpers.Data.cut(a, 6, -3));
console.log(is.Helpers.Data.cut(a, -1, 1));
console.log(is.Helpers.Data.cut(a, -3, 1));
console.log(is.Helpers.Data.cut(a, -6, 1));
console.log(is.Helpers.Data.cut(a, -6, 3));
console.log(is.Helpers.Data.cut(a, -6, -3));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 0, 1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 3, 1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 6, 1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 0, 3)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 3, 3)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 6, 3)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, 6, -3)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -1, 1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -3, 1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -6, 1)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -6, 3)));
console.log(JSON.stringify(is.Helpers.Data.cut(b, -6, -3)));
console.log('---');
console.log('+', is.Helpers.Data.cut(a, 6, 30));
console.log('+', is.Helpers.Data.cut(a, 6, -30));
console.log('+', is.Helpers.Data.cut(a, -6, 30));
console.log('+', is.Helpers.Data.cut(a, -6, -30));
console.log('+', is.Helpers.Data.cut(a, 30, 30));
console.log('+', is.Helpers.Data.cut(a, 30, -30));
console.log('+', is.Helpers.Data.cut(a, -30, 30));
console.log('+', is.Helpers.Data.cut(a, -30, -30));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, 6, 30)));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, 6, -30)));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, -6, 30)));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, -6, -30)));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, 30, 30)));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, 30, -30)));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, -30, 30)));
console.log('+', JSON.stringify(is.Helpers.Data.cut(b, -30, -30)));


//console.log('DATA');
//console.log(is.Helpers.Data.get(a, 0));
//console.log(JSON.stringify(is.Helpers.Data.get(b, 0)));
//console.log(is.Helpers.Data.get(a, 3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, 3)));
//console.log(is.Helpers.Data.get(a, 6));
//console.log(JSON.stringify(is.Helpers.Data.get(b, 6)));
//console.log(is.Helpers.Data.get(a, 0, 3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, 0, 3)));
//console.log(is.Helpers.Data.get(a, 3, 3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, 3, 3)));
//console.log(is.Helpers.Data.get(a, 6, 3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, 6, 3)));
//console.log(is.Helpers.Data.get(a, 6, -3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, 6, -3)));
//console.log(is.Helpers.Data.get(a, -3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, -3)));
//console.log(is.Helpers.Data.get(a, -6));
//console.log(JSON.stringify(is.Helpers.Data.get(b, -6)));
//console.log(is.Helpers.Data.get(a, -6, 3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, -6, 3)));
//console.log(is.Helpers.Data.get(a, -6, -3));
//console.log(JSON.stringify(is.Helpers.Data.get(b, -6, -3)));
//console.log('---');
//console.log('r', is.Helpers.Data.get(a, 0, 0, 'r'));
//console.log('r', JSON.stringify(is.Helpers.Data.get(b, 0, 0, 'r')));
//console.log('r', is.Helpers.Data.get(a, 1, 1, 'r'));
//console.log('r', JSON.stringify(is.Helpers.Data.get(b, 1, 1, 'r')));
//console.log('r', is.Helpers.Data.get(a, 2, 2, 'r'));
//console.log('r', JSON.stringify(is.Helpers.Data.get(b, 2, 2, 'r')));
//console.log('---');
//console.log('+', is.Helpers.Data.get(a, 6, 30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, 6, 30)));
//console.log('+', is.Helpers.Data.get(a, 6, -30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, 6, -30)));
//console.log('+', is.Helpers.Data.get(a, -6, 30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, -6, 30)));
//console.log('+', is.Helpers.Data.get(a, -6, -30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, -6, -30)));
//console.log('+', is.Helpers.Data.get(a, 30, 30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, 30, 30)));
//console.log('+', is.Helpers.Data.get(a, 30, -30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, 30, -30)));
//console.log('+', is.Helpers.Data.get(a, -30, 30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, -30, 30)));
//console.log('+', is.Helpers.Data.get(a, -30, -30));
//console.log('+', JSON.stringify(is.Helpers.Data.get(b, -30, -30)));

</script>
