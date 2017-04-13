/**
 * Created by jacksay on 17-02-27.
 */
var assert = require('assert'),
    amdLoader = require("amd-loader"),
    EventModel = require('../build/EventModel.js');



describe('Test create', ()=>{
    it('Created ?', ()=>{
        var event = new EventModel.Event();
        console.log(event);
        assert.equal('1', 1);
    })
})