var assert = require('assert');
var format = require(__dirname +'/../public/js/oscar/dist/VueFilters.js');
var value;

value = 0;
console.log(value +'\t > ' +format.default.money(value));
assert.equal(format.default.money(value), '0,00');

value = 79;
console.log(value +'\t > ' +format.default.money(value));
assert.equal(format.default.money(value), '79,00');

value = 500;
console.log(value +'\t > ' +format.default.money(value));
assert.equal(format.default.money(value), '500,00');

value = 2500;
console.log(value +'\t > ' +format.default.money(value));
assert.equal(format.default.money(value), '2 500,00');

value = 19750;
console.log(value +'\t > ' +format.default.money(value));
assert.equal(format.default.money(value), '19 750,00');

value = 1000000;
console.log(value +'\t > ' +format.default.money(value));
assert.equal(format.default.money(value), '1 000 000,00');



