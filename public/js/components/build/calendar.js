;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['moment', 'ICalAnalyser', 'EventDT'], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory(require('moment'), require('ICalAnalyser'), require('EventDT'));
  } else {
    root.Calendar = factory(root.moment, root.ICalAnalyser, root.EventDT);
  }
}(this, function(moment, ICalAnalyser, EventDT) {
"use strict";

var _methods;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _EventDT = require("EventDT");

var _EventDT2 = _interopRequireDefault(_EventDT);

var _momentTimezone = require("moment-timezone");

var _momentTimezone2 = _interopRequireDefault(_momentTimezone);

var _ICalAnalyser = require("ICalAnalyser");

var _ICalAnalyser2 = _interopRequireDefault(_ICalAnalyser);

var _vueResource = require("vue-resource");

var _vueResource2 = _interopRequireDefault(_vueResource);

var _Datepicker = require("Datepicker");

var _Datepicker2 = _interopRequireDefault(_Datepicker);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

_momentTimezone2.default.locale('fr');

var colorLabels = {};
var colorIndex = 0;
var colorpool = ['#fcdc80', '#a6cef8', '#9fd588', '#fb90bb', '#e5fbed', '#99a0ce', '#bca078', '#f3cafd', '#d9f4c1', '#60e3bb', '#f2c7f5', '#f64bc0', '#ffc1b2', '#fc9175', '#d7fc74', '#e3d7f8', '#9ffab3', '#d6cbac', '#4dd03c', '#f8f3be'];

var _colorLabel = function _colorLabel(label) {
    if (!colorLabels[label]) {
        colorLabels[label] = colorpool[++colorIndex];
        colorIndex = colorIndex % colorpool.length;
    }
    return colorLabels[label];
};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// MODEL

var CalendarDatas = function () {
    function CalendarDatas() {
        _classCallCheck(this, CalendarDatas);

        this.state = 'week';
        this.events = [];
        this.newID = 1;
        this.transmission = "";
        this.importInProgress = false;
        this.importedEvents = [];
        this.eventEditData = {};
        this.eventEditDataVisible = false;
        this.currentDay = (0, _momentTimezone2.default)();
        this.eventEdit = null;
        this.copyWeekData = null;
        this.copyDayData = null;
        this.generatedId = 0;
        this.defaultLabel = "";
        this.errors = [];
        this.defaultDescription = "";
        this.labels = [];
    }

    _createClass(CalendarDatas, [{
        key: "copyDay",
        value: function copyDay(dt) {
            var _this = this;

            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach(function (event) {
                var dayRef = (0, _momentTimezone2.default)(event.start).format('MMMM D YYYY');
                if (dayRef == dDay) {
                    _this.copyDayData.push({
                        startHours: event.mmStart.hour(),
                        startMinutes: event.mmStart.minute(),
                        endHours: event.mmEnd.hour(),
                        endMinutes: event.mmEnd.minute(),
                        label: event.label,
                        description: event.description
                    });
                }
            });
        }
        ////////////////////////////////////////////////////////////////////////

    }, {
        key: "copyCurrentWeek",
        value: function copyCurrentWeek() {
            var _this2 = this;

            this.copyWeekData = [];
            this.events.forEach(function (event) {
                if (_this2.inCurrentWeek(event)) {
                    _this2.copyWeekData.push({
                        day: event.mmStart.day(),
                        startHours: event.mmStart.hour(),
                        startMinutes: event.mmStart.minute(),
                        endHours: event.mmEnd.hour(),
                        endMinutes: event.mmEnd.minute(),
                        label: event.label,
                        description: event.description
                    });
                }
            });
        }
    }, {
        key: "pasteDay",
        value: function pasteDay(day) {
            if (this.copyDayData) {
                var create = [];

                this.copyDayData.forEach(function (event) {
                    var start = (0, _momentTimezone2.default)(day.format());
                    start.hour(event.startHours).minute(event.startMinutes);

                    var end = (0, _momentTimezone2.default)(day.format());
                    end.hour(event.endHours).minute(event.endMinutes);

                    create.push({
                        id: null,
                        label: event.label,
                        mmStart: start,
                        start: start.format(),
                        mmEnd: end,
                        end: end.format(),
                        description: event.description
                    });
                });

                return create;
            }
            return null;
        }
    }, {
        key: "pasteWeek",
        value: function pasteWeek() {
            var _this3 = this;

            if (this.copyWeekData) {
                var create = [];
                this.copyWeekData.forEach(function (event) {
                    var start = (0, _momentTimezone2.default)(_this3.currentDay);
                    start.day(event.day).hour(event.startHours).minute(event.startMinutes);

                    var end = (0, _momentTimezone2.default)(_this3.currentDay);
                    end.day(event.day).hour(event.endHours).minute(event.endMinutes);

                    create.push({
                        id: null,
                        label: event.label,
                        mmStart: start,
                        start: start.format(),
                        mmEnd: end,
                        end: end.format(),
                        description: event.description
                    });
                });
                return create;
            }
            return null;
        }
    }, {
        key: "previousWeek",
        value: function previousWeek() {
            this.currentDay = (0, _momentTimezone2.default)(this.currentDay).add(-1, 'week');
        }
    }, {
        key: "nextWeek",
        value: function nextWeek() {
            this.currentDay = (0, _momentTimezone2.default)(this.currentDay).add(1, 'week');
        }
    }, {
        key: "newEvent",
        value: function newEvent(evt) {
            evt.id = this.generatedId++;
            this.events.push(evt);
        }
    }, {
        key: "inCurrentWeek",
        value: function inCurrentWeek(event) {
            return event.inWeek(this.currentDay.year(), this.currentDay.week());
        }
    }, {
        key: "sync",
        value: function sync(datas) {
            for (var i = 0; i < datas.length; i++) {
                var local = this.getEventById(datas[i].id);
                if (local) {
                    local.sync(datas[i]);
                } else {
                    this.addNewEvent(datas[i].id, datas[i].label, datas[i].start, datas[i].end, datas[i].description, datas[i].credentials, datas[i].status, datas[i].owner);
                }
            }
        }
    }, {
        key: "getEventById",
        value: function getEventById(id) {
            for (var i = 0; i < this.events.length; i++) {
                if (this.events[i].id == id) {
                    return this.events[i];
                }
            }
            return null;
        }
    }, {
        key: "addNewEvent",
        value: function addNewEvent(id, label, start, end, description) {
            var credentials = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : undefined;
            var status = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : "draft";
            var owner = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : "";

            this.events.push(new _EventDT2.default(id, label, start, end, description, credentials, status, owner));
        }
    }, {
        key: "listEvents",
        get: function get() {
            _EventDT2.default.sortByStart(this.events);
            return this.events;
        }
    }, {
        key: "today",
        get: function get() {
            return (0, _momentTimezone2.default)();
        }
    }, {
        key: "firstEvent",
        get: function get() {}
    }, {
        key: "lastEvent",
        get: function get() {}
    }, {
        key: "currentYear",
        get: function get() {
            return this.currentDay.format('YYYY');
        }
    }, {
        key: "currentMonth",
        get: function get() {
            return this.currentDay.format('MMMM');
        }
    }, {
        key: "currentWeekKey",
        get: function get() {
            return this.currentDay.format('YYYY-W');
        }
    }, {
        key: "currentWeekDays",
        get: function get() {
            var days = [],
                day = (0, _momentTimezone2.default)(this.currentDay.startOf('week'));

            for (var i = 0; i < 7; i++) {
                days.push((0, _momentTimezone2.default)(day.format()));
                day.add(1, 'day');
            }
            return days;
        }
    }]);

    return CalendarDatas;
}();

var store = new CalendarDatas();

var TimeEvent = {

    template: "<div class=\"event\" :style=\"css\"\n            @mouseleave=\"handlerMouseOut\"\n            @mousedown=\"handlerMouseDown\"\n            :title=\"event.label\"\n            :class=\"{'event-moving': moving, 'event-selected': selected, 'event-locked': isLocked, 'status-info': isInfo, 'status-draft': isDraft, 'status-send' : isSend, 'status-valid': isValid, 'status-reject': isReject}\">\n        <div class=\"label\" data-uid=\"UID\">\n          {{ event.label }}\n        </div>\n        <small>Dur\xE9e : <strong>{{ labelDuration }}</strong> heure(s)</small>\n        <div class=\"description\">\n            <p v-if=\"withOwner\">D\xE9clarant <strong>{{ event.owner }}</strong></p>\n          {{ event.description }}\n        </div>\n\n        <nav class=\"admin\">\n            <a href=\"#\" @mousedown.stop.prevent=\"\" @click.stop.prevent=\"$emit('editevent')\" v-if=\"event.editable\">\n                <i class=\"icon-pencil-1\"></i>\n                Modifier</a>\n            <a href=\"#\" @mousedown.stop.prevent=\"\" @click.stop.prevent=\"$emit('deleteevent')\" v-if=\"event.deletable\">\n                <i class=\"icon-trash-empty\"></i>\n                Supprimer</a>\n\n            <a href=\"#\" @mousedown.stop.prevent=\"\" @click.stop.prevent=\"$emit('submitevent')\" v-if=\"event.sendable\">\n                <i class=\"icon-right-big\"></i>\n                Soumettre</a>\n\n            <a href=\"#\" @mousedown.stop.prevent=\"\" @click.stop.prevent=\"$emit('validateevent')\" v-if=\"event.validable\">\n                <i class=\"icon-right-big\"></i>\n                Valider</a>\n            <a href=\"#\" @mousedown.stop.prevent=\"\" @click.stop.prevent=\"$emit('rejectevent')\" v-if=\"event.validable\">\n                <i class=\"icon-right-big\"></i>\n                Rejeter</a>\n        </nav>\n\n        <div class=\"bottom-handler\" v-if=\"event.editable\"\n            @mouseleave=\"handlerEndMovingEnd\"\n            @mousedown.prevent.stop=\"handlerStartMovingEnd\">\n            <span>===</span>\n        </div>\n\n        <time class=\"time start\">{{ labelStart }}</time>\n        <time class=\"time end\">{{ labelEnd }}</time>\n      </div>",

    props: ['event', 'weekDayRef', 'withOwner'],

    data: function data() {
        return {
            selected: false,
            moving: false,
            interval: null,
            movingBoth: true,
            change: false,
            labelStart: "",
            labelEnd: "",
            labelDuration: "",
            startX: 0
        };
    },


    filters: {
        hour: function hour(mm) {
            return mm.format('H:mm');
        },
        dateFull: function dateFull(mm) {
            return mm.format('D MMMM YYYY, h:mm');
        }
    },

    computed: {
        css: function css() {
            var marge = 0;
            var sizeless = 0;
            if (this.event.intersect > 0) {
                sizeless = 3;
                marge = sizeless / this.event.intersect * this.event.intersectIndex;
            }
            return {
                height: this.pixelEnd - this.pixelStart + 'px',
                background: this.colorLabel,
                position: "absolute",
                top: this.pixelStart + 'px',
                width: 100 / 7 - 1 - sizeless + "%",
                left: (this.weekDay - 1) * 100 / 7 + marge + "%"
            };
        },


        ///////////////////////////////////////////////////////////////// STATUS
        isDraft: function isDraft() {
            return this.event.status == "draft";
        },
        isSend: function isSend() {
            return this.event.status == "send";
        },
        isValid: function isValid() {
            return this.event.status == "valid";
        },
        isReject: function isReject() {
            return this.event.status == "reject";
        },
        isInfo: function isInfo() {
            return this.event.status == "info";
        },
        colorLabel: function colorLabel() {
            return _colorLabel(this.event.label);
        },
        isLocked: function isLocked() {
            return !this.event.editable;
        },
        dateStart: function dateStart() {
            return (0, _momentTimezone2.default)(this.event.start);
        },
        dateEnd: function dateEnd() {
            return (0, _momentTimezone2.default)(this.event.end);
        },
        pixelStart: function pixelStart() {
            return this.dateStart.hour() * 40 + 40 / 60 * this.dateStart.minutes();
        },
        pixelEnd: function pixelEnd() {
            return this.dateEnd.hour() * 40 + 40 / 60 * this.dateEnd.minutes();
        },
        weekDay: function weekDay() {
            return this.dateStart.day();
        }
    },

    watch: {
        'event.start': function eventStart() {
            this.labelStart = this.dateStart.format('H:mm');
        },
        'event.end': function eventEnd() {
            this.labelEnd = this.dateEnd.format('H:mm');
        }
    },

    methods: {
        move: function move(event) {
            if (this.event.editable && event.movementY != 0) {
                this.change = true;

                var currentTop = parseInt(this.$el.style.top);
                var currentHeight = parseInt(this.$el.style.height);

                if (this.movingBoth) {
                    currentTop += event.movementY;
                    this.$el.style.top = currentTop + "px";
                } else {
                    currentHeight += event.movementY;
                    this.$el.style.height = currentHeight + "px";
                }

                var dtUpdate = this.topToStart();
                this.labelDuration = dtUpdate.duration;
                this.labelStart = dtUpdate.startLabel;
                this.labelEnd = dtUpdate.endLabel;
            }
        },
        handlerEndMovingEnd: function handlerEndMovingEnd() {
            if (this.movingBoth) {
                this.movingBoth = false;
            }
        },
        handlerStartMovingEnd: function handlerStartMovingEnd(e) {
            this.movingBoth = false;
            this.startMoving(e);
        },
        startMoving: function startMoving(e) {
            if (this.event.editable) {
                this.startX = e.clientX;
                this.selected = true;
                this.moving = true;
                this.$el.addEventListener('mousemove', this.move);
                this.$el.addEventListener('mouseup', this.handlerMouseUp);
            }
        },
        handlerMouseDown: function handlerMouseDown(e) {
            this.movingBoth = true;
            this.startMoving(e);
        },
        handlerMouseUp: function handlerMouseUp(e) {
            if (this.event.editable) {
                this.moving = false;
                this.$el.removeEventListener('mousemove', this.move);

                var dtUpdate = this.topToStart();

                this.event.start = this.dateStart.hours(dtUpdate.startHours).minutes(dtUpdate.startMinutes).format();

                this.event.end = this.dateEnd.hours(dtUpdate.endHours).minutes(dtUpdate.endMinutes).format();
                if (this.change) {
                    this.change = false;
                    this.$emit('savemoveevent', this.event);
                }
            }
        },
        handlerMouseOut: function handlerMouseOut(e) {
            this.handlerMouseUp();
        },
        roundMinutes: function roundMinutes(minutes) {
            return Math.floor(60 / 40 * minutes / 15) * 15;
        },
        formatZero: function formatZero(int) {
            return int < 10 ? '0' + int : int;
        },


        ////////////////////////////////////////////////////////////////////////
        topToStart: function topToStart() {
            var round = 40 / 12;

            var minutesStart = parseInt(this.$el.style.top);
            var minutesEnd = minutesStart + parseInt(this.$el.style.height);

            var startHours = Math.floor(minutesStart / 40);
            var startMinutes = this.roundMinutes(minutesStart - startHours * 40);

            var endHours = Math.floor(minutesEnd / 40);
            var endMinutes = this.roundMinutes(minutesEnd - endHours * 40);

            return {
                startHours: startHours,
                startMinutes: startMinutes,
                endHours: endHours,
                endMinutes: endMinutes,
                duration: formatDuration((endHours * 60 + endMinutes - (startHours * 60 + startMinutes)) * 60),
                startLabel: this.formatZero(startHours) + ':' + this.formatZero(startMinutes),
                endLabel: this.formatZero(endHours) + ':' + this.formatZero(endMinutes)
            };
        }
    },

    mounted: function mounted() {
        this.labelStart = this.dateStart.format('H:mm');
        this.labelEnd = this.dateEnd.format('H:mm');
        this.labelDuration = formatDuration(this.dateEnd.unix() - this.dateStart.unix());
    }
};

var formatDuration = function formatDuration(milliseconde) {
    var h = Math.floor(milliseconde / 60 / 60);
    var m = (milliseconde - h * 60 * 60) / 60;
    return h + (m ? 'h' + m : '');
};

var WeekView = {
    data: function data() {
        return store;
    },


    props: {
        'withOwner': { default: false },
        'createNew': { default: false },
        'pas': { default: 15 }
    },

    components: {
        'timeevent': TimeEvent
    },

    template: "<div class=\"calendar calendar-week\">\n    <div class=\"meta\">\n        <a href=\"#\" @click=\"previousWeek\">\n            <i class=\"icon-left-big\"></i>\n        </a>\n        <h3>\n            Semaine {{ currentWeekNum}}, {{ currentMonth }} {{ currentYear }}\n            <nav class=\"copy-paste\" v-if=\"createNew\">\n                <span href=\"#\" @click=\"copyCurrentWeek\"><i class=\"icon-docs\"></i></span>\n                <span href=\"#\" @click=\"pasteWeek\"><i class=\"icon-paste\"></i></span>\n                <span href=\"#\" @click=\"$emit('submitall', 'send', 'week')\"><i class=\"icon-right-big\"></i></span>\n            </nav>\n        </h3>\n       <a href=\"#\" @click=\"nextWeek\">\n            <i class=\"icon-right-big\"></i>\n       </a>\n    </div>\n\n    <header class=\"line\">\n        <div class=\"content-full\" style=\"margin-right: 12px\">\n            <div class=\"labels-time\">\n                {{currentYear}}\n            </div>\n            <div class=\"events\">\n                <div class=\"cell cell-day day day-1\" :class=\"{today: isToday(day)}\" v-for=\"day in currentWeekDays\">\n                    {{ day.format('dddd D') }}\n                    <nav class=\"copy-paste\" v-if=\"createNew\">\n                        <span href=\"#\" @click=\"copyDay(day)\"><i class=\"icon-docs\"></i></span>\n                        <span href=\"#\" @click=\"pasteDay(day)\"><i class=\"icon-paste\"></i></span>\n                    </nav>\n                </div>\n            </div>\n        </div>\n    </header>\n\n    <div class=\"content-wrapper\">\n        <div class=\"content-full\">\n          <div class=\"labels-time\">\n            <div class=\"unit timeinfo\" v-for=\"time in 24\">{{time-1}}:00</div>\n          </div>\n          <div class=\"events\">\n\n              <div class=\"cell cell-day day\" v-for=\"day in 7\">\n                <div class=\"hour houroff\" v-for=\"time in 6\">&nbsp;</div>\n                <div class=\"hour\" v-for=\"time in 16\"\n                    @mouseup=\"handlerMouseUp\"\n                    @mousedown=\"createEvent(day, time+5)\"\n                    @dblclick=\"createEvent(day, time+5)\">&nbsp;</div>\n                <div class=\"hour houroff\" v-for=\"time in 2\">&nbsp;</div>\n              </div>\n              <div class=\"content-events\">\n                <timeevent v-for=\"event in weekEvents\"\n                    :with-owner=\"withOwner\"\n                    :weekDayRef=\"currentDay\"\n                    v-if=\"inCurrentWeek(event)\"\n                    @deleteevent=\"$emit('deleteevent', event)\"\n                    @editevent=\"$emit('editevent', event)\"\n                    @submitevent=\"$emit('submitevent', event)\"\n                    @validateevent=\"$emit('validateevent', event)\"\n                    @savemoveevent=\"handlerSaveMove\"\n                    :event=\"event\"\n                    :key=\"event.id\"></timeevent>\n              </div>\n          </div>\n        </div>\n    </div>\n\n    <footer class=\"line\">\n      FOOTER\n    </footer>\n    </div>",

    computed: {
        currentYear: function currentYear() {
            return this.currentDay.format('YYYY');
        },
        currentMonth: function currentMonth() {
            return this.currentDay.format('MMMM');
        },
        currentWeekKey: function currentWeekKey() {
            return this.currentDay.format('YYYY-W');
        },
        currentWeekNum: function currentWeekNum() {
            return this.currentDay.format('W');
        },
        currentWeekDays: function currentWeekDays() {
            var days = [],
                day = (0, _momentTimezone2.default)(this.currentDay.startOf('week'));

            for (var i = 0; i < 7; i++) {
                days.push((0, _momentTimezone2.default)(day.format()));
                day.add(1, 'day');
            }
            return days;
        },
        weekEvents: function weekEvents() {
            var weekEvents = [];
            this.events.forEach(function (event) {
                if (store.inCurrentWeek(event)) {
                    event.intersect = 0;
                    event.intersectIndex = 0;
                    weekEvents.push(event);
                }
            });

            // Détection des collapses
            for (var i = 0; i < weekEvents.length; i++) {
                var u1 = weekEvents[i];

                for (var j = i + 1; j < weekEvents.length; j++) {
                    var u2 = weekEvents[j];
                    if (u2 == u1) {
                        continue;
                    }
                    if (u2.overlap(u1)) {
                        u1.intersect++;
                        u2.intersect++;
                        u2.intersectIndex++;
                    }
                }
            }
            return weekEvents;
        }
    },

    methods: {
        handlerSaveMove: function handlerSaveMove(event) {
            this.$emit('savemoveevent', event);
        },
        handlerMouseUp: function handlerMouseUp() {
            console.log('mouse up');
        },
        handlerMouseDown: function handlerMouseDown() {
            console.log('mouse down');
        },
        createEvent: function createEvent(day, time) {
            var start = (0, _momentTimezone2.default)(this.currentDay).day(day).hour(time);
            var end = (0, _momentTimezone2.default)(start).add(2, 'hours');
            var newEvent = new _EventDT2.default(null, this.defaultLabel, start.format(), end.format(), this.defaultDescription, { editable: true, deletable: true });
            this.$emit('createevent', newEvent);
        },
        copyDay: function copyDay(dt) {
            var _this4 = this;

            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach(function (event) {
                var dayRef = (0, _momentTimezone2.default)(event.start).format('MMMM D YYYY');
                if (dayRef == dDay) {
                    _this4.copyDayData.push({
                        startHours: event.mmStart.hour(),
                        startMinutes: event.mmStart.minute(),
                        endHours: event.mmEnd.hour(),
                        endMinutes: event.mmEnd.minute(),
                        label: event.label,
                        description: event.description
                    });
                }
            });
        },
        copyCurrentWeek: function copyCurrentWeek() {
            var _this5 = this;

            this.copyWeekData = [];
            this.events.forEach(function (event) {
                if (_this5.inCurrentWeek(event)) {
                    _this5.copyWeekData.push({
                        day: event.mmStart.day(),
                        startHours: event.mmStart.hour(),
                        startMinutes: event.mmStart.minute(),
                        endHours: event.mmEnd.hour(),
                        endMinutes: event.mmEnd.minute(),
                        label: event.label,
                        description: event.description
                    });
                }
            });
        },
        pasteDay: function pasteDay(day) {
            if (this.copyDayData) {
                this.$emit('createpack', store.pasteDay(day));
            }
        },
        pasteWeek: function pasteWeek() {
            if (this.copyWeekData) {
                this.$emit('createpack', store.pasteWeek());
            }
        },
        previousWeek: function previousWeek() {
            this.currentDay = (0, _momentTimezone2.default)(this.currentDay).add(-1, 'week');
        },
        nextWeek: function nextWeek() {
            this.currentDay = (0, _momentTimezone2.default)(this.currentDay).add(1, 'week');
        },
        isToday: function isToday(day) {
            return day.format('YYYY-MM-DD') == store.today.format('YYYY-MM-DD');
        },
        newEvent: function newEvent(evt) {
            evt.id = this.generatedId++;
            this.events.push(evt);
        },
        inCurrentWeek: function inCurrentWeek(event) {
            return event.inWeek(this.currentDay.year(), this.currentDay.week());
        }
    },

    // Lorsque le composant est créé
    mounted: function mounted() {
        var wrapper = this.$el.querySelector('.content-wrapper');
        wrapper.scrollTop = 280;
    }
};

var MonthView = {
    data: function data() {
        return store;
    },

    template: "<div class=\"calendar calendar-month\">\n        <h2>Month view</h2>\n    </div>"
};

var ListItemView = {
    template: "<article class=\"list-item\" :style=\"css\" :class=\"cssClass\">\n        <time class=\"start\">{{ beginAt }}</time> -\n        <time class=\"end\">{{ endAt }}</time>\n        <strong>{{ event.label }}</strong>\n        <div class=\"details\">\n            <h4>\n                <i class=\"picto\" :style=\"{background: colorLabel}\"></i>\n                [ {{ event.id }}/{{ event.uid }}]\n                {{ event.label }}</h4>\n            <p class=\"time\">\n                de <time class=\"start\">{{ beginAt }}</time> \xE0 <time class=\"end\">{{ endAt }}</time>, <em>{{ event.duration }}</em> heure(s) ~ \xE9tat : <em>{{ event.status }}</em>\n            </p>\n            <p v-if=\"withOwner\">D\xE9clarant <strong>{{ event.owner }}</strong></p>\n            <p v-if=\"event.status == 'send'\" class=\"alert alert-warning\">Cet \xE9v\xE9nement est en attente de validation</p>\n            <p class=\"description\">\n                {{ event.description }}\n            </p>\n            <nav>\n                <button class=\"btn btn-primary btn-xs\" @click=\"$emit('selectevent', event)\">\n                    <i class=\"icon-calendar\"></i>\n                Voir la semaine</button>\n\n                <button class=\"btn btn-primary btn-xs\"  @click=\"$emit('editevent', event)\" v-if=\"event.editable\">\n                    <i class=\"icon-pencil-1\"></i>\n                    Modifier</button>\n\n                <button class=\"btn btn-primary btn-xs\"  @click=\"$emit('submitevent', event)\" v-if=\"event.sendable\">\n                    <i class=\"icon-pencil-1\"></i>\n                    Soumettre</button>\n\n                <button class=\"btn btn-primary btn-xs\"  @click=\"$emit('deleteevent', event)\" v-if=\"event.deletable\">\n                    <i class=\"icon-trash-empty\"></i>\n                    Supprimer</button>\n\n                <button class=\"btn btn-primary btn-xs\"  @click=\"handlerValidate\" v-if=\"event.validable\">\n                    <i class=\"icon-right-big\"></i>\n                    Valider</button>\n\n                <button class=\"btn btn-primary btn-xs\"  @click=\"$emit('rejectevent', event)\" v-if=\"event.validable\">\n                    <i class=\"icon-right-big\"></i>\n                    Rejeter</button>\n            </nav>\n        </div>\n    </article>",
    props: ['event', 'withOwner'],
    methods: {
        handlerValidate: function handlerValidate() {
            this.$emit('validateevent');
        }
    },
    computed: {
        beginAt: function beginAt() {
            return this.event.mmStart.format('HH:mm');
        },
        endAt: function endAt() {
            return this.event.mmEnd.format('HH:mm');
        },
        cssClass: function cssClass() {
            return 'status-' + this.event.status;
        },
        colorLabel: function colorLabel() {
            return _colorLabel(this.event.label);
        },
        css: function css() {
            var percentUnit = 100 / (18 * 60),
                start = (this.event.mmStart.hour() - 6) * 60 + this.event.mmStart.minutes(),
                end = (this.event.mmEnd.hour() - 6) * 60 + this.event.mmEnd.minutes();

            return {
                left: percentUnit * start + '%',
                width: percentUnit * (end - start) + '%',
                background: this.colorLabel
            };
        }
    }
};

var ListView = _defineProperty({
    data: function data() {
        return store;
    },


    computed: {
        firstDate: function firstDate() {
            return store.firstEvent;
        },
        lastDate: function lastDate() {
            return store.lastEvent;
        }
    },

    props: ['withOwner'],

    components: {
        listitem: ListItemView
    },

    template: "<div class=\"calendar calendar-list\">\n        <h2>Liste des cr\xE9neaux</h2>\n        <article v-for=\"pack in listEvents\">\n            <section class=\"events\">\n                <h3>{{ pack.label }}</h3>\n                <section class=\"events-list\">\n                <listitem\n                    :with-owner=\"withOwner\"\n                    @selectevent=\"selectEvent\"\n                    @editevent=\"$emit('editevent', event)\"\n                    @deleteevent=\"$emit('deleteevent', event)\"\n                    @submitevent=\"$emit('submitevent', event)\"\n                    @validateevent=\"$emit('validateevent', event)\"\n                    @rejectevent=\"$emit('rejectevent', event)\"\n                    v-bind:event=\"event\" v-for=\"event in pack.events\"></listitem>\n                </section>\n                <div class=\"total\">\n                    {{ pack.totalHours }} heure(s)\n                </div>\n            </section>\n\n        </article>\n    </div>",

    methods: {
        selectEvent: function selectEvent(event) {
            store.currentDay = (0, _momentTimezone2.default)(event.start);
            store.state = "week";
        }
    }

}, "computed", {
    listEvents: function listEvents() {
        _EventDT2.default.sortByStart(this.events);
        var pack = [];
        var packerFormat = 'ddd D MMMM YYYY';
        var packer = null;

        var currentPack = null;

        if (!store.events) {
            return null;
        }

        for (var i = 0; i < this.events.length; i++) {
            var event = this.events[i];
            var label = event.mmStart.format(packerFormat);

            if (packer == null || packer.label != label) {
                packer = {
                    label: label,
                    events: [],
                    totalHours: 0
                };
                pack.push(packer);
            }
            packer.totalHours += event.duration;
            packer.events.push(event);
        }

        return pack;
    }
});

var EventItemImport = {
    template: "<article class=\"list-item\" :class=\"{ imported: event.imported }\" :style=\"css\" @click=\"event.imported = !event.imported\">\n                  <time class=\"start\">{{ beginAt }}</time> -\n                  <time class=\"end\">{{ endAt }}</time>\n                  <span>\n                  <em>{{ event.label }}</em>\n                  <strong v-show=\"event.useLabel\"> => {{ event.useLabel }}</strong>\n                  </span>\n               </article>",
    props: ['event'],
    computed: {
        beginAt: function beginAt() {
            return this.event.mmStart.format('HH:mm');
        },
        endAt: function endAt() {
            return this.event.mmEnd.format('HH:mm');
        },
        colorLabel: function colorLabel() {
            return _colorLabel(this.event.label);
        },
        css: function css() {
            var percentUnit = 100 / (18 * 60),
                start = (this.event.mmStart.hour() - 6) * 60 + this.event.mmStart.minutes(),
                end = (this.event.mmEnd.hour() - 6) * 60 + this.event.mmEnd.minutes();

            return {
                position: "absolute",
                left: percentUnit * start + '%',
                width: percentUnit * (end - start) + '%',
                background: this.colorLabel
            };
        }
    }
};

var ImportICSView = {
    template: "<div class=\"importer\">\n                <div class=\"importer-ui\">\n                    <h1><i class=\"icon-calendar\"></i>Importer un ICS</h1>\n                    <nav class=\"steps\">\n                        <span :class=\"{active: etape == 1}\">Fichier ICS</span>\n                        <span :class=\"{active: etape == 2}\">Cr\xE9neaux \xE0 importer</span>\n                        <span :class=\"{active: etape == 3}\">Finalisation</span>\n\n                    </nav>\n\n                    <section class=\"etape1 row\" v-if=\"etape == 1\">\n                        <div class=\"col-md-1\">Du</div>\n                        <div class=\"col-md-5\">\n                            <datepicker v-model=\"periodStart\"></datepicker>\n                        </div>\n\n                        <div class=\"col-md-1\">au</div>\n                        <div class=\"col-md-5\">\n                            <datepicker v-model=\"periodEnd\"></datepicker>\n                        </div>\n\n                        <!-- P\xE9riode :\n                        <datepicker v-model=\"periodStart\"></datepicker> au <datepicker v-model=\"periodEnd\"></datepicker>\n                        -->\n                        <p>Choisissez un fichier ICS : </p>\n                        <input type=\"file\" @change=\"loadIcsFile\">\n                    </section>\n\n                    <section class=\"etape2\" v-if=\"etape == 2\">\n                        <h2><i class=\"icon-download-outline\"></i>Aper\xE7u des donn\xE9es charg\xE9es</h2>\n                        <p>Voici les donn\xE9es charg\xE9es depuis le fichier ICS fournis : </p>\n                        <div class=\"calendar calendar-list\">\n                            <article v-for=\"pack in packs\">\n                                <section class=\"events\">\n                                    <h3>{{ pack.label }}</h3>\n                                    <section class=\"events-list\">\n                                        <eventitemimport :event=\"event\" v-for=\"event in pack.events\"></eventitemimport>\n                                    </section>\n                                </section>\n                            </article>\n                        </div>\n                        <div>\n                            <h2><i class=\"icon-loop-outline\"></i>Correspondance des cr\xE9neaux</h2>\n                            <section class=\"correspondances\"\">\n                                <article v-for=\"label in labels\">\n                                    <strong><span :style=\"{'background': background(label)}\" class=\"square\">&nbsp</span>{{ label }}</strong>\n                                    <select name=\"\" id=\"\" @change=\"updateLabel(label, $event.target.value)\">\n                                        <option value=\"\">Conserver</option>\n                                        <option value=\"ignorer\">Ignorer ces cr\xE9neaux</option>\n                                        <option :value=\"creneau\" v-for=\"creneau in creneaux\">Placer dans {{ creneau }}</option>\n                                    </select>\n                                </article>\n                            </section>\n                        </div>\n                    </section>\n\n                    <div class=\"buttons\">\n                        <button class=\"btn btn-default\" @click=\"$emit('cancel')\">Annuler</button>\n                        <button class=\"btn btn-primary\" @click=\"applyImport\" v-if=\"etape==2\">\n                            Valider l'import de ces cr\xE9neaux\n                        </button>\n                    </div>\n                </div>\n            </div>",
    props: {
        'creneaux': {
            default: ['test A', 'test B', 'test C']
        }
    },

    data: function data() {
        return {
            periodStart: null,
            periodEnd: null,
            importedEvents: [],
            associations: {},
            labels: [],
            etape: 1
        };
    },


    components: {
        'datepicker': _Datepicker2.default,
        'eventitemimport': EventItemImport
    },

    computed: {
        packs: function packs() {
            var packs = [];
            this.importedEvents.forEach(function (item) {
                var currentPack = null;
                var currentLabel = item.mmStart.format('YYYY MMMM DD');
                for (var i = 0; i < packs.length && currentPack == null; i++) {
                    if (packs[i].label == currentLabel) {
                        currentPack = packs[i];
                    }
                }
                if (!currentPack) {
                    currentPack = {
                        label: currentLabel,
                        events: []
                    };
                    packs.push(currentPack);
                }
                currentPack.events.push(item);
            });
            return packs;
        }
    },

    methods: {
        background: function background(label) {
            return _colorLabel(label);
        },
        updateLabel: function updateLabel(from, to) {
            if (to == 'ignorer') {
                this.importedEvents.forEach(function (item) {
                    if (item.label == from) item.imported = false;
                });
            } else if (to == 'conserver') {
                this.importedEvents.forEach(function (item) {
                    if (item.label == from) item.useLabel = '';
                    item.imported = true;
                });
            } else {
                this.importedEvents.forEach(function (item) {
                    if (item.label == from) {
                        item.useLabel = to;
                        item.imported = true;
                    }
                });
            }
            this.associations[from] = to;
            console.log(this.associations);
        },


        /** Charge le fichier ICS depuis l'interface **/
        loadIcsFile: function loadIcsFile(e) {
            var _this6 = this;

            var fr = new FileReader();
            fr.onloadend = function (result) {
                _this6.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },


        /** Parse le contenu ICS **/
        parseFileContent: function parseFileContent(content) {
            var _this7 = this;

            var analyser = new _ICalAnalyser2.default();
            var events = analyser.parse(ICAL.parse(content));
            this.importedEvents = [];
            this.labels = [];

            events.forEach(function (item) {
                item.mmStart = (0, _momentTimezone2.default)(item.start);
                item.mmEnd = (0, _momentTimezone2.default)(item.end);
                item.imported = true;
                item.useLabel = "";
                _this7.importedEvents.push(item);
                if (_this7.labels.indexOf(item.label) < 0) _this7.labels.push(item.label);
            });

            this.etape = 2;
            /****/
        },
        applyImport: function applyImport() {
            var imported = [];
            this.importedEvents.forEach(function (event) {
                if (event.imported == true) {
                    imported.push(event);
                }
            });
            this.$emit('import', imported);
        }
    }
};

var Calendar = {

    template: "\n        <div class=\"calendar\">\n\n            <importview :creneaux=\"labels\" @cancel=\"importInProgress = false\" @import=\"importEvents\" v-if=\"importInProgress\"></importview>\n\n            <div class=\"editor\" v-show=\"eventEditDataVisible\">\n                <form @submit.prevent=\"editSave\">\n                    <div class=\"form-group\">\n                        <label for=\"\">Intitul\xE9</label>\n                        <input type=\"text\" v-model=\"eventEditData.label\" />\n                        <select v-model=\"eventEditData.label\" class=\"select2\">\n                            <option v-for=\"label in labels\" :value=\"label\">{{label}}</option>\n                        </select>\n                    </div>\n                    <div>\n                        <label for=\"\">Description</label>\n                        <textarea class=\"form-control\" v-model=\"eventEditData.description\"></textarea>\n                    </div>\n\n                    <button type=\"button\" @click=\"handlerEditCancelEvent\">Annuler</button>\n                    <button type=\"cancel\" @click=\"handlerSaveEvent\">Enregistrer</button>\n                </form>\n            </div>\n\n            <nav class=\"calendar-menu\">\n                <nav class=\"views-switcher\">\n                    <a href=\"#\" @click.prevent=\"state = 'week'\" :class=\"{active: state == 'week'}\"><i class=\"icon-calendar\"></i>{{ trans.labelViewWeek }}</a>\n                    <a href=\"#\" @click.prevent=\"state = 'list'\" :class=\"{active: state == 'list'}\"><i class=\"icon-columns\"></i>{{ trans.labelViewList }}</a>\n                    <a href=\"#\" @click.prevent=\"importInProgress = true\"><i class=\"icon-columns\"></i>Importer un ICS</a>\n                </nav>\n                <section class=\"transmission errors\">\n\n                    <p class=\"error\" v-for=\"error in errors\">\n                        <i class=\"icon-warning-empty\"></i> {{ error }}\n                        <a href=\"#\" @click.prevent=\"errors.splice(errors.indexOf(error), 1)\" class=\"fermer\">[fermer]</a>\n                    </p>\n                </section>\n                <section class=\"transmission infos\" v-show=\"transmission\">\n                    <span>\n                        <i class=\"icon-signal\"></i>\n                        {{ transmission }}\n                    </span>\n                </section>\n            </nav>\n\n            <weekview v-show=\"state == 'week'\"\n                :create-new=\"createNew\"\n                :with-owner=\"withOwner\"\n                @editevent=\"handlerEditEvent\"\n                @deleteevent=\"handlerDeleteEvent\"\n                @createpack=\"handlerCreatePack\"\n                @submitevent=\"handlerSubmitEvent\"\n                @validateevent=\"handlerValidateEvent\"\n                @rejectevent=\"handlerRejectEvent\"\n                @createevent=\"handlerCreateEvent\"\n                @savemoveevent=\"handlerSaveMove\"\n                @submitall=\"submitall\"\n                @saveevent=\"restSave\"></weekview>\n\n            <listview v-show=\"state == 'list'\"\n                :with-owner=\"withOwner\"\n                @editevent=\"handlerEditEvent\"\n                @deleteevent=\"handlerDeleteEvent\"\n                @validateevent=\"handlerValidateEvent\"\n                @rejectevent=\"handlerRejectEvent\"\n                @submitevent=\"handlerSubmitEvent\"></listview>\n        </div>\n\n    ",

    //                <!-- <a href="#" @click.prevent="state = 'month'"><i class="icon-table"></i>{{ trans.labelViewMonth }}</a> -->            <monthview v-show="state == 'month'"></monthview>

    data: function data() {
        return store;
    },


    props: {
        withOwner: {
            default: false
        },
        createNew: {
            default: false
        },
        // Texts
        trans: {
            default: function _default() {
                return {
                    labelViewWeek: "Semaine",
                    labelViewMonth: "Mois",
                    labelViewList: "Liste"
                };
            }
        }
    },

    components: {
        weekview: WeekView,
        monthview: MonthView,
        listview: ListView,
        eventitemimport: EventItemImport,
        importview: ImportICSView
    },

    methods: (_methods = {
        submitall: function submitall(status, period) {
            var events = [];
            if (period == 'week') {
                this.events.forEach(function (event) {
                    if (store.inCurrentWeek(event) && event.sendable) {
                        events.push(event);
                    }
                });
            }
            if (events.length) {
                this.restStep(events, status);
            }
        },
        importEvents: function importEvents(events) {
            var datas = [];
            events.forEach(function (item) {
                var event = JSON.parse(JSON.stringify(item));
                if (event.useLabel) event.label = event.useLabel;
                event.mmStart = (0, _momentTimezone2.default)(event.start);
                event.mmEnd = (0, _momentTimezone2.default)(event.end);
                datas.push(event);
            });
            this.importInProgress = false;
            this.restSave(datas);
        },
        handlerCreatePack: function handlerCreatePack(events) {
            console.log("create pack !");
            this.restSave(events);
        },
        confirmImport: function confirmImport() {
            console.log('Tous ajouter');
        },
        handleradd: function handleradd(pack, event) {
            console.log("Ajout unitaire", event);
            var packIndex = this.importedData.indexOf(pack);
            this.importedData[packIndex].splice(this.importedData[packIndex].indexOf(event));
        },
        editSave: function editSave() {
            this.defaultLabel = this.eventEdit.label = this.eventEditData.label;
            this.defaultDescription = this.eventEdit.description = this.eventEditData.description;
            this.handlerEditCancelEvent();
        },
        handlerEditCancelEvent: function handlerEditCancelEvent() {
            this.eventEditDataVisible = false;
            this.eventEdit = this.eventEditData = {};
        },


        /** Edition de l'événement de la liste */
        handlerEditEvent: function handlerEditEvent(event) {
            this.eventEdit = event;
            this.eventEditDataVisible = true;
            this.eventEditData = JSON.parse(JSON.stringify(event));
        },
        handlerValidateEvent: function handlerValidateEvent(event) {
            this.restValidate([event]);
        },
        handlerRejectEvent: function handlerRejectEvent(event) {
            this.restStep([event], 'reject');
        },
        restSave: function restSave(events) {
            var _this8 = this;

            if (this.restUrl) {
                this.transmission = "Enregistrement des données";
                var data = new FormData();
                for (var i = 0; i < events.length; i++) {
                    data.append('events[' + i + '][label]', events[i].label);
                    data.append('events[' + i + '][description]', events[i].description);
                    data.append('events[' + i + '][start]', events[i].mmStart.format());
                    data.append('events[' + i + '][end]', events[i].mmEnd.format());
                    data.append('events[' + i + '][id]', events[i].id || null);
                    if (this.customDatas) {
                        var customs = this.customDatas();
                        for (var k in customs) {
                            if (customs.hasOwnProperty(k) && events[i].label == k) {
                                for (var l in customs[k]) {
                                    if (customs[k].hasOwnProperty(l)) {
                                        data.append('events[' + i + '][' + l + ']', customs[k][l]);
                                    }
                                }
                            }
                        }
                    }
                }

                this.$http.post(this.restUrl(), data).then(function (response) {
                    store.sync(response.body.timesheets);
                    _this8.handlerEditCancelEvent();
                }, function (error) {
                    _this8.errors.push("Impossible d'enregistrer les données : " + error);
                }).then(function () {
                    return _this8.transmission = "";
                });;
            }
        },
        restSend: function restSend(events) {
            this.restStep(events, 'send');
        },
        restValidate: function restValidate(events) {
            this.restStep(events, 'validate');
        },
        restStep: function restStep(events, action) {
            var _this9 = this;

            if (this.restUrl) {
                this.transmission = "Enregistrement en cours...";
                var data = new FormData();
                data.append('do', action);
                for (var i = 0; i < events.length; i++) {
                    data.append('events[' + i + '][id]', events[i].id || null);
                }

                this.$http.post(this.restUrl(), data).then(function (response) {
                    store.sync(response.body.timesheets);
                    _this9.handlerEditCancelEvent();
                }, function (error) {
                    _this9.errors.push("Impossible de modifier l'état du créneau : " + error);
                }).then(function () {
                    _this9.transmission = "";
                });
            }
        },


        /** Suppression de l'événement de la liste */
        handlerDeleteEvent: function handlerDeleteEvent(event) {
            var _this10 = this;

            if (this.restUrl) {
                this.transmission = "Suppression...";
                this.$http.delete(this.restUrl() + "?timesheet=" + event.id).then(function (response) {
                    _this10.events.splice(_this10.events.indexOf(event), 1);
                }, function (error) {
                    console.log(error);
                }).then(function () {
                    _this10.transmission = "";
                });
            } else {
                this.events.splice(this.events.indexOf(event), 1);
            }
        },
        handlerSaveMove: function handlerSaveMove(event) {
            console.log('handlerSaveMove(', event, ')');
            var data = JSON.parse(JSON.stringify(event));
            data.mmStart = (0, _momentTimezone2.default)(data.start);
            data.mmEnd = (0, _momentTimezone2.default)(data.end);;
            this.restSave([data]);
        },
        handlerSaveEvent: function handlerSaveEvent(event) {
            console.log('handlerSaveEvent(event)');
            var data = JSON.parse(JSON.stringify(this.eventEditData));
            data.mmStart = this.eventEdit.mmStart;
            data.mmEnd = this.eventEdit.mmEnd;
            this.restSave([data]);
        },


        /** Soumission de l'événement de la liste */
        handlerSubmitEvent: function handlerSubmitEvent(event) {
            console.log('Envoi', arguments);
            this.restSend([event]);
        },


        /** Soumission de l'événement de la liste */
        handlerCreateEvent: function handlerCreateEvent(event) {
            this.restSave([event]);
            //            this.events.push(event);
        },


        /** Charge le fichier ICS depuis l'interface **/
        loadIcsFile: function loadIcsFile(e) {
            var _this11 = this;

            this.transmission = "Analyse du fichier ICS...";
            var fr = new FileReader();
            fr.onloadend = function (result) {
                _this11.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },


        /** Parse le contenu ICS **/
        parseFileContent: function parseFileContent(content) {
            var _this12 = this;

            var analyser = new _ICalAnalyser2.default();
            var events = analyser.parse(ICAL.parse(content));
            this.importedData = [];

            events.forEach(function (item) {
                item.mmStart = (0, _momentTimezone2.default)(item.start);
                item.mmEnd = (0, _momentTimezone2.default)(item.end);

                var currentPack = null;
                var currentLabel = item.mmStart.format('YYYY-MM-D');
                for (var i = 0; i < _this12.importedData.length && currentPack == null; i++) {
                    if (_this12.importedData[i].label == currentLabel) {
                        currentPack = _this12.importedData[i];
                    }
                }
                if (!currentPack) {
                    currentPack = {
                        label: currentLabel,
                        events: []
                    };
                    _this12.importedData.push(currentPack);
                }
                currentPack.events.push(item);
            });
            this.importInProgress = true;
        },


        /** Ajoute la liste d'événement **/
        hydrateEventWith: function hydrateEventWith(arrayOfObj) {

            arrayOfObj.forEach(function (obj) {
                store.addNewEvent(obj.id, obj.label, obj.start, obj.end, obj.description, { editable: true, deletable: true }, 'draft');
            });
        },
        deleteEvent: function deleteEvent(event) {
            this.events.splice(this.events.indexOf(event), 1);
        },
        createEvent: function createEvent(day, time) {
            var start = (0, _momentTimezone2.default)(this.currentDay).day(day).hour(time);
            var end = (0, _momentTimezone2.default)(start).add(2, 'hours');
            this.newEvent(new _EventDT2.default(null, this.defaultLabel, start.format(), end.format(), this.defaultDescription, { editable: true, deletable: true }));
        },
        editEvent: function editEvent(event) {
            this.eventEdit = event;
            this.eventEditData = JSON.parse(JSON.stringify(event));
        }
    }, _defineProperty(_methods, "editSave", function editSave() {
        console.log('deprecated');
    }), _defineProperty(_methods, "editCancel", function editCancel() {
        this.eventEdit = this.eventEditData = null;
    }), _defineProperty(_methods, "fetch", function fetch() {
        var _this13 = this;

        this.transmission = "Chargement des créneaux...";

        this.$http.get(this.restUrl()).then(function (ok) {
            store.sync(ok.body.timesheets);
        }, function (ko) {
            _this13.errors.push("Impossible de charger les données : " + ko);
        }).then(function () {
            _this13.transmission = "";
        });
    }), _defineProperty(_methods, "post", function post(event) {
        console.log("POST", event);
    }), _methods),

    mounted: function mounted() {
        console.log("customdatas");
        if (this.customDatas) {
            var customs = this.customDatas();
            for (var k in customs) {
                if (customs.hasOwnProperty(k)) {
                    console.log('customdata', k);
                    colorLabels[k] = colorpool[colorIndex];
                    if (!store.defaultLabel) {
                        store.defaultLabel = k;
                    }
                    store.labels.push(k);
                }
            }
            colorIndex++;
            console.log(colorLabels);
        }

        if (this.restUrl) {
            this.fetch();
        }
    }
};
return Calendar;
}));
