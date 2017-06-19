;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['moment', 'ICalAnalyser', 'EventDT', 'Datepicker', 'bootbox'], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory(require('moment'), require('ICalAnalyser'), require('EventDT'), require('Datepicker'), require('bootbox'));
  } else {
    root.Calendar = factory(root.moment, root.ICalAnalyser, root.EventDT, root.Datepicker, root.bootbox);
  }
}(this, function(moment, ICalAnalyser, EventDT, Datepicker, bootbox) {
'use strict';

var _methods;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

moment.locale('fr');

var colorLabels = {};
var colorIndex = 0;
var colorpool = ['#fcdc80', '#a6cef8', '#9fd588', '#fb90bb', '#e5fbed', '#99a0ce', '#bca078', '#f3cafd', '#d9f4c1', '#60e3bb', '#f2c7f5', '#f64bc0', '#ffc1b2', '#fc9175', '#d7fc74', '#e3d7f8', '#9ffab3', '#d6cbac', '#4dd03c', '#f8f3be'];

var _colorLabel = function _colorLabel(label) {
    if (!colorLabels[label]) {
        console.log('Color label', label);

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
        this.currentDay = moment();
        this.eventEdit = null;
        this.copyWeekData = null;
        this.copyDayData = null;
        this.generatedId = 0;
        this.defaultLabel = "";
        this.errors = [];
        this.defaultDescription = "";
        this.labels = [];
        this.owners = [];

        this.gostDatas = {
            x: 0,
            y: 0,
            startFrom: null,
            decalageY: 0,
            day: 1,
            start: null,
            end: null,
            title: "Nouveau créneau",
            description: "",
            startX: 0,
            height: 40,
            drawing: false,
            editActive: false
        };

        ////
        this.displayRejectModal = false;
        this.rejectValidateType = null;
        this.rejectComment = "";
        this.rejectedEvents = [];
    }

    _createClass(CalendarDatas, [{
        key: 'copyDay',
        value: function copyDay(dt) {
            var _this = this;

            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach(function (event) {
                var dayRef = moment(event.start).format('MMMM D YYYY');
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
        /**
         * Copie les créneaux de la semaine en cours d'affichage.
         */

    }, {
        key: 'copyCurrentWeek',
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

        /**
         * Colle les créneaux en mémoire (jour) dans le jour spécifié.
         *
         * @param day
         * @returns {*}
         */

    }, {
        key: 'pasteDay',
        value: function pasteDay(day) {
            if (this.copyDayData) {
                var create = [];

                this.copyDayData.forEach(function (event) {
                    var start = moment(day.format());
                    start.hour(event.startHours).minute(event.startMinutes);

                    var end = moment(day.format());
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

        /**
         * Colle les créneaux en mémoire dans le semaine en cours d'affichage.
         * @returns {*}
         */

    }, {
        key: 'pasteWeek',
        value: function pasteWeek() {
            var _this3 = this;

            if (this.copyWeekData) {
                var create = [];
                this.copyWeekData.forEach(function (event) {
                    var start = moment(_this3.currentDay);
                    start.day(event.day).hour(event.startHours).minute(event.startMinutes);

                    var end = moment(_this3.currentDay);
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

        /**
         * Affiche la semaine précédente.
         */

    }, {
        key: 'previousWeek',
        value: function previousWeek() {
            this.currentDay = moment(this.currentDay).add(-1, 'week');
        }

        /**
         * Affiche la semaine suivante.
         */

    }, {
        key: 'nextWeek',
        value: function nextWeek() {
            this.currentDay = moment(this.currentDay).add(1, 'week');
        }

        /**
         * Création d'un nouveau créneau à partir du EventDT transmis en paramètre.
         * @param evt EventDT
         */

    }, {
        key: 'newEvent',
        value: function newEvent(evt) {
            evt.id = this.generatedId++;
            this.events.push(evt);
        }
    }, {
        key: 'inCurrentWeek',
        value: function inCurrentWeek(event) {
            return event.inWeek(this.currentDay.year(), this.currentDay.week());
        }
    }, {
        key: 'sync',
        value: function sync(datas) {
            for (var i = 0; i < datas.length; i++) {
                var local = this.getEventById(datas[i].id);
                if (local) {
                    local.sync(datas[i]);
                } else {
                    this.addNewEvent(datas[i].id, datas[i].label, datas[i].start, datas[i].end, datas[i].description, datas[i].credentials, datas[i].status, datas[i].owner, datas[i].owner_id, datas[i].rejectedSciComment, datas[i].rejectedSciAt, datas[i].rejectedAdminComment, datas[i].rejectedAdminAt, datas[i].validatedSciAt, datas[i].validatedSciBy, datas[i].validatedAdminAt, datas[i].validatedAdminBy);
                }
            }
        }
    }, {
        key: 'getEventById',
        value: function getEventById(id) {
            for (var i = 0; i < this.events.length; i++) {
                if (this.events[i].id == id) {
                    return this.events[i];
                }
            }
            return null;
        }
    }, {
        key: 'addNewEvent',
        value: function addNewEvent(id, label, start, end, description) {
            var credentials = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : undefined;
            var status = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : "draft";
            var owner = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : "";
            var owner_id = arguments.length > 8 && arguments[8] !== undefined ? arguments[8] : null;
            var rejectedSciComment = arguments.length > 9 && arguments[9] !== undefined ? arguments[9] : "";
            var rejectedSciAt = arguments.length > 10 && arguments[10] !== undefined ? arguments[10] : null;
            var rejectedAdminComment = arguments.length > 11 && arguments[11] !== undefined ? arguments[11] : "";
            var rejectedAdminAt = arguments.length > 12 && arguments[12] !== undefined ? arguments[12] : null;
            var validatedSciAt = arguments.length > 13 && arguments[13] !== undefined ? arguments[13] : null;
            var validatedSciBy = arguments.length > 14 && arguments[14] !== undefined ? arguments[14] : null;
            var validatedAdminAt = arguments.length > 15 && arguments[15] !== undefined ? arguments[15] : null;
            var validatedAdminBy = arguments.length > 16 && arguments[16] !== undefined ? arguments[16] : null;

            this.events.push(new EventDT(id, label, start, end, description, credentials, status, owner, owner_id, rejectedSciComment, rejectedSciAt, rejectedAdminComment, rejectedAdminAt, validatedSciAt, validatedSciBy, validatedAdminAt, validatedAdminBy));
        }
    }, {
        key: 'listEvents',
        get: function get() {
            EventDT.sortByStart(this.events);
            return this.events;
        }
    }, {
        key: 'today',
        get: function get() {
            return moment();
        }
    }, {
        key: 'firstEvent',
        get: function get() {}
    }, {
        key: 'lastEvent',
        get: function get() {}
    }, {
        key: 'currentYear',
        get: function get() {
            return this.currentDay.format('YYYY');
        }
    }, {
        key: 'currentMonth',
        get: function get() {
            return this.currentDay.format('MMMM');
        }
    }, {
        key: 'currentWeekKey',
        get: function get() {
            return this.currentDay.format('YYYY-W');
        }
    }, {
        key: 'currentWeekDays',
        get: function get() {
            var days = [],
                day = moment(this.currentDay.startOf('week'));

            for (var i = 0; i < 7; i++) {
                days.push(moment(day.format()));
                day.add(1, 'day');
            }
            return days;
        }
    }]);

    return CalendarDatas;
}();

var store = new CalendarDatas();

var TimeEvent = {

    template: '<div class="event" :style="css"\n\n            @mousedown="handlerMouseDown"\n            :title="event.label"\n            :class="{\'event-changing\': changing, \'event-moving\': moving, \'event-selected\': selected, \'event-locked\': isLocked, \'status-info\': isInfo, \'status-draft\': isDraft, \'status-send\' : isSend, \'status-valid\': isValid, \'status-reject\': isReject, \'valid-sci\': isValidSci, \'valid-adm\': isValidAdm, \'reject-sci\':isRejectSci}, \'reject-adm\': isRejectAdm">\n        <div class="label" data-uid="UID">\n          {{ event.label }}\n        </div>\n        <small>Dur\xE9e : <strong>{{ labelDuration }}</strong> heure(s)</small>\n        <div class="description">\n            <p v-if="withOwner">D\xE9clarant <strong>{{ event.owner }} ({{event.owner_id}})</strong></p>\n          {{ event.description }}\n          <div>\n            <i class="icon-archive icon-admin" :class="adminState"></i> Admin / <i class="icon-beaker icon-sci"></i> Scien.\n        </div>\n        </div>\n        \n        \n\n        <div class="refus" @mouseover.prevent="showRefus != showRefus">\n            <div v-show="showRefus">\n                <i class="icon-beaker"></i>\n                Refus scientifique :\n                <div class="comment">{{ event.rejectedSciComment}}</div>\n                <i class="icon-archive"></i>\n                Refus administratif :\n                <div class="comment">{{ event.rejectedAdminComment}}</div>\n            </div>\n        </div>\n\n        <nav class="admin">\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="handlerDebug(event)">\n                <i class="icon-bug"></i>\n                Debug</a>\n                \n            <a href="#" \n                @mousedown.stop.prevent="" \n                @click.stop.prevent="handlerShowReject(event)" \n                v-if="event.rejectedSciComment || event.rejectedAdminComment">\n                <i class="icon-attention"></i>\n                Afficher le rejet</a>\n                \n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'editevent\')" v-if="event.editable">\n                <i class="icon-pencil-1"></i>\n                Modifier</a>\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'deleteevent\')" v-if="event.deletable">\n                <i class="icon-trash-empty"></i>\n                Supprimer</a>\n\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'submitevent\')" v-if="event.sendable">\n                <i class="icon-right-big"></i>\n                Soumettre</a>\n\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'rejectscievent\')" v-if="event.validableSci">\n                <i class="icon-attention-1"></i>\n                Refus scientifique</a>\n\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'rejectadmevent\')" v-if="event.validableAdm">\n                <i class="icon-attention-1"></i>\n                Refus administratif</a>\n                \n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'validatescievent\')" v-if="event.validableSci">\n                <i class="icon-beaker"></i>\n                Validation scientifique</a>\n            \n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'validateadmevent\')" v-if="event.validableAdm">\n                <i class="icon-archive"></i>\n                Validation administrative</a>\n            \n             \n            \n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'validateevent\')" v-if="event.validable">\n                <i class="icon-right-big"></i>\n                Valider</a>\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'rejectevent\')" v-if="event.validable">\n                <i class="icon-right-big"></i>\n                Rejeter</a>\n        </nav>\n\n        <div class="bottom-handler" v-if="event.editable"\n\n            @mousedown.prevent.stop="handlerStartMovingEnd">\n            <span>===</span>\n        </div>\n\n        <time class="time start">{{ labelStart }}</time>\n        <time class="time end">{{ labelEnd }}</time>\n      </div>',

    props: ['event', 'weekDayRef', 'withOwner'],

    data: function data() {
        return {
            selected: false,
            moving: false,
            interval: null,
            movingBoth: true,
            changing: false,
            change: false,
            showRefus: false,
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
        adminState: function adminState() {
            return this.event.rejectedAdminAt ? 'rejected' : this.event.validatedAdminAt ? 'validated' : 'waiting';
        },
        admStatus: function admStatus() {
            if (this.event.rejectedAdminAt) {
                return "Rejet administratif";
            } else if (this.event.validatedAdminAt) {
                return "Validation administrative le à";
            } else {
                return "en attente de validation";
            }
        },
        sciStatus: function sciStatus() {
            if (this.event.rejectedSciAt) {
                return "Rejet administratif";
            } else if (this.event.validatedSciAt) {
                return "Validation administrative le à";
            } else {
                return "en attente de validation";
            }
        },
        css: function css() {
            var marge = 0;
            var sizeless = 0;
            if (this.event.intersect > 0) {
                sizeless = 3;
                marge = sizeless / this.event.intersect * this.event.intersectIndex;
            }
            return {
                'pointer-events': this.changing ? 'none' : 'auto',
                height: this.pixelEnd - this.pixelStart + 'px',
                background: this.withOwner ? _colorLabel(this.event.owner) : _colorLabel(this.event.label),
                position: "absolute",
                //'opacity': (this.changing ? '1' : 'inherit'),
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
        isValidSci: function isValidSci() {
            return this.event.validatedSciAt != null;
        },
        isValidAdm: function isValidAdm() {
            return this.event.validatedAdminAt != null;
        },
        isRejectSci: function isRejectSci() {
            return this.event.rejectedSciAt != null;
        },
        isRejectAdm: function isRejectAdm() {
            return this.event.rejectedAdminAt != null;
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
            return moment(this.event.start);
        },
        dateEnd: function dateEnd() {
            return moment(this.event.end);
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
        handlerDebug: function handlerDebug(data) {
            console.log(data);
        },
        updateWeekDay: function updateWeekDay(value) {
            var start = this.dateStart.day(value);
            var end = this.dateEnd.day(value);
            this.event.start = start.format();
            this.event.end = end.format();
        },
        handlerShowRefus: function handlerShowRefus() {
            bootbox.alert({
                size: "small",
                title: '<i class="icon-beaker"></i>   Refus scientifique',
                message: '<em>Motif : </em>' + this.event.rejectedSciComment + ""
            });
        },
        handlerShowRefusAdmin: function handlerShowRefusAdmin() {
            bootbox.alert({
                size: "small",
                title: '<i class="icon-archive"></i>   Refus administratif',
                message: '<em>Motif : </em>' + this.event.rejectedAdminComment + ""
            });
        },
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

                this.updateLabel();
            }
        },
        updateLabel: function updateLabel() {
            var dtUpdate = this.topToStart();
            this.labelDuration = dtUpdate.duration;
            this.labelStart = dtUpdate.startLabel;
            this.labelEnd = dtUpdate.endLabel;
        },
        handlerEndMovingEnd: function handlerEndMovingEnd() {
            if (this.movingBoth) {
                this.movingBoth = false;
            }
        },
        handlerStartMovingEnd: function handlerStartMovingEnd(e) {
            /*this.movingBoth = false;
             this.startMoving(e);*/
            this.$emit('onstartmoveend', this);
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
            if (this.event.editable) {
                this.changing = true;
                this.$emit('mousedown', this, e);
            }
        },
        handlerMouseUp: function handlerMouseUp(e) {
            if (this.event.editable) {
                console.log('UPDATE now');
                this.moving = false;
                this.$el.removeEventListener('mousemove', this.move);

                var dtUpdate = this.topToStart();

                this.event.start = this.dateStart.hours(dtUpdate.startHours).minutes(dtUpdate.startMinutes).format();

                this.event.end = this.dateEnd.hours(dtUpdate.endHours).minutes(dtUpdate.endMinutes).format();

                if (this.change) {
                    console.log('trigger update');
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

    template: '<div class="calendar calendar-week">\n    <div class="meta">\n        <a href="#" @click="previousWeek">\n            <i class="icon-left-big"></i>\n        </a>\n        <h3>\n            Semaine {{ currentWeekNum}}, {{ currentMonth }} {{ currentYear }}\n            <nav class="copy-paste" v-if="createNew">\n                <span href="#" @click="copyCurrentWeek"><i class="icon-docs"></i></span>\n                <span href="#" @click="pasteWeek"><i class="icon-paste"></i></span>\n                <span href="#" @click="$emit(\'submitall\', \'send\', \'week\')"><i class="icon-right-big"></i></span>\n            </nav>\n        </h3>\n       <a href="#" @click="nextWeek">\n            <i class="icon-right-big"></i>\n       </a>\n    </div>\n\n    <header class="line">\n        <div class="content-full" style="margin-right: 12px">\n            <div class="labels-time">\n                {{currentYear}}\n            </div>\n            <div class="events">\n                <div class="cell cell-day day day-1" :class="{today: isToday(day)}" v-for="day in currentWeekDays">\n                    {{ day.format(\'dddd D\') }}\n                    <nav class="copy-paste" v-if="createNew">\n                        <span href="#" @click="copyDay(day)"><i class="icon-docs"></i></span>\n                        <span href="#" @click="pasteDay(day)"><i class="icon-paste"></i></span>\n                        <span href="#" @click="submitDay(day)"><i class="icon-right-big"></i></span>\n                    </nav>\n                </div>\n            </div>\n        </div>\n    </header>\n\n    <div class="content-wrapper">\n        <div class="content-full">\n          <div class="labels-time">\n            <div class="unit timeinfo" v-for="time in 24">{{time-1}}:00</div>\n          </div>\n          <div class="events" :class="{\'drawing\': (gostDatas.editActive) }"\n                   @mouseup.self="handlerMouseUp"\n                   @mousedown.self="handlerMouseDown"\n                   @mousemove.self="handlerMouseMove">\n\n              <div class="cell cell-day day" v-for="day in 7" style="pointer-events: none">\n                <div class="hour houroff" v-for="time in 6">&nbsp;</div>\n                <div class="hour" v-for="time in 16"\n                    @dblclick="handlerCreate(day, time+5)">&nbsp;</div>\n                <div class="hour houroff" v-for="time in 2">&nbsp;</div>\n              </div>\n              <div class="content-events">\n                <div class="gost"\n                    :style="gostStyle" v-show="gostDatas.drawing">&nbsp;</div>\n                <timeevent v-for="event in weekEvents"\n                    :with-owner="withOwner"\n                    :weekDayRef="currentDay"\n                    v-if="inCurrentWeek(event)"\n                    @deleteevent="$emit(\'deleteevent\', event)"\n                    @editevent="$emit(\'editevent\', event)"\n                    @submitevent="$emit(\'submitevent\', event)"\n                    @rejectscievent="$emit(\'rejectevent\', event, \'sci\')"\n                    @rejectadmevent="$emit(\'rejectevent\', event, \'adm\')"\n                    @validatescievent="$emit(\'validateevent\', event, \'sci\')"\n                    @validateadmevent="$emit(\'validateevent\', event, \'adm\')"\n                    @mousedown="handlerEventMouseDown"\n                    @savemoveevent="handlerSaveMove(event)"\n                    @onstartmoveend="handlerStartMoveEnd"\n                    :event="event"\n                    :key="event.id"></timeevent>\n              </div>\n          </div>\n        </div>\n    </div>\n\n    <footer class="line">\n      Afficher les sous-totaux\n    </footer>\n    </div>',

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
                day = moment(this.currentDay.startOf('week'));

            for (var i = 0; i < 7; i++) {
                days.push(moment(day.format()));
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
        },
        gostStyle: function gostStyle() {
            return {
                'left': this.gostDatas.x + "px",
                'top': this.gostDatas.y + "px",
                'width': '13.2857%',
                'pointer-events': 'none',
                'height': this.gostDatas.height + "px",
                'position': 'absolute'
            };
        }
    },

    methods: {

        //        @savemoveevent="handlerSaveMove"
        handlerEventMouseDown: function handlerEventMouseDown(event, evt) {
            if (event.event.editable) {
                this.gostDatas.eventActive = event;
                this.gostDatas.editActive = true;
            }
        },


        /**
         * Début du déplacement de la borne de fin.
         *
         * @param event
         */
        handlerStartMoveEnd: function handlerStartMoveEnd(event) {
            this.gostDatas.eventMovedEnd = event;
            this.gostDatas.editActive = true;
            this.gostDatas.eventMovedEnd.changing = true;
        },
        handlerSaveMove: function handlerSaveMove(event) {
            this.$emit('savemoveevent', event);
        },
        handlerMouseUp: function handlerMouseUp(e) {
            if (this.gostDatas.drawing) {
                this.gostDatas.drawing = false;
                this.createEvent(this.gostDatas.day, Math.floor(this.gostDatas.y / 40), this.gostDatas.height / 40 * 60);
            }

            if (this.gostDatas.eventActive) {
                this.gostDatas.eventActive.changing = false;
                this.gostDatas.eventActive.handlerMouseUp();
                this.gostDatas.eventActive = null;
            }

            if (this.gostDatas.eventMovedEnd) {
                console.log("FIN du déplacement de la borne de fin");
                this.gostDatas.eventMovedEnd.changing = false;
                this.gostDatas.eventMovedEnd.handlerMouseUp();
                this.gostDatas.eventMovedEnd = null;
            }
            this.gostDatas.startFrom = null;
            this.gostDatas.editActive = false;
        },
        handlerMouseDown: function handlerMouseDown(e) {
            var roundFactor = 40 / 60 * this.pas;
            this.gostDatas.y = Math.round(e.offsetY / roundFactor) * roundFactor;
            var pas = $(e.target).width() / 7;
            var day = Math.floor(e.offsetX / pas);
            this.gostDatas.day = day + 1;
            this.gostDatas.x = day * pas;
            this.gostDatas.startX = this.gostDatas.x;
            this.gostDatas.drawing = true;
            this.gostDatas.editActive = true;
        },
        handlerMouseMove: function handlerMouseMove(e) {
            if (this.gostDatas.drawing) {
                this.gostDatas.height = Math.round((e.offsetY - this.gostDatas.y) / (40 / 60 * this.pas)) * (40 / 60 * this.pas);
            } else if (this.gostDatas.eventActive) {
                this.gostDatas.eventActive.changing = true;
                if (this.gostDatas.startFrom == null) {
                    this.gostDatas.startFrom = e.offsetY + parseInt($(this.gostDatas.eventActive.$el).css('top'));
                    this.gostDatas.decalageY = e.offsetY - parseInt($(this.gostDatas.eventActive.$el).css('top'));
                } else {

                    // On calcule l'emplacement de la souris pour savoir si on
                    // a une bascule de la journée
                    var pas = $(e.target).width() / 7,
                        day = Math.floor(e.offsetX / pas),


                    // Déplacement réél de la souris
                    realMove = e.offsetY - this.gostDatas.startFrom - this.gostDatas.decalageY,


                    // Déplacement arrondis (effet magnétique)
                    effectivMove = Math.round(realMove / (40 / 60 * this.pas)),
                        effectiveMoveApplication = effectivMove * (40 / 60 * this.pas),


                    // Position Y
                    top = parseInt($(this.gostDatas.eventActive.$el).css('top'));

                    /*if( top < 0 ){
                        top = 0;
                        return;
                    }*/

                    // Mise à jour du jour si besoin
                    if (day + 1 != this.gostDatas.eventActive.weekDay) {
                        this.gostDatas.eventActive.updateWeekDay(day + 1);
                    }

                    // Application du déplacement
                    if (effectivMove != 0) {
                        $(this.gostDatas.eventActive.$el).css('top', effectiveMoveApplication + top);
                        this.gostDatas.startFrom = effectiveMoveApplication + top;
                        this.gostDatas.eventActive.change = true;
                        this.gostDatas.eventActive.updateLabel();
                    }
                }
            } else if (this.gostDatas.eventMovedEnd) {
                console.log("déplacement de la borne de fin");
                if (this.gostDatas.startFrom == null) {
                    this.gostDatas.startFrom = e.offsetY;
                } else {
                    var pas = $(e.target).width() / 7;
                    var day = Math.floor(e.offsetX / pas);
                    var realMove = e.offsetY - this.gostDatas.startFrom; //, e.target;
                    var effectivMove = Math.round(realMove / (40 / 60 * this.pas));
                    if (effectivMove != 0) {
                        var height = parseInt($(this.gostDatas.eventMovedEnd.$el).css('height'));
                        $(this.gostDatas.eventMovedEnd.$el).css('height', effectivMove * (40 / 60 * this.pas) + height);
                        this.gostDatas.startFrom = parseInt($(this.gostDatas.eventMovedEnd.$el).css('top')) + effectivMove * (40 / 60 * this.pas) + height;
                        this.gostDatas.eventMovedEnd.change = true;
                        this.gostDatas.eventMovedEnd.updateLabel();
                        // eventActive
                    }
                }
            }
        },
        handlerCreate: function handlerCreate(day, time) {
            this.createEvent(day, time);
        },
        createEvent: function createEvent(day, time) {
            var duration = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 120;

            var start = moment(this.currentDay).day(day).hour(time);
            var end = moment(start).add(duration, 'minutes');
            var newEvent = new EventDT(null, this.defaultLabel, start.format(), end.format(), this.defaultDescription, {
                editable: true,
                deletable: true
            });
            this.$emit('createevent', newEvent);
        },
        copyDay: function copyDay(dt) {
            var _this4 = this;

            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach(function (event) {
                var dayRef = moment(event.start).format('MMMM D YYYY');
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
        submitDay: function submitDay(dt) {
            this.$emit('submitday', dt);
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
            this.currentDay = moment(this.currentDay).add(-1, 'week');
        },
        nextWeek: function nextWeek() {
            this.currentDay = moment(this.currentDay).add(1, 'week');
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

    template: '<div class="calendar calendar-month">\n        <h2>Month view</h2>\n    </div>'
};

var ListItemView = {
    template: '<article class="list-item" :style="css" :class="cssClass">\n        <time class="start">{{ beginAt }}</time> -\n        <time class="end">{{ endAt }}</time>\n        <strong>{{ event.label }}</strong>\n        <div class="details">\n            <h4>\n                <i class="picto" :style="{background: colorLabel}"></i>\n                [ {{ event.id }}/{{ event.uid }}]\n                {{ event.label }}</h4>\n            <p class="time">\n                de <time class="start">{{ beginAt }}</time> \xE0 <time class="end">{{ endAt }}</time>, <em>{{ event.duration }}</em> heure(s) ~ \xE9tat : <em>{{ event.status }}</em>\n            </p>\n            <p v-if="withOwner">D\xE9clarant <strong>{{ event.owner }}</strong></p>\n            <p v-if="event.status == \'send\'" class="alert alert-warning">Cet \xE9v\xE9nement est en attente de validation</p>\n            <p class="description">\n                {{ event.description }}\n            </p>\n            <nav>\n                <button class="btn btn-primary btn-xs" @click="$emit(\'selectevent\', event)">\n                    <i class="icon-calendar"></i>\n                Voir la semaine</button>\n\n                <button class="btn btn-primary btn-xs"  @click="$emit(\'editevent\', event)" v-if="event.editable">\n                    <i class="icon-pencil-1"></i>\n                    Modifier</button>\n\n                <button class="btn btn-primary btn-xs"  @click="$emit(\'submitevent\', event)" v-if="event.sendable">\n                    <i class="icon-pencil-1"></i>\n                    Soumettre</button>\n\n                <button class="btn btn-primary btn-xs"  @click="$emit(\'deleteevent\', event)" v-if="event.deletable">\n                    <i class="icon-trash-empty"></i>\n                    Supprimer</button>\n\n                <!--<button class="btn btn-primary btn-xs"  @click="handlerValidate" v-if="event.validable">\n                    <i class="icon-right-big"></i>\n                    Valider</button>\n\n                <button class="btn btn-primary btn-xs"  @click="$emit(\'rejectevent\', event)" v-if="event.validable">\n                    <i class="icon-right-big"></i>\n                    Rejeter</button>-->\n            </nav>\n        </div>\n    </article>',
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

    template: '<div class="calendar calendar-list">\n        <h2>Liste des cr\xE9neaux</h2>\n        <article v-for="pack in listEvents">\n            <section class="events">\n                <h3>{{ pack.label }}</h3>\n                <section class="events-list">\n                <listitem\n                    :with-owner="withOwner"\n                    @selectevent="selectEvent"\n                    @editevent="$emit(\'editevent\', event)"\n                    @deleteevent="$emit(\'deleteevent\', event)"\n                    @submitevent="$emit(\'submitevent\', event)"\n                    @validateevent="$emit(\'validateevent\', event)"\n                    @rejectevent="$emit(\'rejectevent\', event)"\n                    v-bind:event="event" v-for="event in pack.events"></listitem>\n                </section>\n                <div class="total">\n                    {{ pack.totalHours }} heure(s)\n                </div>\n            </section>\n\n        </article>\n    </div>',

    methods: {
        selectEvent: function selectEvent(event) {
            store.currentDay = moment(event.start);
            store.state = "week";
        }
    }

}, 'computed', {
    listEvents: function listEvents() {
        EventDT.sortByStart(this.events);
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
    template: '<article class="list-item" :class="{ imported: event.imported }" :style="css" @click="event.imported = !event.imported">\n                  <time class="start">{{ beginAt }}</time> -\n                  <time class="end">{{ endAt }}</time>\n                  <span>\n                  <em>{{ event.label }}</em>\n                  <strong v-show="event.useLabel"> => {{ event.useLabel }}</strong>\n                  </span>\n               </article>',
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
    template: '<div class="importer">\n                <div class="importer-ui">\n                    <h1><i class="icon-calendar"></i>Importer un ICS</h1>\n                    <nav class="steps">\n                        <span :class="{active: etape == 1}">Fichier ICS</span>\n                        <span :class="{active: etape == 2}">Cr\xE9neaux \xE0 importer</span>\n                        <span :class="{active: etape == 3}">Finalisation</span>\n\n                    </nav>\n\n                    <section class="etape1 row" v-if="etape == 1">\n                        <div class="col-md-1">Du</div>\n                        <div class="col-md-5">\n                            <datepicker v-model="periodStart"></datepicker>\n                        </div>\n\n                        <div class="col-md-1">au</div>\n                        <div class="col-md-5">\n                            <datepicker v-model="periodEnd"></datepicker>\n                        </div>\n\n                        <!-- P\xE9riode :\n                        <datepicker v-model="periodStart"></datepicker> au <datepicker v-model="periodEnd"></datepicker>\n                        -->\n                        <p>Choisissez un fichier ICS : </p>\n                        <input type="file" @change="loadIcsFile">\n                    </section>\n\n                    <section class="etape2" v-if="etape == 2">\n                        <h2><i class="icon-download-outline"></i>Aper\xE7u des donn\xE9es charg\xE9es</h2>\n                        <p>Voici les donn\xE9es charg\xE9es depuis le fichier ICS fournis : </p>\n                        <div class="calendar calendar-list">\n                            <article v-for="pack in packs">\n                                <section class="events">\n                                    <h3>{{ pack.label }}</h3>\n                                    <section class="events-list">\n                                        <eventitemimport :event="event" v-for="event in pack.events"></eventitemimport>\n                                    </section>\n                                </section>\n                            </article>\n                        </div>\n                        <div>\n                            <h2><i class="icon-loop-outline"></i>Correspondance des cr\xE9neaux</h2>\n                            <section class="correspondances"">\n                                <article v-for="label in labels">\n                                    <strong><span :style="{\'background\': background(label)}" class="square">&nbsp</span>{{ label }}</strong>\n                                    <select name="" id="" @change="updateLabel(label, $event.target.value)">\n                                        <option value="">Conserver</option>\n                                        <option value="ignorer">Ignorer ces cr\xE9neaux</option>\n                                        <option :value="creneau" v-for="creneau in creneaux">Placer dans {{ creneau }}</option>\n                                    </select>\n                                </article>\n                            </section>\n                        </div>\n                    </section>\n\n                    <div class="buttons">\n                        <button class="btn btn-default" @click="$emit(\'cancel\')">Annuler</button>\n                        <button class="btn btn-primary" @click="applyImport" v-if="etape==2">\n                            Valider l\'import de ces cr\xE9neaux\n                        </button>\n                    </div>\n                </div>\n            </div>',
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
        'datepicker': Datepicker,
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

            var analyser = new ICalAnalyser();
            var events = analyser.parse(ICAL.parse(content));
            this.importedEvents = [];
            this.labels = [];

            events.forEach(function (item) {
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);
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

var SelectEditable = {
    template: '<div>\n        <select v-model="selectedValue" @change="onSelectChange" class="form-control">\n            <option v-for="choose in chooses" :value="choose">{{ choose }}</option>\n            <option value="FREE">Autre&hellip;</option>\n        </select>\n        <input v-show="selectedValue == \'FREE\'" v-model="valueIn" @input="onInput" class="form-control" />\n    </div>',

    props: {
        'value': {
            default: ''
        },
        'chooses': {
            default: function _default() {
                return ["A", "B", "C"];
            }
        }
    },

    data: function data() {
        return {
            valueIn: this.value,
            editMode: false
        };
    },


    computed: {
        selectedValue: function selectedValue() {
            if (this.chooses.indexOf(this.valueIn) >= 0) {
                return this.valueIn;
            } else {
                return 'FREE';
            }
        }
    },

    watch: {
        value: function value(newV, oldV) {
            this.valueIn = newV;
        }
    },

    methods: {
        onInput: function onInput() {
            console.log('change', this.valueIn);
            this.$emit('input', this.valueIn, this.model);
        },
        onSelectChange: function onSelectChange(e) {
            if (e.target.value == "FREE") {
                this.valueIn = "";
            } else {
                this.valueIn = e.target.value;
            }
            this.onInput();
        }
    }
};

var Calendar = {

    template: '\n        <div class="calendar">\n\n            <importview :creneaux="labels" @cancel="importInProgress = false" @import="importEvents" v-if="importInProgress"></importview>\n\n            <div class="editor" v-show="eventEditDataVisible">\n                <form @submit.prevent="editSave">\n                    <div class="form-group">\n                        <label for="">Intitul\xE9</label>\n                        <selecteditable v-model="eventEditData.label" :chooses="labels"></selecteditable>\n                    </div>\n                    <div v-if="withOwner">\n                        {{ eventEditData.owner_id }}\n                        <select v-model="eventEditData.owner_id">\n                            <option :value="o.id" v-for="o in owners">{{ o.displayname }}</option>\n                        </select>\n                        D\xE9clarant LISTE\n                    </div>\n                    <div>\n                        <label for="">Description</label>\n                        <textarea class="form-control" v-model="eventEditData.description"></textarea>\n                    </div>\n                    <hr />\n                    <button type="button" @click="handlerEditCancelEvent" class="btn btn-primary">Annuler</button>\n                    <button type="cancel" @click="handlerSaveEvent" class="btn btn-default">Enregistrer</button>\n                </form>\n            </div>\n\n            <div class="editor" v-show="displayRejectModal">\n                <form @submit.prevent="handlerSendReject">\n                    <h3>Refuser des cr\xE9neaux</h3>\n                    <section>\n                       <article v-for="creneau in rejectedEvents" class="event-inline-simple">\n                        <i class="icon-archive"></i><strong>{{ creneau.label }}</strong><br>\n                        <i class="icon-user"></i><strong>{{ creneau.owner }}</strong><br>\n                        <i class="icon-calendar"></i><strong>{{ creneau.dayTime }}</strong>\n                       </article>\n                    </section>\n                    <div class="form-group">\n                        <label for="">Pr\xE9ciser la raison du refus</label>\n                        <textarea class="form-control" v-model="rejectComment" placeholder="Raison du refus"></textarea>\n                    </div>\n                    <hr />\n                    <button type="submit" class="btn btn-primary" :class="{disabled: !rejectComment}">Envoyer</button>\n                    <button type="cancel" class="btn btn-default" @click.prevent="displayRejectModal = false">Annuler</button>\n                </form>\n            </div>\n\n            <nav class="calendar-menu">\n                <nav class="views-switcher">\n                    <a href="#" @click.prevent="state = \'week\'" :class="{active: state == \'week\'}"><i class="icon-calendar"></i>{{ trans.labelViewWeek }}</a>\n                    <a href="#" @click.prevent="state = \'list\'" :class="{active: state == \'list\'}"><i class="icon-columns"></i>{{ trans.labelViewList }}</a>\n                    <a href="#" @click.prevent="importInProgress = true"><i class="icon-columns"></i>Importer un ICS</a>\n                </nav>\n                <section class="transmission errors">\n\n                    <p class="error" v-for="error in errors">\n                        <i class="icon-warning-empty"></i> {{ error }}\n                        <a href="#" @click.prevent="errors.splice(errors.indexOf(error), 1)" class="fermer">[fermer]</a>\n                    </p>\n                </section>\n                <section class="transmission infos" v-show="transmission">\n                    <span>\n                        <i class="icon-signal"></i>\n                        {{ transmission }}\n                    </span>\n                </section>\n            </nav>\n\n            <weekview v-show="state == \'week\'"\n                :create-new="createNew"\n                :with-owner="withOwner"\n                @editevent="handlerEditEvent"\n                @deleteevent="handlerDeleteEvent"\n                @createpack="handlerCreatePack"\n                @submitevent="handlerSubmitEvent"\n                @validateevent="handlerValidateEvent"\n                @rejectevent="handlerRejectEvent"\n                @createevent="handlerCreateEvent"\n                @savemoveevent="handlerSaveMove"\n                @submitday="submitday"\n                @submitall="submitall"\n                @saveevent="restSave"></weekview>\n\n            <listview v-show="state == \'list\'"\n                :with-owner="withOwner"\n                @editevent="handlerEditEvent"\n                @deleteevent="handlerDeleteEvent"\n                @validateevent="handlerValidateEvent"\n                @rejectevent="handlerRejectEvent"\n                @submitevent="handlerSubmitEvent"></listview>\n        </div>\n\n    ',

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
        importview: ImportICSView,
        selecteditable: SelectEditable
    },

    methods: (_methods = {
        /**
         * Envoi des données (de la semaine), @todo Faire la variante pour les mois.
         * @param status
         * @param period
         */
        submitall: function submitall(status, period) {
            var _this8 = this;

            var events = [];
            if (period == 'week') {
                this.events.forEach(function (event) {
                    if (store.inCurrentWeek(event) && event.sendable) {
                        events.push(event);
                    }
                });
            }
            if (events.length) {
                bootbox.confirm("Soumettre le(s) créneau(x) ?", function (confirm) {
                    if (confirm) _this8.restStep(events, status);
                });
            }
        },


        /**
         * Envoi des créneaux de la journée.
         *
         * @param day
         */
        submitday: function submitday(day) {
            var _this9 = this;

            // Liste des événements éligibles
            var events = [];
            this.events.forEach(function (event) {
                if (event.mmStart.format('YYYYMMDD') == day.format('YYYYMMDD') && event.sendable) {
                    events.push(event);
                }
            });

            // Envoi
            if (events.length) {
                bootbox.confirm("Soumettre le(s) créneau(x) ?", function (confirm) {
                    if (confirm) _this9.restStep(events, 'send');
                });
            }
        },
        importEvents: function importEvents(events) {
            var datas = [];
            events.forEach(function (item) {
                var event = JSON.parse(JSON.stringify(item));
                if (event.useLabel) event.label = event.useLabel;
                event.mmStart = moment(event.start);
                event.mmEnd = moment(event.end);
                datas.push(event);
            });
            this.importInProgress = false;
            this.restSave(datas);
        },
        handlerCreatePack: function handlerCreatePack(events) {
            this.restSave(events);
        },
        confirmImport: function confirmImport() {
            console.log('Tous ajouter');
        },
        handleradd: function handleradd(pack, event) {
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


        ////////////////////////////////////////////////////////////////////////

        handlerSendReject: function handlerSendReject() {
            var _this10 = this;

            console.log('Envoi du rejet', this.rejectComment);
            var events = [];
            this.rejectedEvents.forEach(function (event) {
                var e = JSON.parse(JSON.stringify(event));
                if (_this10.rejectValidateType == 'rejectsci') {
                    e.rejectedSciComment = _this10.rejectComment;
                } else if (_this10.rejectValidateType == 'rejectadm') {
                    e.rejectedAdminComment = _this10.rejectComment;
                }
                events.push(e);
            });
            this.restStep(events, this.rejectValidateType);
        },
        handlerRejectEvent: function handlerRejectEvent(event) {
            var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "unknow";

            this.showRejectModal([event], 'reject' + type);
        },
        handlerValidateEvent: function handlerValidateEvent(events) {
            var _this11 = this;

            var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "unknow";

            console.log("Validation", type);
            if (type == "sci" || type == "adm") {
                bootbox.confirm(type == 'sci' ? '<i class="icon-beaker"></i>   Validation scientifique' : '<i class="icon-archive"></i>   Validation administrative', function (response) {
                    if (response) {
                        _this11.restStep(events, 'validate' + type);
                    }
                });
            } else {
                throw "Type inconnue";
            }
        },
        showRejectModal: function showRejectModal(events, type) {
            this.displayRejectModal = true;
            this.rejectValidateType = type;
            this.rejectedEvents = events;
        },


        ////////////////////////////////////////////////////////////////////////

        restSave: function restSave(events) {
            var _this12 = this;

            if (this.restUrl) {
                this.transmission = "Enregistrement des données";
                var data = new FormData();
                for (var i = 0; i < events.length; i++) {
                    data.append('events[' + i + '][label]', events[i].label);
                    data.append('events[' + i + '][description]', events[i].description);
                    data.append('events[' + i + '][start]', events[i].mmStart.format());
                    data.append('events[' + i + '][end]', events[i].mmEnd.format());
                    data.append('events[' + i + '][id]', events[i].id || null);
                    data.append('events[' + i + '][owner_id]', events[i].owner_id || null);
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
                    _this12.handlerEditCancelEvent();
                }, function (error) {
                    console.log(error);
                    _this12.errors.push("Impossible d'enregistrer les données : " + error);
                }).then(function () {
                    return _this12.transmission = "";
                });
                ;
            }
        },
        restSend: function restSend(events) {
            this.restStep(events, 'send');
        },
        restValidate: function restValidate(events) {
            this.restStep(events, 'validate');
        },
        restStep: function restStep(events, action) {
            var _this13 = this;

            if (!Array.isArray(events)) {
                events = [events];
            }
            if (this.restUrl) {
                this.transmission = "Enregistrement en cours...";
                var data = new FormData();
                data.append('do', action);
                for (var i = 0; i < events.length; i++) {
                    console.log(events[i]);
                    data.append('events[' + i + '][id]', events[i].id || null);
                    data.append('events[' + i + '][rejectedSciComment]', events[i].rejectedSciComment || null);
                    data.append('events[' + i + '][rejectedAdminComment]', events[i].rejectedAdminComment || null);
                }

                this.$http.post(this.restUrl(), data).then(function (response) {
                    store.sync(response.body.timesheets);
                    _this13.displayRejectModal = false;
                    _this13.handlerEditCancelEvent();
                }, function (error) {
                    _this13.errors.push("Impossible de modifier l'état du créneau : " + error);
                }).then(function () {
                    _this13.transmission = "";
                });
            }
        },


        /** Suppression de l'événement de la liste */
        handlerDeleteEvent: function handlerDeleteEvent(event) {
            var _this14 = this;

            if (this.restUrl) {
                this.transmission = "Suppression...";
                this.$http.delete(this.restUrl() + "?timesheet=" + event.id).then(function (response) {
                    _this14.events.splice(_this14.events.indexOf(event), 1);
                }, function (error) {
                    console.log(error);
                }).then(function () {
                    _this14.transmission = "";
                });
            } else {
                this.events.splice(this.events.indexOf(event), 1);
            }
        },
        handlerSaveMove: function handlerSaveMove(event) {
            console.log('Sauvegarde de la position', event);
            var data = JSON.parse(JSON.stringify(event));
            data.mmStart = moment(data.start);
            data.mmEnd = moment(data.end);
            this.restSave([data]);
        },
        handlerSaveEvent: function handlerSaveEvent(event) {
            store.defaultLabel = this.eventEditData.label;
            /*
             var data = JSON.parse(JSON.stringify(this.eventEditData));
             data.mmStart = this.eventEdit.mmStart;
             data.mmEnd = this.eventEdit.mmEnd;
             this.restSave([data], 'new');
             /****/
        },


        /** Soumission de l'événement de la liste */
        handlerSubmitEvent: function handlerSubmitEvent(event) {
            var _this15 = this;

            store.defaultLabel = event.label;
            bootbox.confirm("Soumettre le(s) créneau(x) ?", function (confirm) {
                if (confirm) _this15.restSend([event]);
            });
        },


        /** Soumission de l'événement de la liste */
        handlerCreateEvent: function handlerCreateEvent(event) {
            this.handlerEditEvent(event);
        },


        /** Charge le fichier ICS depuis l'interface **/
        loadIcsFile: function loadIcsFile(e) {
            var _this16 = this;

            this.transmission = "Analyse du fichier ICS...";
            var fr = new FileReader();
            fr.onloadend = function (result) {
                _this16.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },


        /** Parse le contenu ICS **/
        parseFileContent: function parseFileContent(content) {
            var _this17 = this;

            var analyser = new ICalAnalyser();
            var events = analyser.parse(ICAL.parse(content));
            this.importedData = [];

            events.forEach(function (item) {
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);

                var currentPack = null;
                var currentLabel = item.mmStart.format('YYYY-MM-D');
                for (var i = 0; i < _this17.importedData.length && currentPack == null; i++) {
                    if (_this17.importedData[i].label == currentLabel) {
                        currentPack = _this17.importedData[i];
                    }
                }
                if (!currentPack) {
                    currentPack = {
                        label: currentLabel,
                        events: []
                    };
                    _this17.importedData.push(currentPack);
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
            var start = moment(this.currentDay).day(day).hour(time);
            var end = moment(start).add(2, 'hours');
            this.newEvent(new EventDT(null, this.defaultLabel, start.format(), end.format(), this.defaultDescription, {
                editable: true,
                deletable: true
            }));
        },
        editEvent: function editEvent(event) {
            console.log('editEvent(', event, ')');
            this.eventEdit = event;
            this.eventEditData = JSON.parse(JSON.stringify(event));
        }
    }, _defineProperty(_methods, 'editSave', function editSave() {
        var event = JSON.parse(JSON.stringify(this.eventEditData));
        event.mmStart = moment(event.start);
        event.mmEnd = moment(event.end);
        this.restSave([event]);
    }), _defineProperty(_methods, 'editCancel', function editCancel() {
        this.eventEdit = this.eventEditData = null;
    }), _defineProperty(_methods, 'fetch', function fetch() {
        var _this18 = this;

        this.transmission = "Chargement des créneaux...";

        this.$http.get(this.restUrl()).then(function (ok) {
            store.sync(ok.body.timesheets);
        }, function (ko) {
            _this18.errors.push("Impossible de charger les données : " + ko);
        }).then(function () {
            _this18.transmission = "";
        });
    }), _defineProperty(_methods, 'post', function post(event) {
        console.log("POST", event);
    }), _methods),

    mounted: function mounted() {
        if (this.customDatas) {
            var customs = this.customDatas();
            console.log(customs);
            for (var k in customs) {
                if (customs.hasOwnProperty(k)) {
                    colorLabels[k] = colorpool[customs[k].color];
                    if (!store.defaultLabel) {
                        store.defaultLabel = k;
                    }
                    store.labels.push(k);
                }
            }
            colorIndex++;
        }
        if (this.ownersList) {
            store.owners = this.ownersList();
            console.log('OWNERS', store.owners);
        }

        if (this.restUrl) {
            this.fetch();
        }
    }
};
return Calendar;
}));
