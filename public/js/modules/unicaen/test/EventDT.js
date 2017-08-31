var assert = require('assert'),
    EventDT = require('../dist/js/EventDT.js')
    ;

describe('Test EventDT instance', ()=>{
    it('Methods inWeek(year, week), single day event', ()=>{
      var e = new EventDT({ id: 1, label: 'Item1', start: '2017-01-03', end:'2017-01-03' });
      assert.equal(e.inWeek(2017, 1), true)

      e = new EventDT({ id: 1, label: 'Item1', start: '2017-01-07', end:'2017-01-07' });
      assert.equal(e.inWeek(2017, 1), true)

      e = new EventDT({ id: 1, label: 'Item1', start: '2017-01-08', end:'2017-01-08' });
      assert.equal(e.inWeek(2017, 1), true)

    })

    it('Methods inWeek(year, week), Start before, end after', ()=>{
      var e = new EventDT({ id: 1, label: 'Item1', start: '2016-12-15', end:'2017-01-15' });
      assert.equal(e.inWeek(2017, 1), true, "Mi décembre à Mi Janvier")
    })

    it('Methods inWeek(year, week), Start before, end inside', ()=>{
      var e = new EventDT({ id: 1, label: 'Item1', start: '2016-12-15', end:'2017-01-03' });
      assert.equal(e.inWeek(2017, 1), true, "Mi décembre à Mi semaine")
    })

    it('Methods inWeek(year, week), Start inside, end after', ()=>{
      var e = new EventDT({ id: 1, label: 'Item1', start: '2017-01-03', end:'2017-03-03' });
      assert.equal(e.inWeek(2017, 1), true, "Mi décembre à Mi semaine")
    })

    it('Methods inWeek(year, week), Start inside, end inside', ()=>{
      var e = new EventDT({ id: 1, label: 'Item1', start: '2017-01-03', end:'2017-01-03' });
      assert.equal(e.inWeek(2017, 1), true, "Dedans")
    })

    it('Methods inWeek(year, week), Outside before', ()=>{
      var e = new EventDT({ id: 1, label: 'Item1', start: '2016-01-03', end: '2016-01-03' });
      assert.equal(e.inWeek(2017, 1), false, "avant")
    })

    it('Methods inWeek(year, week), Outside after', ()=>{
      var e = new EventDT({ id: 1, label: 'Item1', start: '2017-01-15', end:'2017-01-15' });
      assert.equal(e.inWeek(2017, 1), false, "après")
    })


    ////////////////////////////////////////////////////////////////////////////
    it('Methods first(e1, e2), return first', ()=>{
        var e1 = new EventDT({ id: 1, label: 'Item1', start: '2017-01-01', end:'2017-01-01' });
        var e2 = new EventDT({ id: 2, label: 'Item2', start: '2017-01-02', end:'2017-01-02' });
        var e3 = new EventDT({ id: 2, label: 'Item3', start: '2017-01-03', end:'2017-01-03' });
        assert.equal(EventDT.first([e1, e2, e3]), e1, 'Retourne le premier événement');
    })

    ////////////////////////////////////////////////////////////////////////////
    it('Methods byDateStart(e1, e2), return events sorted by date start', ()=>{
        var e1 = new EventDT({ id: 1, label: 'Item1', start: '2017-01-01', end:'2017-01-01' });
        var e2 = new EventDT({ id: 2, label: 'Item2', start: '2017-01-02', end:'2017-01-02' });
        var e3 = new EventDT({ id: 2, label: 'Item3', start: '2017-01-03', end:'2017-01-03' });

        var unOrder = [e2,  e3, e1];
        EventDT.sortByStart(unOrder);

        assert.equal(unOrder[0], e1, 'premier est e1');
        assert.equal(unOrder[1], e2, 'deuxième est e2');
        assert.equal(unOrder[2], e3, 'premier est e3');
    })

    ////////////////////////////////////////////////////////////////////////////
    it('Methods duration(e1, e2), return events sorted by date start', ()=>{
        var e1 = new EventDT({ id: 1, label: 'Item1', start: '2017-01-01T08:00', end: '2017-01-01T12:00' });
        var e2 = new EventDT({ id: 1, label: 'Item2', start: '2017-01-01T08:00', end: '2017-01-01T10:30' });

        assert.equal(e1.duration, 4, 'Dure 4 heures');
        assert.equal(e2.duration, 2.5, 'Dure 2.5 heures');
    })


    it('Methods overlap(event), OVERLAP', ()=>{
        var u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });
        var u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });

        assert.equal(u1.overlap(u2), true, "U1 et U2 sont identique");

        u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });
        u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 09:00', end: '2017-07-04 11:00' });
        assert.equal(u1.overlap(u2), true, "U1 est croisé avec U2");

        u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });
        u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 07:00', end: '2017-07-04 09:00' });
        assert.equal(u1.overlap(u2), true, "U2 est croisé avec U1");

        u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });
        u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 08:30', end: '2017-07-04 09:30' });
        assert.equal(u1.overlap(u2), true, "U2 est dans U1");

        u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 09:00', end: '2017-07-04 10:00' });
        u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 08:30', end: '2017-07-04 11:00' });
        assert.equal(u1.overlap(u2), true, "U2 est dans U1");
    })

    it('Methods overlap(event), NOOVERLAP', ()=>{
        var u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });
        var u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 10:00', end: '2017-07-04 11:00' });
        assert.equal(u1.overlap(u2), false, "U1 fini au début de U2");

        var u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });
        var u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 06:00', end: '2017-07-04 08:00' });
        assert.equal(u1.overlap(u2), false, "U2 fini au début de U1");

        var u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 08:00', end: '2017-07-04 10:00' });
        var u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 11:00', end: '2017-07-04 12:00' });
        assert.equal(u1.overlap(u2), false, "U1 avant U2");

        var u1 = new EventDT({ id: 1, label: 'U1', start: '2017-07-04 09:00', end: '2017-07-04 10:00' });
        var u2 = new EventDT({ id: 2, label: 'U2', start: '2017-07-04 06:00', end: '2017-07-04 08:00' });
        assert.equal(u1.overlap(u2), false, "U2 avant U1");

    })
});
