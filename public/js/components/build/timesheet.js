define(["exports", "vue", "vue-resource", "mm", "ical", "in-the-box", "papa-parse", "colorpicker", "datepicker"], function (exports, _vue, _vueResource, _mm, _ical, _inTheBox, _papaParse, _colorpicker, _datepicker) {
    "use strict";

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _vue2 = _interopRequireDefault(_vue);

    var _vueResource2 = _interopRequireDefault(_vueResource);

    var _mm2 = _interopRequireDefault(_mm);

    var _ical2 = _interopRequireDefault(_ical);

    var _inTheBox2 = _interopRequireDefault(_inTheBox);

    var _papaParse2 = _interopRequireDefault(_papaParse);

    var _colorpicker2 = _interopRequireDefault(_colorpicker);

    var _datepicker2 = _interopRequireDefault(_datepicker);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    var _Vue$extend;

    function _defineProperty(obj, key, value) {
        if (key in obj) {
            Object.defineProperty(obj, key, {
                value: value,
                enumerable: true,
                configurable: true,
                writable: true
            });
        } else {
            obj[key] = value;
        }

        return obj;
    }

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var _createClass = function () {
        function defineProperties(target, props) {
            for (var i = 0; i < props.length; i++) {
                var descriptor = props[i];
                descriptor.enumerable = descriptor.enumerable || false;
                descriptor.configurable = true;
                if ("value" in descriptor) descriptor.writable = true;
                Object.defineProperty(target, descriptor.key, descriptor);
            }
        }

        return function (Constructor, protoProps, staticProps) {
            if (protoProps) defineProperties(Constructor.prototype, protoProps);
            if (staticProps) defineProperties(Constructor, staticProps);
            return Constructor;
        };
    }();

    _vue2.default.use(_vueResource2.default);

    var EventItem = function () {
        function EventItem(start, end, summary, description) {
            var uid = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;

            _classCallCheck(this, EventItem);

            this.uid = uid;
            this.summary = summary;
            this.description = description;
            this.start = start;
            this.end = end;
            this.updateMM();
        }

        _createClass(EventItem, [{
            key: "updateMM",
            value: function updateMM() {
                this.mmStart = (0, _mm2.default)(this.start);
                this.mmEnd = (0, _mm2.default)(this.end);
            }
        }, {
            key: "durationMinutes",
            get: function get() {
                return Math.ceil((this.mmEnd - this.mmStart) / 1000 / 60);
            }
        }, {
            key: "durationHours",
            get: function get() {
                return this.durationMinutes / 60;
            }
        }, {
            key: "dayStart",
            get: function get() {
                return this.start.slice(0, 10);
            }
        }, {
            key: "dayEnd",
            get: function get() {
                return this.end.slice(0, 10);
            }
        }, {
            key: "hourStart",
            get: function get() {
                return this.mmStart.format('H:mm');
            }
        }, {
            key: "hourEnd",
            get: function get() {
                return this.mmEnd.format('H:mm');
            }
        }, {
            key: "percentStart",
            get: function get() {
                return 100 / 1440 * (this.mmStart.hours() * 60 + this.mmStart.minutes());
            }
        }, {
            key: "percentEnd",
            get: function get() {
                return 100 / 1440 * (this.mmEnd.hours() * 60 + this.mmEnd.minutes());
            }
        }, {
            key: "day",
            get: function get() {
                return 'Le ' + this.mmStart.format('dddd Do MMMM YYYY');
            }
        }, {
            key: "month",
            get: function get() {
                return 'Mois de ' + this.mmStart.format('MMMM YYYY');
            }
        }, {
            key: "year",
            get: function get() {
                return 'En ' + this.mmStart.format('YYYY');
            }
        }, {
            key: "week",
            get: function get() {
                return 'Semaine ' + this.mmStart.format('W, YYYY');
            }
        }]);

        return EventItem;
    }();

    var IcalAnalyser = function () {
        function IcalAnalyser() {
            var ending = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : new Date();

            _classCallCheck(this, IcalAnalyser);

            if (ending instanceof String) ending = new Date(ending);

            if (!(ending instanceof Date)) throw 'Bad usage, date or string required.';

            this.ending = typeof ending == 'string' ? new Date(ending) : ending;
            this.daysString = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
            this.summaries = [];
        }

        _createClass(IcalAnalyser, [{
            key: "generateItem",
            value: function generateItem(item) {

                // POST traitement
                var mmStart = (0, _mm2.default)(item.start);
                var mmEnd = (0, _mm2.default)(item.end);

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
                return [new EventItem(item.start, item.end, item.summary, item.description, item.uid)];
            }
        }, {
            key: "repeat",
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
                            copy.start = (0, _mm2.default)(fromDate).toISOString();
                            copy.end = (0, _mm2.default)(toDate).toISOString();
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
                                _copy.start = (0, _mm2.default)(fromDate).format();
                                _copy.end = (0, _mm2.default)(toDate).format();
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
            key: "parse",
            value: function parse(icsData) {
                var _this = this;

                var out = [],
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
                                var m = _mm2.default.tz(dd[3], dd[1].tzid);
                                exdate.push(m.tz('Europe/Brussels').toISOString());
                            } else if (dd[0] == 'organizer') {
                                item.email = dd[3];
                            } else if (dd[0] == 'description') {
                                item.description = dd[3];
                            } else if (dd[0] == 'dtstart') {
                                var m = _mm2.default.tz(dd[3], dd[1].tzid);
                                item.start = m.tz('Europe/Brussels').toISOString();
                            } else if (dd[0] == 'recurrence-id') {
                                var m = _mm2.default.tz(dd[3], dd[1].tzid);
                                item.exception = m.tz('Europe/Brussels').toISOString();
                            } else if (dd[0] == 'dtend') {
                                var m = _mm2.default.tz(dd[3], dd[1].tzid);
                                item.end = m.tz('Europe/Brussels').toISOString();
                            } else if (dd[0] == 'last-modified') {
                                item.lastModified = (0, _mm2.default)(dd[3]).format();
                            } else if (dd[0] == 'summary') {
                                item.summary = dd[3];
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
        }]);

        return IcalAnalyser;
    }();

    var OscarTimePicker = {
        props: {
            value: String,
            // Intitulé
            placeholder: {
                type: String,
                default: "Heure HH:MM"
            },
            minuteStep: {
                type: Number,
                default: 15
            }
        },

        data: function data() {
            return {
                error: ""
            };
        },


        template: "<div class=\"input-group\">\n          <span class=\"input-group-addon\"><i class=\"icon-clock\"></i> {{ value }}</span>\n          <p class=\"error alert-danger\" v-if=\"error\">\n          {{ error }}\n          </p>\n          <input type=\"time\"\n                  class=\"form-control\"\n                  :placeholder=\"placeholder\"\n                  v-model=\"value\"\n                  @change=\"handlerChange\"\n          />\n      </div>",

        methods: {
            handlerChange: function handlerChange() {
                this.normalizeValue();
                this.$emit('input', this.value);
            },
            handlerKeydown: function handlerKeydown(evt) {
                switch (evt.key) {
                    case 'ArrowUp':
                        this.increment();
                        break;
                }
            },
            extractHourAndMinute: function extractHourAndMinute(str) {
                var hour = 0,
                    minute = 0;

                // Format ?:?
                if (this.value.indexOf(':') > 0) {
                    var split = this.value.split(':');
                    hour = parseInt(split[0]);
                    minute = parseInt(split[1]);
                } else {
                    var data = this.value.replace(/\D/g, '');
                    switch (data.length) {
                        case 1:
                        case 2:
                            hour = data;
                            break;

                        case 3:
                            hour = parseInt(data.substring(0, 2));
                            minute = parseInt(data.substring(2)) * 10;
                            if (hour > 23) {
                                hour = parseInt(data.substring(0, 1));
                                minute = parseInt(data.substring(1));
                            }
                            break;

                        case 4:
                            hour = parseInt(data.substring(0, 2));
                            minute = parseInt(data.substring(2));
                            break;

                        default:
                            this.value = "";
                            this.error = "Invalide entry";
                            return;
                    }
                }

                if (isNaN(hour) || isNaN(minute) || hour > 23 || minute > 59) {
                    throw "Invalide entry";
                }

                var strHour = hour > 9 ? hour : '0' + hour,
                    strMinute = minute > 9 ? minute : '0' + minute;

                return {
                    hour: hour,
                    minute: minute,
                    strHour: strHour,
                    strMinute: strMinute,
                    output: strHour + ':' + strMinute
                };
            },
            normalizeValue: function normalizeValue() {
                try {
                    this.value = this.extractHourAndMinute(this.value).output;
                } catch (err) {
                    this.value = "";
                    this.error = err;
                }
            },
            increment: function increment() {
                var val = this.extractHourAndMinute(this.value);
                val.minute += this.minuteStep;
                if (val.minute >= 60) {
                    val.minute = val.minute - 60;
                    val.hour++;
                }
                this.value = (val.hour > 9 ? val.hour : '0' + val.hour) + ':' + (val.minute > 9 ? val.minute : '0' + val.minute);
                this.handlerChange();
            }
        }
    };

    var Timesheet = _vue2.default.extend((_Vue$extend = {
        components: {
            timepicker: OscarTimePicker
        },
        data: function data() {
            return {
                icsFile: null,
                test: '2:00',
                events: [], // Liste des plages horaires chargées
                timepack: "day", // Groupement des données
                tags: [],
                workPackages: [],
                selectedColor: '',
                selectedEvent: null,
                formEvent: null,
                total: 0,
                tagsColors: null,
                tagslabels: [],
                mode: [{ key: "day", label: "Journalier", format: "dddd Do MMMM YYYY" }, { key: "week", label: "Hebdomadaire", format: "Wo en YYYY" }, { key: "month", label: "Mensuel", format: "MMMM YYYYY" }, { key: "year", label: "Annuel", format: "YYYY" }]
            };
        },


        template: "<div>\n    <h1>Traitement des d\xE9clarations</h1>\n    <transition name=\"popup\">\n        <div class=\"form-wrapper\" v-if=\"formEvent\">\n            <form @submit.prevent=\"ajouterEvent\">\n\n                <div class=\"form-group\">\n                    <label>Jour</label>\n                    <div class=\"input-group\">\n                      <span class=\"input-group-addon\">\n                        <i class=\"icon-calendar\"></i>\n                        {{ formEvent.day }}\n                      </span>\n\n                      <input type=\"date\"\n                            class=\"form-control\"\n                            data-provide=\"datepicker\"\n                            data-date-format=\"yyyy-mm-dd\"\n                            placeholder=\"Jour\"\n                            v-model=\"formEvent.day\"\n                            @change=\"handleDateChange\"\n                            @input=\"handleDateChange\"\n                            @blur=\"handleDateChange\"\n                            />\n                    </div>\n                </div>\n\n                <div class=\"row\">\n                    <div class=\"col-md-6\">\n                        <label>De</label>\n                        <timepicker v-model=\"formEvent.startHour\"></timepicker>\n                    </div>\n                    <div class=\"col-md-6\">\n                        <label>\xC0</label>\n                        <timepicker v-model=\"formEvent.endHour\"></timepicker>\n                    </div>\n                </div>\n\n                <div class=\"form-group\">\n                    <label>Project ou Lot de travail</label>\n                    <div class=\"input-group\">\n                      <span class=\"input-group-addon\">\n                        <i class=\"icon-archive\"></i>\n                      </span>\n                      <input type=\"text\"\n                            class=\"form-control\"\n                            id=\"inputGroupSuccess3\"\n                            v-model=\"formEvent.summary\"\n                            aria-describedby=\"inputGroupSuccess3Status\" />\n                    </div>\n                </div>\n\n                <div class=\"form-group\">\n                    <label>Commentaire</label>\n                    <textarea class=\"form-control\" v-model=\"formEvent.description\"></textarea>\n                </div>\n                <nav class=\"oscar-buttons\">\n                    <button @click.prevent=\"formEvent = null\" class=\"btn btn-default\">Annuler</button>\n                    <button type=\"submit\" class=\"btn btn-primary\">Enregistrer</button>\n                </nav>\n\n            </form>\n        </div>\n    </transition>\n\n     <p>Ajouter une plage : <button class=\"btn btn-xs- btn-primary\" @click.prevent=\"nouveau\">Ajouter un plage</button></p>\n\n\n    <p>Selectionnez un fichier ICS \xE0 importer</p>\n\n    <input type=\"file\" @change=\"loadIcsFile\" v-model=\"icsFile\">\n\n    <div class=\"calendar\" v-show=\"events.length\">\n        <h3>\n            <i class=\"icon-tags\"></i>\n            L\xE9gendes\n        </h3>\n        <p>Voici la liste des diff\xE9rents intitul\xE9s trouv\xE9s dans le calendrier : </p>\n        <div class=\"legende\">\n            <header>Plages :</header>\n            <pre>{{ tags }}</pre>\n            <span v-for=\"tag, summary in tags\" class=\"plage-legende\"\n                :style=\"{ background: tag }\"\n                @click=\"currentColor(summary)\">\n                {{ summary }}\n                <a href=\"\" @click.prevent=\"removeAll(summary)\">Supprimer</a>\n            </span>\n            <div class=\"colorselector\" v-show=\"selectedColor\">\n                <div class=\"input-group colorpicker-component color-picker\">\n                    <input type=\"text\" value=\"selectedColor\" class=\"form-control\" @change=\"updateCurrentColor\"/>\n                    <span class=\"input-group-addon\"><i></i></span>\n                </div>\n            </div>\n        </div>\n        <hr>\n        <h3>\n            <i class=\" icon-calendar-outlilne\"></i>\n            Contenu du calendrier charg\xE9\n        </h3>\n        <p>Voici les plages horaires issues du calendrier</p>\n        <article class=\"cal-day\" v-for=\"plages, label in byDays\">\n            <strong class=\"label\">{{ label }}</strong>\n            <div class=\"plages\">\n                <div v-for=\"plage in plages\"\n                        :style=\"{ background: getColor(plage.summary),left: plage.percentStart +'%', width: (plage.percentEnd - plage.percentStart) +'%' }\"\n                        class=\"plage\"\n                        @contexmenu=\"handlerContext($event, plage)\">\n                    <div class=\"intitule\">\n                       <small>{{ plage.hourStart }} - {{ plage.hourEnd }}</small>\n                       <span class=\"recursive\" v-if=\"plage.recursive\">R</span>\n                       <span class=\"exception\" v-if=\"plage.exception\">E</span>\n                    </div>\n                    <div class=\"details\">\n                        <h3>{{ plage.summary }}</h3>\n                        <strong>{{ label }}</strong><br>\n                        ID <strong>{{ plage.uid }}</strong><span v-if=\"plage.exception\"> (exception de la s\xE9rie {{ plage.exception }})</span><br>\n                        Dur\xE9e : <strong>{{ plage.durationHours }} heure(s)</strong><br>\n                        de : <strong>{{ plage.hourStart }}</strong> \xE0 <strong>{{ plage.hourEnd }}</strong><br>\n                        <p v-if=\"plage.description\">{{ plage.description }}</p>\n                        <a href=\"#\" @click.prevent=\"remove(plage)\">Supprimer ce cr\xE9neaux</a>\n                        <a href=\"#\" @click.prevent=\"editer(plage)\">Editer ce cr\xE9neaux</a>\n                    </div>\n                </div>\n            </div>\n        </article>\n    </div>\n    <section class=\"events\" v-show=\"events.length\">\n        <h3>\n            <i class=\"icon-calculator\"></i>\n            Analyse du temps d\xE9clar\xE9\n        </h3>\n        Mode\n        <select name=\"\" id=\"\" v-model=\"timepack\">\n            <option v-for=\"m in mode\" :value=\"m.key\">{{ m.label }}</option>\n        </select>\n\n        <table class=\"table timesheet\">\n            <thead>\n                <tr>\n                    <th>P\xE9riode</th>\n                    <th v-for=\"wp in workPackages\">\n                        <strong>{{ wp }}</strong>\n                    </th>\n                    <th>Hors WP</th>\n                    <th>cr\xE9neaux</th>\n                    <th>Total</th>\n                </tr>\n            </thead>\n            <tr v-for=\"row in structuredEvents\">\n                <th>{{ row.label }}</th>\n                <td v-for=\"wp in workPackages\">\n                    <span class=\"hours\">{{ row.cols[wp] }}</span>\n                </td>\n\n                <td>{{ row.cols.other }}</td>\n                <td>{{ row.cols.slots }}</td>\n                <td>{{ row.cols.total }}</td>\n\n            </tr>\n            <tfoot>\n                <tr>\n                    <th :colspan=\"workPackages.length + 2\">\n                        Total\n                    </th>\n                    <th>\n                        {{ total }}\n                    </th>\n                </tr>\n            </tfoot>\n        </table>\n        <a class=\"btn btn-primary\" @click=\"toBase64\">T\xE9l\xE9charger au format CSV</a>\n    </section>\n    </div>"
    }, _defineProperty(_Vue$extend, "components", {
        timepicker: OscarTimePicker
    }), _defineProperty(_Vue$extend, "computed", {
        referenceNewTag: function referenceNewTag(tag) {
            console.log('Reference new tag', tag);
        },
        tags: function tags() {
            var _this2 = this;

            console.log('Mise à jour des tags');
            var tagsList = [];
            this.events.forEach(function (event) {
                if (tagsList.indexOf(event.summary) < 0) {
                    tagsList.push(event.summary);
                }
            });
            if (!this.tagsColors || !this.tagsColors.length < tagsList.length) {
                this.tagsColors = _inTheBox2.default.Color.generateColor(tagsList.length);
            }

            var tags = {};
            tagsList.forEach(function (summary, i) {
                tags[summary] = _this2.tagsColors[i];
            });
            return tags;
        },
        currentTimeFormat: function currentTimeFormat() {
            for (var i = 0; i < this.mode.length; i++) {
                if (this.mode[i] == this.timepack) {
                    return this.mode[i].format;
                }
            }
            return 'error';
        },
        byDays: function byDays() {
            var byDays = {};
            this.events.forEach(function (event) {
                var currentDay = event.day;
                if (!byDays[currentDay]) {
                    byDays[currentDay] = [];
                }
                byDays[currentDay].push(event);
            });
            return byDays;
        },
        structuredEvents: function structuredEvents() {
            var _this3 = this;

            var data = [];
            var row = null;

            this.total = 0;

            this.events.forEach(function (event) {
                var currentRow = event[_this3.timepack];
                if (row == null || row.label != currentRow) {
                    if (row) {
                        data.push(row);
                    }

                    row = {
                        label: currentRow,
                        cols: {}
                    };

                    _this3.workPackages.forEach(function (wp) {
                        row.cols[wp] = 0;
                    });

                    row.cols.other = 0;
                    row.cols.slots = 0;
                    row.cols.total = 0;
                }
                if (row.cols[event.summary] != undefined) {
                    row.cols[event.summary] += event.durationHours;
                } else {
                    row.cols.other += event.durationHours;
                }
                row.cols.total += event.durationHours;
                _this3.total += event.durationHours;
                row.cols.slots++;
            });

            if (row) {
                data.push(row);
            }
            return data;
        }
    }), _defineProperty(_Vue$extend, "filters", {
        date: function date(value) {
            return (0, _mm2.default)(value).format('Do MMMM YYYY, H:mm');
        }
    }), _defineProperty(_Vue$extend, "methods", {
        handleDateChange: function handleDateChange(evt) {
            this.formEvent.day = evt.target.value;
        },
        editer: function editer(event) {
            this.selectedEvent = event;
            this.formEvent = {
                day: event.dayStart,
                startHour: event.hourStart,
                endHour: event.hourEnd,
                summary: event.summary,
                description: event.description
            };
        },
        nouveau: function nouveau() {
            this.selectedEvent = null;
            var today = (0, _mm2.default)().format('YYYY-MM-DD');
            this.formEvent = {
                day: today,
                startHour: '09:00',
                endHour: '12:00',
                summary: 'NEW SUMMARY',
                description: ""
            };
        },
        ajouterEvent: function ajouterEvent() {
            var hstart = this.formEvent.startHour;
            if (hstart.length == 4) {
                hstart = '0' + hstart;
            }

            var hend = this.formEvent.endHour;
            if (hend.length == 4) {
                hend = '0' + hend;
            }

            var tz = (0, _mm2.default)().tz(_mm2.default.tz.guess()).format('Z'),
                start = this.formEvent.day + 'T' + hstart + ':00' + tz,
                end = this.formEvent.day + 'T' + hend + ':00' + tz;

            if (this.selectedEvent) {
                console.log("MAJ");
                this.selectedEvent.start = start;
                this.selectedEvent.end = end;
                this.selectedEvent.summary = this.formEvent.summary;
                this.selectedEvent.updateMM();
                this.eventsSort(this.events);
            } else {
                console.log("AJOUT");
                var event = new EventItem(start, end, this.formEvent.summary, this.formEvent.description);
                this.referenceNewTag(event.summary);
                this.events.push(event);
                this.events = this.eventsSort(this.events);
            }
            this.selectedEvent = null;
            this.formEvent = null;
        },
        eventsSort: function eventsSort(events) {
            events.sort(function (a, b) {
                if (a.dayStart < b.dayStart) return -1;
                if (a.dayStart > b.dayStart) return 1;
                return 0;
            });
            return events;
        },
        currentColor: function currentColor(summary) {
            this.selectedColor = summary;
            this.colorPicker.colorpicker('setValue', this.tags[summary]);
        },
        getColor: function getColor(summary) {
            return this.tags[summary];
        },
        updateCurrentColor: function updateCurrentColor() {
            if (this.selectedColor && this.tags[this.selectedColor]) {
                this.$set(this.tags, this.selectedColor, this.colorPicker.colorpicker('getValue'));
            }
        },
        handlerKeypress: function handlerKeypress(e) {
            console.log(e);
        },
        removeAll: function removeAll(summary) {
            if (this.selectedColor == summary) {
                this.selectedColor = null;
            }
            var newEvents = [];
            this.events.forEach(function (event) {
                if (event.summary != summary) {
                    newEvents.push(event);
                }
            });
            this.events = newEvents;
        },
        toBase64: function toBase64() {
            var _this4 = this;

            var data = [];
            var headers = ['année', 'mois', 'jour', 'de', 'à'];
            headers = headers.concat(this.workPackages);
            headers.push('Hors WP');

            data.push(headers);

            this.events.forEach(function (event) {
                var date = (0, _mm2.default)(event.start);
                var dateEnd = (0, _mm2.default)(event.end);
                var row = [date.format('YYYY'), date.format('M'), date.format('D'), date.format('HH:mm'), dateEnd.format('HH:mm')];
                var inWP = false;
                _this4.workPackages.forEach(function (wp) {
                    if (event.summary == wp) {
                        row.push(event.durationHours.toString().replace('.', ','));
                        inWP = true;
                    } else {
                        row.push(0);
                    }
                });
                if (inWP == false) {
                    row.push(event.durationHours.toString().replace('.', ','));
                } else {
                    row.push(0);
                }
                data.push(row);
            });

            var str = _papaParse2.default.unparse({
                data: data,
                quotes: true,
                delimiter: ",",
                newline: "\r\n"
            });
            var content = 'data:application/octet-stream;base64,' + btoa(unescape(encodeURIComponent(str)));
            window.open(content);
        },
        handlerContext: function handlerContext($event, plage) {
            console.log(arguments);
        },
        toCSV: function toCSV() {},
        remove: function remove(item) {
            console.log('delete', item, this.events.indexOf(item));
            if (this.events.indexOf(item) > -1) this.events.splice(this.events.indexOf(item), 1);
        },
        send: function send() {
            this.http.post(this.url, JSON.parse(JSON.stringify(this.events))).then(function (res) {
                console.log(res);
            }, function (err) {
                console.log(err);
                flashMessage(err.body);
            });
        },
        loadIcsFile: function loadIcsFile(e) {
            var _this5 = this;

            var fr = new FileReader();
            fr.onloadend = function (result) {
                _this5.parseFileContent(ICAL.parse(fr.result));
            };
            fr.readAsText(e.target.files[0]);
        },
        parseFileContent: function parseFileContent(data) {
            var _this6 = this;

            var events = [];
            if (data.length < 2) throw "Bad format";
            var d = data[2];

            var warnings = [];

            var parser = new IcalAnalyser();
            events = parser.parse(data);

            events.forEach(function (event) {
                event.tag = _this6.tags[event.summary];
            });

            this.events = this.eventsSort(events);
        }
    }), _defineProperty(_Vue$extend, "mounted", function mounted() {
        console.log("MOUNTED", $(this.$el).find('.color-picker'));
        this.colorPicker = $(this.$el).find('.color-picker').colorpicker();
        this.colorPicker.on('changeColor', this.updateCurrentColor);

        this.tagsColors = _inTheBox2.default.Color.generateColor(20);
        console.log(this.tagsColors);
    }), _Vue$extend));

    exports.default = Timesheet;
});