var assert = require('assert'),
    EventDT = require('../dist/js/EventDT'),
    fs = require('fs'),
    ICAL = require('ical.js'),
    moment = require("moment-timezone"),
    ICalAnalyser = require('../dist/js/ICalAnalyser.js')
    ;

describe('ICalAnalyser', ()=>{
/*
    it(' - bad format throw exception', ()=>{
        try {
            var fileContent = fs.readFileSync(__dirname + '/NOT-AN-ICS.ics');
            var analyser = new ICalAnalyser();
            analyser.parseFileContent(fileContent.toString());
            assert.equal(true, false, "Non-ICS file must throw !");


        } catch( error ){
            assert.ok("Non-ICS file throw exception : " + error);
        }

    })

    it(' - parseFileContent PLANNING_ICS-SIMPLE.ics', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/PLANNING_ICS-SIMPLE.ics');
        var analyser = new ICalAnalyser();
        var events = analyser.parseFileContent(fileContent.toString());
        events.forEach((item) =>{
            if( item.label == 'ITEM1' ){
                assert.equal('2017-03-14T08:00:00+01:00', item.start)
                assert.equal('2017-03-14T12:00:00+01:00', item.end)
                assert.equal(undefined, item.description)
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

    it(' - parseFileContent > Daily event non created by default)', ()=>{

        var fileContent = fs.readFileSync(__dirname + '/DAILY.ics');
        var analyser = new ICalAnalyser();
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(true, events.length == 0);
    })

    it(' - parseFileContent > Daily event configuration)', ()=>{

        var fileContent = fs.readFileSync(__dirname + '/DAILY.ics');
        var analyser = new ICalAnalyser(new Date(), [{startTime: '8:00', endTime: '12:00'}, {startTime: '13:00', endTime: '17:00'}]);
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(true, events.length == 2);
        assert.equal(true, events[0].start == '2017-08-21T08:00:00+02:00');
        assert.equal(true, events[0].end == '2017-08-21T12:00:00+02:00');
        assert.equal(true, events[1].start == '2017-08-21T13:00:00+02:00');
        assert.equal(true, events[1].end == '2017-08-21T17:00:00+02:00');
    })

    it(' - Daily event in ICS (custom)', ()=>{
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

    it(' - Daily event in ICS (ignore)', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/DAILY.ics');
        var analyser = new ICalAnalyser(new Date(), []);
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(true, events.length == 0);
    })

    it(' - filter', ()=>{
        // TODO
    })

    it(' - Repeated', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/RECURENT-SIMPLE-Aout2017.ics');
        var analyser = new ICalAnalyser(new Date('2017-08-31'));
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(5, events.length);

        assert.equal('2017-08-01T08:00:00+02:00', events[0].start); assert.equal('2017-08-01T12:00:00+02:00', events[0].end);
        assert.equal('2017-08-08T08:00:00+02:00', events[1].start); assert.equal('2017-08-08T12:00:00+02:00', events[1].end);
        assert.equal('2017-08-15T08:00:00+02:00', events[2].start); assert.equal('2017-08-15T12:00:00+02:00', events[2].end);
        assert.equal('2017-08-22T08:00:00+02:00', events[3].start); assert.equal('2017-08-22T12:00:00+02:00', events[3].end);
        assert.equal('2017-08-29T08:00:00+02:00', events[4].start); assert.equal('2017-08-29T12:00:00+02:00', events[4].end);
    })

    it(' - Repeated (Del)', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/RECURENT-EXCEPTION-del-Aout2017.ics');

        var analyser = new ICalAnalyser(new Date('2017-08-31'));
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(4, events.length);

        assert.equal('2017-08-01T08:00:00+02:00', events[0].start); assert.equal('2017-08-01T12:00:00+02:00', events[0].end);
        assert.equal('2017-08-08T08:00:00+02:00', events[1].start); assert.equal('2017-08-08T12:00:00+02:00', events[1].end);
        assert.equal('2017-08-15T09:00:00+02:00', events[2].start); assert.equal('2017-08-15T13:00:00+02:00', events[2].end);
        assert.equal('2017-08-29T08:00:00+02:00', events[3].start); assert.equal('2017-08-29T12:00:00+02:00', events[3].end);
    })

    it(' - Repeated (with on exception)', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/RECURENT-SIMPLE-Aout2017.ics');
        var analyser = new ICalAnalyser(new Date('2017-08-31'));
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(5, events.length);
    })

    it(' - Repeated (daily events)', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/RECURENT-DAILY.ics');
        var analyser = new ICalAnalyser(new Date('2017-09-01'), [{startTime: '8:00', endTime: '16:00'}]);
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(5, events.length);
    })

    it(' - Many days', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/MANY-DAY.ics');
        var analyser = new ICalAnalyser(new Date('2017-09-01'), [{startTime: '8:00', endTime: '16:00'}]);
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(3, events.length);
    })

    it(' - Many labels', ()=>{
        var fileContent = fs.readFileSync(__dirname + '/MANY-LABELS.ics');
        var analyser = new ICalAnalyser(new Date('2017-09-01'), [{startTime: '8:00', endTime: '16:00'}]);
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(20, events.length);
    })

    it(' Weekly reapeat with exception', ()=>{

        var fileContent = fs.readFileSync(__dirname + '/RECURENT-DAILY-WEEKLY-WITH-EXCEPTION.ics');
        var analyser = new ICalAnalyser(new Date('2017-09-01'), [{startTime: '8:00', endTime: '16:00'}]);
        analyser.debugMode = true;
        var events  = analyser.parseFileContent(fileContent.toString());
        assert.equal(8, events.length);
    })
/****/
    it(' Weekly reapeat with exception', ()=>{

        var fileContent = fs.readFileSync(__dirname + '/BIG.ics');
        var analyser = new ICalAnalyser(new Date('2017-09-01'), [{startTime: '8:00', endTime: '16:00'}]);
        analyser.debugMode = true;
        var events  = analyser.parseFileContent(fileContent.toString());
        // assert.equal(8, events.length);

    })

});
