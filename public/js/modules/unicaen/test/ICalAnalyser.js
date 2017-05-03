var assert = require('assert'),
    EventDT = require('../dist/js/EventDT'),
    fs = require('fs'),
    ICAL = require('ical.js'),
    moment = require("moment-timezone"),
    ICalAnalyser = require('../dist/js/ICalAnalyser.js')
    ;
/*
describe('Test ICalAnalyser instance', ()=>{

    it('Non-ICS file throw exception', ()=>{
        try {
            var fileContent = fs.readFileSync(__dirname + '/NOT-AN-ICS.ics');
            var analyser = new ICalAnalyser();
            analyser.parseFileContent(fileContent.toString());
            assert.equal(true, false, "Non-ICS file must throw !");


        } catch( error ){
            assert.ok("Non-ICS file throw exception : " + error);
        }

    })

    it('parse file content PLANNING_ICS-SIMPLE.ics', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/PLANNING_ICS-SIMPLE.ics');
        var analyser = new ICalAnalyser();
        var events = analyser.parseFileContent(fileContent.toString());
        events.forEach((item) =>{
            if( item.label == 'ITEM1' ){
                assert.equal('2017-03-14T08:00:00+01:00', item.start)
                assert.equal('2017-03-14T12:00:00+01:00', item.end)
            }
            if( item.label == 'ITEM3' ){
                assert.equal('2017-03-14T13:00:00+01:00', item.start)
                assert.equal('2017-03-14T17:00:00+01:00', item.end)
            }
            if( item.label == 'ITEM2' ){
                assert.equal('2017-03-15T08:00:00+01:00', item.start)
                assert.equal('2017-03-15T12:00:00+01:00', item.end)
            }
            if( item.label == 'ITEM4' ){
                assert.equal('2017-03-15T13:00:00+01:00', item.start)
                assert.equal('2017-03-15T17:00:00+01:00', item.end)
            }
        })
    })

});
*/