var assert = require('assert');
var model = require(__dirname + '/../public/js/oscar/dist/CalendarModel.js').default;

console.log(model);

describe('Validation Process', function(){
    describe('EventValidation', function(){
        it('not null', function() {
            var validation = new model.ValidationEvent();
            assert.ok(validation, "N'est pas null");
            assert.equal(true, validation.isValid());
        });
    })
})