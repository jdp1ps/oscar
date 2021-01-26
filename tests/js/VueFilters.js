var VueFilters = require("../../public/js/oscar/dist/VueFilters").default;
var assert = require('assert');

console.log(VueFilters);

describe('Test de rendu des tunes', function () {
    console.log("Foo ???");

    it("Valeur 0.0", function () {
        assert.equal(VueFilters.money(0.0), '0,00')
    })

    it("Valeur 99.9", function () {
        assert.equal(VueFilters.money(99.9), '99,90')
    })

    it("Valeur 1000.0"  , function () {
        assert.equal(VueFilters.money(1000.0), '1 000,00')
    })

    it("Valeur -5204.42"  , function () {
        assert.equal(VueFilters.money(-5204.42), '-5 204,42')
    })

    it("Valeur  -39520.89"  , function () {
        assert.equal(VueFilters.money(-39520.89), '-39 520,89')
    })



});