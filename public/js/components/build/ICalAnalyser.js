;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['moment', 'ical'], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory(require('moment'), require('ical'));
  } else {
    root.ICalAnalyser = factory(root.moment, root.ical);
  }
}(this, function(moment, ical) {
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// import moment from "moment-timezone";

moment.locale('fr');
moment.tz(moment.tz.guess());

var ICalAnalyser = function () {
    function ICalAnalyser() {
        var ending = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : new Date();

        _classCallCheck(this, ICalAnalyser);

        if (ending instanceof String) ending = new Date(ending);

        if (!(ending instanceof Date)) throw 'Bad usage, date or string required.';

        this.ending = typeof ending == 'string' ? new Date(ending) : ending;
        this.daysString = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
        this.summaries = [];
    }

    _createClass(ICalAnalyser, [{
        key: 'generateItem',
        value: function generateItem(item) {
            // POST traitement
            var mmStart = moment(item.start);
            var mmEnd = moment(item.end);

            // Détection des chevauchements
            // découpe la période en 2 morceaux pour n'avoir que des périodes
            // journalières.
            if (mmStart.date() != mmEnd.date()) {

                var part1 = JSON.parse(JSON.stringify(item)),
                    part2 = JSON.parse(JSON.stringify(item)),
                    splitEnd = mmStart.endOf('day');

                part1.end = splitEnd.toISOString();

                var beginnextDay = splitEnd.add(1, 'day').startOf('day');
                part2.start = beginnextDay.toISOString();

                // Si le deuxième morceau a une durée nulle, on l'ignore
                if (part2.start == part2.end) {
                    return this.generateItem(part1);
                }
                return [].concat(this.generateItem(part1)).concat(this.generateItem(part2));
            }
            return [{ label: item.summary, summary: item.summary, start: item.start, end: item.end, description: item.description }];
        }

        /**
         * Traitement des événements récursifs.
         *
         * @param item
         * @param rrule
         * @param exdate
         * @returns {Array}
         */

    }, {
        key: 'repeat',
        value: function repeat(item, rrule) {
            var exdate = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;


            var items = [];
            item.recursive = true;

            if (rrule.freq == 'DAILY' || rrule.freq == 'WEEKLY') {
                var fromDate = new Date(item.start);
                var toDate = new Date(item.end);
                var end = rrule.until ? new Date(rrule.until) : this.ending;
                var interval = rrule.interval || 1;
                var pas = rrule.freq == 'DAILY' ? 1 : 7;
                var count = rrule.count || null;
                var byday = rrule.byday || this.daysString;
                if (byday instanceof String) byday = [byday];

                if (count) {
                    for (var i = 0; i < count; i++) {
                        var copy = JSON.parse(JSON.stringify(item));
                        copy.start = moment(fromDate).toISOString();
                        copy.end = moment(toDate).toISOString();
                        copy.recursive = true;
                        items = items.concat(this.generateItem(copy));
                        fromDate.setDate(fromDate.getDate() + interval * pas);
                        toDate.setDate(toDate.getDate() + interval * pas);
                    }
                } else {
                    while (fromDate < end) {
                        var currentDay = this.daysString[fromDate.getDay()];

                        if (!(byday.indexOf(currentDay) < 0 || exdate.indexOf(fromDate.toISOString()) > -1)) {
                            var _copy = JSON.parse(JSON.stringify(item));
                            _copy.start = moment(fromDate).format();
                            _copy.end = moment(toDate).format();
                            _copy.recursive = true;
                            items = items.concat(this.generateItem(_copy));
                        }
                        fromDate.setDate(fromDate.getDate() + interval * pas);
                        toDate.setDate(toDate.getDate() + interval * pas);
                    }
                }
            } else {
                console.log('RECURENCE NON-TRAITEE', rrule);
            }

            if (items.length == 0) {
                console.log(" !!!!!!!!!!!!!!!! RIEN de CRÉÉ", item, rrule);
                console.log(' TO => ', new Date(rrule.until));
                console.log(' TO => ', this.ending);
                console.log(' TO => ', end);
            } else {
                console.log(' ================ ', items.length, ' créé(s)');
            }

            return items;
        }
    }, {
        key: 'parse',
        value: function parse(icsData) {
            var _this = this;

            // local TZ
            var defaultTimeZone = moment.tz.guess(),
                out = [],
                exceptions = [];

            icsData[2].forEach(function (d) {
                var item = { warnings: [] },
                    rrule = null,
                    exdate = [];

                // Extraction des données brutes
                if (d[0] == 'vevent') {
                    d[1].forEach(function (dd) {
                        if (dd[0] == 'uid') item.uid = dd[3];else if (dd[0] == 'rrule') {
                            rrule = dd[3];
                        } else if (dd[0] == 'exdate') {
                            var m = moment.tz(dd[3], dd[1].tzid);
                            exdate.push(m.tz(defaultTimeZone).format());
                        } else if (dd[0] == 'organizer') {
                            item.email = dd[3];
                        } else if (dd[0] == 'description') {
                            item.description = dd[3];
                        } else if (dd[0] == 'dtstart') {
                            var m = moment.tz(dd[3], dd[1].tzid);
                            item.start = m.tz(defaultTimeZone).format();
                        } else if (dd[0] == 'recurrence-id') {
                            var m = moment.tz(dd[3], dd[1].tzid);
                            item.exception = m.tz(defaultTimeZone).format();
                        } else if (dd[0] == 'dtend') {
                            var m = moment.tz(dd[3], dd[1].tzid);
                            item.end = m.tz(defaultTimeZone).format();
                        } else if (dd[0] == 'last-modified') {
                            item.lastModified = moment(dd[3]).format();
                        } else if (dd[0] == 'summary') {
                            item.summary = item.label = dd[3];
                            if (_this.summaries.indexOf(item.summary) < 0) {
                                _this.summaries.push(item.summary);
                            }
                        }
                    });

                    if (item.exception) {
                        exceptions = exceptions.concat(_this.generateItem(item));
                    } else {
                        if (rrule) {
                            out = out.concat(_this.repeat(item, rrule, exdate));
                        } else {
                            out = out.concat(_this.generateItem(item));
                        }
                    }
                }
            });

            exceptions.forEach(function (ex) {
                for (var i = 0; i < out.length; i++) {
                    if (out[i].uid == ex.uid && out[i].start == ex.exception) {
                        out.splice(i, 1, ex);
                    }
                }
            });

            return out;
        }
    }, {
        key: 'loadIcsFile',
        value: function loadIcsFile(e) {
            var _this2 = this;

            var fr = new FileReader();
            fr.onloadend = function (result) {
                _this2.parseFileContent(ICAL.parse(fr.result));
            };
            fr.readAsText(e.target.files[0]);
        }

        /**
         * Analyse le contenu du fichier pour en extraire un objet structuré.
         */

    }, {
        key: 'parseFileContent',
        value: function parseFileContent(dataString) {
            try {
                var data = ICAL.parse(dataString);
                if (data.length < 2) throw "Bad format";
                var events = this.parse(data);
                return events;
            } catch (error) {
                throw error;
            }
        }
    }]);

    return ICalAnalyser;
}();
return ICalAnalyser;
}));
