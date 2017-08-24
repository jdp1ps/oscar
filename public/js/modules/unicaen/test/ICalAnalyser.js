var assert = require('assert'),
    EventDT = require('../dist/js/EventDT'),
    fs = require('fs'),
    ICAL = require('ical.js'),
    moment = require("moment-timezone"),
    ICalAnalyser = require('../dist/js/ICalAnalyser.js')
    ;

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
    
/****/
    it('Daily event in ICS', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/DAILY.ics');
        var analyser = new ICalAnalyser();
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(true, events.length == 2);
        assert.equal(true, events[0].start == '2017-08-21T08:00:00+02:00');
        assert.equal(true, events[0].end == '2017-08-21T12:00:00+02:00');
        assert.equal(true, events[1].start == '2017-08-21T13:00:00+02:00');
        assert.equal(true, events[1].end == '2017-08-21T17:00:00+02:00');
    })

    it('Daily event in ICS (custom)', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/DAILY.ics');
        var analyser = new ICalAnalyser(new Date(), [{startTime: '8:00', endTime: '12:00'}, {startTime: '13:00', endTime: '15:00'}, {startTime: '16:00', endTime: '18:00'}]);
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(true, events.length == 3);
        assert.equal(true, events[0].start == '2017-08-21T08:00:00+02:00');
        assert.equal(true, events[0].end == '2017-08-21T12:00:00+02:00');
        assert.equal(true, events[1].start == '2017-08-21T13:00:00+02:00');
        assert.equal(true, events[1].end == '2017-08-21T15:00:00+02:00');
        assert.equal(true, events[2].start == '2017-08-21T16:00:00+02:00');
        assert.equal(true, events[2].end == '2017-08-21T18:00:00+02:00');
    })

    it('Daily event in ICS (ignore)', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/DAILY.ics');
        var analyser = new ICalAnalyser(new Date(), []);
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(true, events.length == 0);
    })
});
