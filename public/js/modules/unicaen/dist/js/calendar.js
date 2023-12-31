;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['moment', 'ICalAnalyser', 'EventDT', 'Datepicker', 'bootbox', 'papa-parse'], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory(require('moment'), require('ICalAnalyser'), require('EventDT'), require('Datepicker'), require('bootbox'), require('papa-parse'));
  } else {
    root.Calendar = factory(root.moment, root.ICalAnalyser, root.EventDT, root.Datepicker, root.bootbox, root.Papa);
  }
}(this, function(moment, ICalAnalyser, EventDT, Datepicker, bootbox, Papa) {
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

        this.filterOwner = "";
        this.filterActivity = "";
        this.events = [];
        this.newID = 1;
        this.transmission = "";
        this.importInProgress = false;
        this.importedEvents = [];
        this.eventEditData = {};
        this.eventEditDataVisible = false;
        this.currentDay = moment();
        this.loading = true;
        this.remoteError = "";
        this.workPackageIndex = [];
        this.wps = null;
        this.activities = [];
        this.eventEdit = null;
        this.copyWeekData = null;
        this.copyDayData = null;
        this.generatedId = 0;
        this.totalWeek = 0;
        this.defaultLabel = "";
        this.tooltip = null;
        this.errors = [];
        this.listEventsOpen = [];

        this.ics = [];

        // Données pour transformer les créneaux longs
        this.transformLong = [{ startHours: 8, startMinutes: 0, endHours: 12, endMinutes: 0 }, { startHours: 13, startMinutes: 0, endHours: 17, endMinutes: 0 }];
        this.defaultDescription = "";
        this.status = {
            "draft": "Brouillon",
            "send": "En cours de validation",
            "valid": "Validé",
            "reject": "Rejeté",
            "info": "Information"
        };
        this.filterType = "";
        this.labels = [];
        this.owners = [];
        this.rejectShow = null;
        this.weekCredentials = this.defaultWeekCredentials();
        this.state = 'week';
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

        this.displayRejectModal = false;
        this.rejectValidateType = null;
        this.rejectComment = "";
        this.rejectedEvents = [];
    }

    _createClass(CalendarDatas, [{
        key: 'tooltipUpdate',
        value: function tooltipUpdate() {
            console.log(arguments);
        }
    }, {
        key: 'defaultWeekCredentials',
        value: function defaultWeekCredentials() {
            return {
                send: false,
                adm: false,
                sci: false,
                admdaily: [false, false, false, false, false, false, false, false],
                scidaily: [false, false, false, false, false, false, false, false],
                senddaily: [false, false, false, false, false, false, false, false],
                copydaily: [false, false, false, false, false, false, false, false],
                total: [0, 0, 0, 0, 0, 0, 0]
            };
        }

        /**
         * Retourne les données pour afficher la feuille de temps.
         */

    }, {
        key: 'timesheetDatas',
        value: function timesheetDatas() {
            var _this = this;

            var structuredDatas = {};
            var activityWpsIndex = {};

            for (var k in this.wps) {
                if (this.wps.hasOwnProperty(k)) {
                    if (!activityWpsIndex[this.wps[k].activity]) {
                        activityWpsIndex[this.wps[k].activity] = [];
                    }
                    activityWpsIndex[this.wps[k].activity].push(this.wps[k].code);
                }
            }

            this.workPackageIndex = [];
            for (var k in this.wps) {
                if (this.wps.hasOwnProperty(k)) {
                    this.workPackageIndex.push(this.wps[k].code);
                }
            }

            this.listEvents.forEach(function (event) {
                if (event.isValid) {

                    var packActivity = void 0,
                        packPerson = void 0,
                        packMonth = void 0,
                        packWeek = void 0,
                        packDay = void 0;
                    var activityLabel = _this.wps[event.label].activity;
                    var wpReference = activityWpsIndex[activityLabel];

                    // Regroupement par person
                    if (!structuredDatas[activityLabel]) {
                        structuredDatas[activityLabel] = {
                            label: activityLabel,
                            total: 0.0,
                            persons: {},
                            wps: wpReference
                        };
                    }
                    packActivity = structuredDatas[activityLabel];
                    packActivity.total += event.duration;

                    // Regroupement par person
                    if (!packActivity.persons[event.owner_id]) {
                        packActivity.persons[event.owner_id] = {
                            label: event.owner,
                            wps: packActivity.wps,
                            personid: event.owner_id,
                            total: 0.0,
                            totalWP: [],
                            months: {}
                        };
                        wpReference.forEach(function (value, i) {
                            packActivity.persons[event.owner_id].totalWP[i] = 0.0;
                        });
                    }
                    packPerson = packActivity.persons[event.owner_id];
                    packPerson.total += event.duration;

                    // Regroupement par mois
                    var monthKey = event.mmStart.format('MMMM YYYY');
                    if (!packPerson.months[monthKey]) {
                        packPerson.months[monthKey] = {
                            total: 0.0,
                            wps: [],
                            days: {}
                        };
                        wpReference.forEach(function (value, i) {
                            packPerson.months[monthKey].wps[i] = 0.0;
                        });
                    }
                    packMonth = packPerson.months[monthKey];
                    packMonth.total += event.duration;
                    var wpKey = wpReference.indexOf(_this.wps[event.label].code);
                    packMonth.wps[wpKey] += event.duration;
                    packPerson.totalWP[wpKey] += event.duration;

                    var dayKey = event.mmStart.format('dddd D MMMM YYYY');
                    if (!packMonth.days[dayKey]) {
                        packMonth.days[dayKey] = {
                            total: 0.0,
                            comments: "",
                            wps: []
                        };
                        wpReference.forEach(function (value, i) {
                            packMonth.days[dayKey].wps[i] = 0.0;
                        });
                    }

                    packDay = packMonth.days[dayKey];
                    packDay.wps[wpKey] += event.duration;
                    packDay.total += event.duration;
                    if (event.description) {
                        packDay.comments += event.description + "\n";
                    }
                }
            });
            return structuredDatas;
        }
    }, {
        key: 'copyDay',
        value: function copyDay(dt) {
            var _this2 = this;

            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach(function (event) {
                var dayRef = moment(event.start).format('MMMM D YYYY');
                if (dayRef == dDay) {
                    _this2.copyDayData.push({
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
            var _this3 = this;

            this.copyWeekData = [];
            this.events.forEach(function (event) {
                if (_this3.inCurrentWeek(event)) {
                    _this3.copyWeekData.push({
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
            var _this4 = this;

            if (this.copyWeekData) {
                var create = [];
                this.copyWeekData.forEach(function (event) {
                    var start = moment(_this4.currentDay);
                    start.day(event.day).hour(event.startHours).minute(event.startMinutes);

                    var end = moment(_this4.currentDay);
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
                    this.addNewEvent(datas[i]);
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
        key: 'getIcsByUid',
        value: function getIcsByUid(uid) {
            for (var i = 0; i < this.ics.length; i++) {
                if (this.ics[i].icsfileuid == uid) {
                    return this.ics[i];
                }
            }
            return null;
        }
    }, {
        key: 'addIcsRef',
        value: function addIcsRef(event) {
            this.ics.push({
                icsfileuid: event.icsfileuid,
                icsfilename: event.icsfilename,
                icsfiledateAdded: event.icsfiledateadded
            });
        }
    }, {
        key: 'addNewEvent',
        value: function addNewEvent(data) {
            if (data.icsfileuid && !this.getIcsByUid(data.icsfileuid)) this.addIcsRef(data);

            this.events.push(new EventDT(data));
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

    template: '<div class="event" :style="css"\n            @mouseenter="handlerTooltipOn(event, $event)"\n            @mouseleave="handlerTooltipOff(event, $event)"\n            @mousedown="handlerMouseDown"\n            :title="event.label"\n            :class="{\'event-changing\': changing, \'event-moving\': moving, \'event-selected\': selected, \'event-locked\': isLocked, \'status-external\': isExternal,\'status-info\': isInfo, \'status-draft\': isDraft, \'status-send\' : isSend, \'status-valid\': isValid, \'status-reject\': isReject, \'valid-sci\': isValidSci, \'valid-adm\': isValidAdm, \'reject-sci\':isRejectSci, \'reject-adm\': isRejectAdm}">\n        <div class="label" data-uid="UID">\n          {{ event.label }}\n        </div>\n        \n        <div class="description" v-if="!(isInfo || isExternal) ">\n            <div class="submit-status">\n                <span class="admin-status">\n                    <i class="icon-archive icon-admin" :class="adminState"></i> Admin\n                </span>\n                <span class="sci-status">\n                    <i class="icon-beaker icon-sci"></i> Scien.\n                </span>\n            </div>\n        </div>\n        <small>Dur\xE9e : <strong>{{ labelDuration }}</strong> heure(s)</small>\n\n        <div class="refus" @mouseover.prevent="showRefus != showRefus">\n            <div v-show="showRefus">\n                <i class="icon-beaker"></i>\n                Refus scientifique :\n                <div class="comment">{{ event.rejectedSciComment}}</div>\n                <i class="icon-archive"></i>\n                Refus administratif :\n                <div class="comment">{{ event.rejectedAdminComment}}</div>\n            </div>\n        </div>\n        \n        \n\n        <nav class="admin">\n            <a href="#" \n                @mousedown.stop.prevent="" \n                @click.stop.prevent="handlerShowReject(event)" \n                v-if="event.rejectedSciComment || event.rejectedAdminComment">\n                <i class="icon-attention"></i>\n                Afficher le rejet</a>\n                \n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'editevent\')" v-if="event.editable">\n                <i class="icon-pencil-1"></i>\n                Modifier</a>\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'deleteevent\')" v-if="event.deletable">\n                <i class="icon-trash-empty"></i>\n                Supprimer</a>\n\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'submitevent\')" v-if="event.sendable">\n                <i class="icon-right-big"></i>\n                Soumettre</a>\n\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'rejectscievent\')" v-if="event.validableSci">\n                <i class="icon-attention-1"></i>\n                Refus scientifique</a>\n\n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'rejectadmevent\')" v-if="event.validableAdm">\n                <i class="icon-attention-1"></i>\n                Refus administratif</a>\n                \n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'validatescievent\')" v-if="event.validableSci">\n                <i class="icon-beaker"></i>\n                Validation scientifique</a>\n            \n            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'validateadmevent\')" v-if="event.validableAdm">\n                <i class="icon-archive"></i>\n                Validation administrative</a>\n\n        </nav>\n\n        <div class="bottom-handler" v-if="event.editable" @mousedown.prevent.stop="handlerStartMovingEnd">\n            <span>===</span>\n        </div>\n\n        <time class="time start">{{ labelStart }}</time>\n        <time class="time end">{{ labelEnd }}</time>\n      </div>',

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
        isExternal: function isExternal() {

            return this.event.status == "conges" || this.event.status == "formation" || this.event.status == "enseignement" || this.event.status == "external";
        },
        colorLabel: function colorLabel() {
            return _colorLabel(this.event.label);
        },
        isLocked: function isLocked() {
            return this.event.isLocked;
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
        handlerTooltipOn: function handlerTooltipOn(event, e) {
            store.tooltip = {
                title: '<h3>' + event.label + '</h3>',
                event: event,
                x: '50px',
                y: '50px'
            };
        },
        handlerTooltipOff: function handlerTooltipOff(event, e) {
            store.tooltip = "";
        },

        /**
         * Déclenche l'affichage du rejet.
         *
         * @param event
         */
        handlerShowReject: function handlerShowReject(event) {
            this.$emit('rejectshow', event);
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

    template: '\n<div class="calendar calendar-week">\n    <div class="meta">\n        <a href="#" @click="previousWeek"><i class=" icon-angle-left"></i></a>\n        <h3>\n            Semaine {{ currentWeekNum}}, {{ currentMonth }} {{ currentYear }} \n            <small class="total-heures-semaine"> ({{ totalWeek }} heures)</small>  \n            <nav class="reject-valid-group">\n                <i class=" icon-angle-down"></i>\n                <ul>\n                    <li @click="copyCurrentWeek" v-if="createNew && (weekEvents && weekEvents.length > 0)"><i class="icon-docs"></i> Copier la semaine</li>\n                    <li @click="pasteWeek" v-if="createNew && copyWeekData"><i class="icon-paste"></i> Coller la semaine</li>\n                    <li @click="$emit(\'submitall\', \'send\', \'week\')" v-if="weekCredentials.send"><i class="icon-right-big"></i> Soumettre les cr\xE9neaux de la semaine</li>\n                    <li @click.prevent="handlerValidateSciWeek" v-if="weekCredentials.sci"><i class="icon-beaker"></i>Valider scientifiquement la semaine</li>\n                    <li @click.prevent="handlerRejectSciWeek" v-if="weekCredentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement la semaine</li>\n                    <li @click.prevent="handlerValidateAdmWeek" v-if="weekCredentials.adm"><i class="icon-archive"></i>Valider administrativement la semaine</li>\n                    <li @click.prevent="handlerRejectAdmWeek" v-if="weekCredentials.adm"><i class="icon-archive"></i>Rejeter administrativement la semaine</li>\n                </ul>\n            </nav>\n        </h3>\n        <a href="#" @click="nextWeek"><i class=" icon-angle-right"></i></a>\n    </div>\n    \n    <header class="line">\n        <div class="content-full" style="margin-right: 12px">\n            <div class="labels-time">\n            {{currentYear}}\n            </div>\n            <div class="events">\n                <div class="cell cell-day day day-1" :class="{today: isToday(day)}" v-for="day in currentWeekDays">\n                    {{ day.format(\'dddd D\') }}\n                    <nav class="reject-valid-group">\n                    <i class=" icon-angle-down"></i>\n                    <ul>\n                        <li @click="copyDay(day)" v-if="createNew && weekCredentials.copydaily[day.day()]"><i class="icon-docs"></i> Copier les cr\xE9neaux</li>\n                        <li @click="pasteDay(day)" v-if="createNew && copyDayData && copyDayData.length"><i class="icon-paste"></i> Coller les cr\xE9neaux</li>\n                        <li @click="submitDay(day)" v-if="weekCredentials.senddaily[day.day()]"><i class="icon-right-big"></i> Soumettre les cr\xE9neaux</li>\n                        <li @click.prevent="handlerValidateSciDay(day)" v-if="weekCredentials.scidaily[day.day()]"><i class="icon-beaker"></i>Valider scientifiquement la journ\xE9e</li>\n                        <li @click.prevent="handlerRejectSciDay(day)" v-if="weekCredentials.scidaily[day.day()]"><i class="icon-beaker"></i>Rejeter scientifiquement la journ\xE9e</li>\n                        <li @click.prevent="handlerValidateAdmDay(day)" v-if="weekCredentials.admdaily[day.day()]"><i class="icon-archive"></i>Valider administrativement la journ\xE9e</li>\n                        <li @click.prevent="handlerRejectAdmDay(day)" v-if="weekCredentials.admdaily[day.day()]"><i class="icon-archive"></i>Rejeter administrativement la journ\xE9e</li>\n                    </ul>\n                    </nav>\n                </div>\n            </div>\n        </div>\n    </header>\n\n    <div class="content-wrapper">\n        <div class="content-full">\n            <div class="labels-time">\n                <div class="unit timeinfo" v-for="time in 24">{{time-1}}:00</div>\n            </div>\n            <div class="events" :class="{\'drawing\': (gostDatas.editActive) }"\n                    @mouseup.self="handlerMouseUp"\n                    @mousedown.self="handlerMouseDown"\n                    @mousemove.self="handlerMouseMove">\n\n                <div class="cell cell-day day" v-for="day in 7" style="pointer-events: none">\n                    <div class="hour houroff" v-for="time in 6">&nbsp;</div>\n                    <div class="hour" v-for="time in 16" @dblclick="handlerCreate(day, time+5)">&nbsp;</div>\n                    <div class="hour houroff" v-for="time in 2">&nbsp;</div>\n                </div>\n                <div class="content-events">\n                    <div class="gost" :style="gostStyle" v-show="gostDatas.drawing">&nbsp;</div>\n                    <timeevent v-for="event in weekEvents"\n                            :with-owner="withOwner"\n                            :weekDayRef="currentDay"\n                            v-if="inCurrentWeek(event)"\n                            @deleteevent="$emit(\'deleteevent\', event)"\n                            @editevent="$emit(\'editevent\', event)"\n                            @submitevent="$emit(\'submitevent\', event)"\n                            @rejectscievent="$emit(\'rejectevent\', event, \'sci\')"\n                            @rejectadmevent="$emit(\'rejectevent\', event, \'adm\')"\n                            @validatescievent="$emit(\'validateevent\', event, \'sci\')"\n                            @validateadmevent="$emit(\'validateevent\', event, \'adm\')"\n                            @mousedown="handlerEventMouseDown"\n                            @savemoveevent="handlerSaveMove(event)"\n                            @onstartmoveend="handlerStartMoveEnd"\n                            @rejectshow="handlerRejectShow"\n                            :event="event"\n                            :key="event.id"></timeevent>\n                </div>\n            </div>\n        </div>\n    </div>\n   \n    <header class="line week-days-total">\n        <div class="content-full" style="margin-right: 12px">\n            <div class="labels-time">-</div>\n            <div class="events">\n                <div class="cell cell-day day day-1" v-for="t in weekCredentials.total">\n                    <strong>{{ t }}</strong> heure(s)\n                </div>\n            </div>\n        </div>\n    </header>\n</div>',

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


        /**
         * Retourne la liste des événements de la semaine en cours d'affichage.
         * @returns {Array}
         */
        weekEvents: function weekEvents() {
            var _this5 = this;

            var weekEvents = [];
            this.weekCredentials = store.defaultWeekCredentials();
            var totalW = 0;

            this.events.forEach(function (event) {
                // On filtre les événements de la semaine et le déclarant si besoin
                if (store.inCurrentWeek(event) && (store.filterActivity == '' || store.filterActivity == event.activityId) && (store.filterOwner == '' || store.filterOwner == event.owner_id) && (store.filterType == '' || store.filterType == event.status)) {
                    if (event.validableSci) {
                        _this5.weekCredentials.sci = true;
                        _this5.weekCredentials.scidaily[event.mmStart.day()] = true;
                    }
                    if (event.validableAdm) {
                        _this5.weekCredentials.adm = true;
                        _this5.weekCredentials.admdaily[event.mmStart.day()] = true;
                    }
                    if (event.sendable) {
                        _this5.weekCredentials.send = true;
                        _this5.weekCredentials.senddaily[event.mmStart.day()] = true;
                    }
                    _this5.weekCredentials.copydaily[event.mmStart.day()] = true;
                    _this5.weekCredentials.total[event.mmStart.day() - 1] += event.duration;
                    totalW += event.duration;
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

            this.totalWeek = totalW;

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
        handlerRejectShow: function handlerRejectShow(event) {
            this.$emit('rejectshow', event);
        },
        handlerValidateSciWeek: function handlerValidateSciWeek() {
            this.$emit('validateevent', this.weekEvents, 'sci');
        },
        handlerRejectSciWeek: function handlerRejectSciWeek() {
            this.$emit('rejectevent', this.weekEvents, 'sci');
        },
        handlerValidateAdmWeek: function handlerValidateAdmWeek() {
            this.$emit('validateevent', this.weekEvents, 'adm');
        },
        handlerRejectAdmWeek: function handlerRejectAdmWeek() {
            this.$emit('rejectevent', this.weekEvents, 'adm');
        },
        getEventsDay: function getEventsDay(day) {
            var events = [];
            this.weekEvents.forEach(function (event) {
                if (day.day() == event.mmStart.day()) {
                    events.push(event);
                }
            });
            return events;
        },
        handlerValidateSciDay: function handlerValidateSciDay(day) {
            this.$emit('validateevent', this.getEventsDay(day), 'sci');
        },
        handlerRejectSciDay: function handlerRejectSciDay(day) {
            this.$emit('rejectevent', this.getEventsDay(day), 'sci');
        },
        handlerValidateAdmDay: function handlerValidateAdmDay(day) {
            this.$emit('validateevent', this.getEventsDay(day), 'adm');
        },
        handlerRejectAdmDay: function handlerRejectAdmDay(day) {
            this.$emit('rejectevent', this.getEventsDay(day), 'adm');
        },


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
                this.createEvent(this.gostDatas.day, this.gostDatas.y / 40, this.gostDatas.height / 40 * 60);
            }

            if (this.gostDatas.eventActive) {
                this.gostDatas.eventActive.changing = false;
                this.gostDatas.eventActive.handlerMouseUp();
                this.gostDatas.eventActive = null;
            }

            if (this.gostDatas.eventMovedEnd) {
                this.gostDatas.eventMovedEnd.changing = false;
                this.gostDatas.eventMovedEnd.handlerMouseUp();
                this.gostDatas.eventMovedEnd = null;
            }
            this.gostDatas.startFrom = null;
            this.gostDatas.editActive = false;
        },
        handlerMouseDown: function handlerMouseDown(e) {
            if (this.createNew) {
                var roundFactor = 40 / 60 * this.pas;
                this.gostDatas.y = Math.round(e.offsetY / roundFactor) * roundFactor;
                var pas = $(e.target).width() / 7;
                var day = Math.floor(e.offsetX / pas);
                this.gostDatas.day = day + 1;
                this.gostDatas.x = day * pas;
                this.gostDatas.startX = this.gostDatas.x;
                this.gostDatas.drawing = true;
                this.gostDatas.editActive = true;
            }
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

            var hours = Math.floor(time);
            var minutes = Math.round((time - hours) * 60);
            var start = moment(this.currentDay).day(day).hour(time).minutes(minutes);
            var end = moment(start).add(duration, 'minutes');
            var newEvent = new EventDT({
                id: null,
                label: this.defaultLabel,
                start: start.format(),
                end: end.format(),
                description: this.defaultDescription,
                credentials: {
                    editable: true,
                    deletable: true
                }
            });
            this.$emit('createevent', newEvent);
        },
        copyDay: function copyDay(dt) {
            var _this6 = this;

            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach(function (event) {
                var dayRef = moment(event.start).format('MMMM D YYYY');
                if (dayRef == dDay) {
                    _this6.copyDayData.push({
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
            var _this7 = this;

            this.copyWeekData = [];
            this.events.forEach(function (event) {
                if (_this7.inCurrentWeek(event)) {
                    _this7.copyWeekData.push({
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
    template: '<article class="list-item" :style="css" :class="{\n                    \'event-editable\': event.editable, \n                    \'status-info\': event.isInfo, \n                    \'status-external\': event.isExternal,\n                    \'status-draft\': event.isDraft, \n                    \'status-send\' : event.isSend, \n                    \'status-valid\': event.isValid, \n                    \'status-reject\': event.isReject, \n                    \'valid-sci\': event.isValidSci, \n                    \'valid-adm\': event.isValidAdm, \n                    \'reject-sci\':event.isRejectSci, \n                    \'reject-adm\': event.isRejectAdm\n                    }">\n        <time class="start">{{ beginAt }}</time> -\n        <time class="end">{{ endAt }}</time>\n        <strong>{{ event.label }}</strong>\n        <div class="details">\n            <h4>\n                <i class="picto" :style="{background: colorLabel}"></i>\n                {{ event.label }} {{ event.status }}</h4>\n            <p class="time">\n                de <time class="start">{{ beginAt }}</time> \xE0 <time class="end">{{ endAt }}</time>, <em>{{ event.duration }}</em> heure(s) ~ \xE9tat : <em>{{ event.status }}</em>\n            </p>\n            <p class="small description">\n                {{ event.description }}\n            </p>\n         \n            <nav>\n                <button class="btn btn-default btn-xs" @click="$emit(\'selectevent\', event)">\n                    <i class="icon-calendar"></i>\n                Voir la semaine</button>\n\n                <button class="btn btn-primary btn-xs"  @click="$emit(\'editevent\', event)" v-if="event.editable">\n                    <i class="icon-pencil-1"></i>\n                    Modifier</button>\n\n                <button class="btn btn-primary btn-xs"  @click="$emit(\'submitevent\', event)" v-if="event.sendable">\n                    <i class="icon-right-big"></i>\n                    Soumettre</button>\n\n                <button class="btn btn-primary btn-xs"  @click="$emit(\'deleteevent\', event)" v-if="event.deletable">\n                    <i class="icon-trash-empty"></i>\n                    Supprimer</button>\n                    \n               <button class="btn btn-danger btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'rejectscievent\')" v-if="event.validableSci">\n                    <i class="icon-attention-1"></i>\n                    Refus scientifique</button>\n    \n                 <button class="btn btn-danger btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'rejectadmevent\')" v-if="event.validableAdm">\n                    <i class="icon-attention-1"></i>\n                    Refus administratif</button>\n                    \n                <button class="btn btn-success btn-xs"  @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'validatescievent\')" v-if="event.validableSci">\n                    <i class="icon-beaker"></i>\n                    Validation scientifique</button>\n                \n                 <button class="btn btn-success btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit(\'validateadmevent\')" v-if="event.validableAdm">\n                    <i class="icon-archive"></i>\n                    Validation administrative</button>\n            </nav>\n        </div>\n    </article>',
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
                top: this.event.decaleY * 1.75 + "em",
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
        },
        open: function open() {
            return store.listEventsOpen;
        }
    },

    props: ['withOwner'],

    components: {
        listitem: ListItemView
    },

    template: '<div class="calendar calendar-list">\n        <section v-for="eventsYear, year in listEvents" class="year-pack">\n            <h2 class="flex-position">\n                <strong>\n                    <span @click="toggle(year)">\n                        <i class="icon-right-dir" v-show="listEventsOpen.indexOf(year) == -1"></i>    \n                        <i class="icon-down-dir" v-show="listEventsOpen.indexOf(year) >= 0"></i>\n                        {{year}}\n                    </span>\n                    <nav class="reject-valid-group" v-if="eventsYear.credentials.actions">\n                        <i class=" icon-angle-down"></i>\n                        <ul>\n                            <li @click.prevent="performYear(eventsYear, \'submit\')" v-if="eventsYear.credentials.send"><i class="icon-right-big"></i> Soumettre les cr\xE9neaux de l\'ann\xE9e</li>\n                            <li @click.prevent="performYear(eventsYear, \'validatesci\')" v-if="eventsYear.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement l\'ann\xE9e</li>\n                            <li @click.prevent="performYear(eventsYear, \'rejectsci\')" v-if="eventsYear.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement l\'ann\xE9e</li>\n                            <li @click.prevent="performYear(eventsYear, \'validateadm\')" v-if="eventsYear.credentials.adm"><i class="icon-archive"></i>Valider administrativement l\'ann\xE9e</li>\n                            <li @click.prevent="performYear(eventsYear, \'rejectadm\')" v-if="eventsYear.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement l\'ann\xE9e</li>\n                            <li><i class="icon-archive"></i> Suprimmer les cr\xE9neaux affich\xE9s</li>\n                        </ul>\n                    </nav>\n                </strong>\n                <span class="onright total">{{ eventsYear.total }} heure(s)</span>\n            </h2>\n            <section v-for="eventsMonth, month in eventsYear.months" class="month-pack" v-show="listEventsOpen.indexOf(year) >= 0">\n                <h3 class="flex-position">\n                    <strong>  \n                    <span  @click="toggle(year+\'-\'+month)">\n                        <i class="icon-right-dir" v-show="listEventsOpen.indexOf(year+\'-\'+month) == -1"></i>    \n                        <i class="icon-down-dir" v-show="listEventsOpen.indexOf(year+\'-\'+month) >= 0"></i>\n                        {{month}}\n                    </span>\n                    <nav class="reject-valid-group" v-if="eventsMonth.credentials.actions">\n                        <i class=" icon-angle-down"></i>\n                        <ul>\n                            <li @click.prevent="performMonth(eventsMonth, \'submit\')" v-if="eventsMonth.credentials.send"><i class="icon-right-big"></i> Soumettre les cr\xE9neaux du mois</li>\n                            <li @click.prevent="performMonth(eventsMonth, \'validatesci\')" v-if="eventsMonth.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement le mois</li>\n                            <li @click.prevent="performMonth(eventsMonth, \'rejectsci\')" v-if="eventsMonth.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement le mois</li>\n                            <li @click.prevent="performMonth(eventsMonth, \'validateadm\')" v-if="eventsMonth.credentials.adm"><i class="icon-archive"></i>Valider administrativement le mois</li>\n                            <li @click.prevent="performMonth(eventsMonth, \'rejectadm\')" v-if="eventsMonth.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement le mois</li>\n                        </ul>\n                    </nav>\n                    </strong> \n                    <span class="onright total">{{eventsMonth.total}} heure(s)</span>\n                </h3>\n                <section v-for="eventsWeek, week in eventsMonth.weeks" class="week-pack" v-show="listEventsOpen.indexOf(year+\'-\'+month) >= 0">\n                    <h4 class="flex-position">\n                        <strong>Semaine {{week}} ~ \n                        <nav class="reject-valid-group" v-if="eventsWeek.credentials.actions">\n                            <i class=" icon-angle-down"></i>\n                            <ul>\n                                <li @click.prevent="performWeek(eventsWeek, \'submit\')" v-if="eventsWeek.credentials.send"><i class="icon-right-big"></i> Soumettre les cr\xE9neaux de la semaine</li>\n                                <li @click.prevent="performWeek(eventsWeek, \'validatesci\')" v-if="eventsWeek.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement la semaine</li>\n                                <li @click.prevent="performWeek(eventsWeek, \'rejectsci\')" v-if="eventsWeek.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement la semaine</li>\n                                <li @click.prevent="performWeek(eventsWeek, \'validateadm\')" v-if="eventsWeek.credentials.adm"><i class="icon-archive"></i>Valider administrativement la semaine</li>\n                                <li @click.prevent="performWeek(eventsWeek, \'rejectadm\')" v-if="eventsWeek.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement la semaine</li>\n                            </ul>\n                        </nav>\n                        </strong>                        \n                        <span class="onright total">{{eventsWeek.total}} heure(s)</span>\n                    </h4>\n                     <section v-for="eventsDay, day in eventsWeek.days" class="day-pack events">\n                        <h5>{{day}} \n                        <nav class="reject-valid-group" v-if="eventsDay.credentials.actions">\n                            <i class=" icon-angle-down"></i>\n                            <ul>\n                                <li @click.prevent="performDay(eventsDay, \'submit\')" v-if="eventsDay.credentials.send"><i class="icon-right-big"></i> Soumettre les cr\xE9neaux de la journ\xE9e</li>\n                                <li @click.prevent="performDay(eventsDay, \'validatesci\')" v-if="eventsDay.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement la journ\xE9e</li>\n                                <li @click.prevent="performDay(eventsDay, \'rejectsci\')" v-if="eventsDay.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement la journ\xE9e</li>\n                                <li @click.prevent="performDay(eventsDay, \'validateadm\')" v-if="eventsDay.credentials.adm"><i class="icon-archive"></i>Valider administrativement la journ\xE9e</li>\n                                <li @click.prevent="performDay(eventsDay, \'rejectadm\')" v-if="eventsDay.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement la journ\xE9e</li>\n                            </ul>\n                        </nav>\n                        </h5>\n                         <section class="events-list" :style="{ \'height\': eventsDay.persons.length*1.8 +\'em\' }">\n                            <listitem\n                                :with-owner="withOwner"\n                                @selectevent="selectEvent"\n                                @editevent="$emit(\'editevent\', event)"\n                                @deleteevent="$emit(\'deleteevent\', event)"\n                                @submitevent="$emit(\'submitevent\', event)"\n                                @rejectscievent="$emit(\'rejectevent\', event, \'sci\')"\n                                @rejectadmevent="$emit(\'rejectevent\', event, \'adm\')"\n                                @validatescievent="$emit(\'validateevent\', event, \'sci\')"\n                                @validateadmevent="$emit(\'validateevent\', event, \'adm\')"\n                                v-bind:event="event" v-for="event in eventsDay.events"></listitem>\n                        </section>\n                        <div class="total">\n                            {{eventsDay.total}} heure(s)\n                        </div>\n                    </section>\n                </section>\n            </section>\n        </section>\n        <div v-if="!listEvents" class="alert alert-danger">\n            Aucun cr\xE9neaux d\xE9t\xE9ct\xE9s\n        </div>\n    </div>',

    methods: {
        toggle: function toggle(tag) {
            if (store.listEventsOpen.indexOf(tag) == -1) {
                store.listEventsOpen.push(tag);
            } else {
                store.listEventsOpen.splice(store.listEventsOpen.indexOf(tag), 1);
            }
        },
        selectEvent: function selectEvent(event) {
            store.currentDay = moment(event.start);
            store.state = "week";
        },
        getMonthPack: function getMonthPack(pack) {
            var events = [];
            for (var k in pack.weeks) {
                if (pack.weeks.hasOwnProperty(k)) {
                    events = events.concat(this.getWeekPack(pack.weeks[k]));
                }
            }
            return events;
        },
        getWeekPack: function getWeekPack(pack) {
            var events = [];
            for (var k in pack.days) {
                if (pack.days.hasOwnProperty(k)) {
                    events = events.concat(this.getDayPack(pack.days[k]));
                }
            }
            return events;
        },
        getDayPack: function getDayPack(pack) {
            return pack.events;
        },
        performYear: function performYear(yearPack, action) {
            var events = [];
            for (var monthKey in yearPack.months) {
                if (yearPack.months.hasOwnProperty(monthKey)) {
                    events = events.concat(this.getMonthPack(yearPack.months[monthKey]));
                }
            }

            this.performEmit(events, action);
        },
        performMonth: function performMonth(monthPack, action) {
            this.performEmit(this.getMonthPack(monthPack), action);
        },
        performWeek: function performWeek(weekPack, action) {
            this.performEmit(this.getWeekPack(weekPack), action);
        },
        performDay: function performDay(dayPack, action) {
            this.performEmit(this.getDayPack(dayPack), action);
        },
        performEmit: function performEmit(events, action) {
            if (action == 'validatesci') {
                this.$emit('validateevent', events, 'sci');
            } else if (action == 'validateadm') {
                this.$emit('validateevent', events, 'adm');
            } else if (action == 'rejectsci') {
                this.$emit('rejectevent', events, 'sci');
            } else if (action == 'rejectadm') {
                this.$emit('rejectevent', events, 'adm');
            } else if (action == 'submit') {
                this.$emit('submitevent', events);
            }
        }
    }

}, 'computed', {
    listEvents: function listEvents() {

        if (!store.listEvents) {
            return null;
        }

        var structure = {};
        var owners = [];
        var events = store.listEvents;

        for (var i = 0; i < events.length; i++) {
            var event = events[i];
            if (!(store.filterActivity == '' || store.filterActivity == event.activityId)) continue;
            if (!(store.filterOwner == '' || store.filterOwner == event.owner_id)) continue;
            if (!(store.filterType == '' || store.filterType == event.status)) continue;

            var currentYear = void 0,
                currentMonth = void 0,
                currentWeek = void 0,
                currentDay = void 0;
            var duration = event.duration;
            var labelYear = event.mmStart.format('YYYY');
            var labelMonth = event.mmStart.format('MMMM');
            var labelWeek = event.mmStart.format('W');
            var labelDay = event.mmStart.format('ddd D');

            if (owners.indexOf(event.owner_id) < 0) {
                owners.push(event.owner_id);
            }

            if (!structure[labelYear]) {
                structure[labelYear] = {
                    total: 0.0,
                    months: {},
                    credentials: {
                        send: false,
                        sci: false,
                        adm: false,
                        actions: false
                    }
                };
            }
            currentYear = structure[labelYear];
            currentYear.total += duration;

            if (!currentYear.months[labelMonth]) {
                currentYear.months[labelMonth] = {
                    total: 0.0,
                    weeks: {},
                    credentials: {
                        send: false,
                        sci: false,
                        adm: false,
                        actions: false
                    }
                };
            }
            currentMonth = currentYear.months[labelMonth];
            currentMonth.total += duration;

            if (!currentMonth.weeks[labelWeek]) {
                currentMonth.weeks[labelWeek] = {
                    total: 0.0,
                    days: {},
                    credentials: {
                        send: false,
                        sci: false,
                        adm: false,
                        actions: false
                    }
                };
            }
            currentWeek = currentMonth.weeks[labelWeek];
            currentWeek.total += duration;

            if (!currentWeek.days[labelDay]) {
                currentWeek.days[labelDay] = {
                    total: 0.0,
                    persons: [],
                    events: [],
                    credentials: {
                        send: false,
                        sci: false,
                        adm: false,
                        actions: false
                    }
                };
            }
            currentDay = currentWeek.days[labelDay];
            currentDay.total += duration;
            if (currentDay.persons.indexOf(event.owner_id) < 0) {
                currentDay.persons.push(event.owner_id);
            }

            currentDay.events.push(event);

            event.decaleY = currentDay.persons.indexOf(event.owner_id);

            if (event.validableSci == true) {
                currentYear.credentials.sci = currentMonth.credentials.sci = currentWeek.credentials.sci = currentDay.credentials.sci = currentYear.credentials.actions = currentMonth.credentials.actions = currentWeek.credentials.actions = currentDay.credentials.actions = true;
            }
            if (event.validableAdm == true) {
                currentYear.credentials.adm = currentMonth.credentials.adm = currentWeek.credentials.adm = currentDay.credentials.adm = currentYear.credentials.actions = currentMonth.credentials.actions = currentWeek.credentials.actions = currentDay.credentials.actions = true;
            }
            if (event.sendable == true) {
                currentYear.credentials.send = currentMonth.credentials.send = currentWeek.credentials.send = currentDay.credentials.send = currentYear.credentials.actions = currentMonth.credentials.actions = currentWeek.credentials.actions = currentDay.credentials.actions = true;;
            }
        }

        return structure;
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
    template: '\n<div class="importer">\n    <div class="importer-ui">\n        \n        <ul class="nav nav-tabs" role="tablist">\n            <li role="presentation" class="active">\n                <a href="#import-newimport" data-toggle="tab">\n                    <i class="icon-calendar"></i>\n                    Nouvel import\n                </a>                        \n            </li>\n            <li role="presentation">\n                <a href="#import-importslist" data-toggle="tab">\n                    <i class="icon-history"></i>\n                    Historique des importations\n                </a>                        \n            </li>\n        </ul>\n        \n        <div class="tab-content">\n            <div role="tabpanel" class="tab-pane" id="import-importslist">\n                <article class="card" v-for="imp in existingIcs">\n                    <h3 class="card-title">\n                        {{ imp.icsfilename }}, le {{ imp.icsfiledateAdded | moment }}\n                    </h3>\n                    <small>UID : <strong>{{ imp.icsfileuid }} </strong></small>\n                    <nav>\n                        <a href="#" @click="$emit(\'deleteics\',imp.icsfileuid)" class="link"><i class="icon-trash"> supprimer</a>\n                    </nav>\n                </article>\n                <div class="buttons">\n                    <button class="btn btn-default" @click="$emit(\'cancel\')">Fermer</button>                   \n                </div>        \n            </div>\n            <div role="tabpanel" class="tab-pane active" id="import-newimport">\n                <h1><i class="icon-calendar"></i>Importer un ICS</h1>\n                <nav class="steps">\n                    <span :class="{active: etape == 1}">Fichier ICS</span>\n                    <span :class="{active: etape == 2}">Cr\xE9neaux \xE0 importer</span>\n                    <span :class="{active: etape == 3}">Finalisation</span>\n                </nav>\n\n                <section class="etape1 row" v-if="etape == 1">\n                    <div class="col-md-1">Du</div>\n                    <div class="col-md-5">\n                        <datepicker v-model="periodStart"></datepicker>\n                    </div>\n\n                    <div class="col-md-1">au</div>\n                    <div class="col-md-5">\n                        <datepicker v-model="periodEnd"></datepicker>\n                    </div>\n                    <p>Choisissez un fichier ICS : </p>\n                    <input type="file" @change="loadIcsFile">\n                </section>\n\n                <section class="etape2" v-if="etape == 2">\n                    <h2><i class="icon-download-outline"></i>Aper\xE7u des donn\xE9es charg\xE9es</h2>\n                    <p>Voici les donn\xE9es charg\xE9es depuis le fichier ICS fournis : </p>\n                    <div class="calendar calendar-list">\n                        <article v-for="pack in packs">\n                            <section class="events">\n                                <h3>{{ pack.label }}</h3>\n                                <section class="events-list">\n                                    <eventitemimport :event="event" v-for="event in pack.events"></eventitemimport>\n                                </section>\n                            </section>\n                        </article>\n                    </div>\n                    <div>\n                        <h2><i class="icon-loop-outline"></i>Correspondance des cr\xE9neaux</h2>\n                        <input v-model="search" placeholder="Filter les cr\xE9neaux">\n                        <section class="correspondances">\n                            <article v-for="label in labels" v-show="!search || (label && label.toLowerCase().indexOf(search.toLowerCase())) >= 0">\n                                <strong><span :style="{\'background\': background(label)}" class="square">&nbsp</span>{{ label }}</strong>\n                                <select v-model="associations[label]" id="" @change="updateLabel(label, $event.target.value)" class="form-control">\n                                    <option value="ignorer">Ignorer ces cr\xE9neaux</option>\n                                    <option value="">Conserver</option>\n                                    <option :value="creneau" v-for="creneau in creneaux">Placer dans {{ creneau }}</option>\n                                </select>\n                            </article>\n                        </section>\n                    </div>\n                </section>\n\n                <div class="buttons">\n                    <button class="btn btn-default" @click="$emit(\'cancel\')">Annuler</button>\n                    <button class="btn btn-primary" @click="applyImport" v-if="etape==2">\n                        Valider l\'import de ces cr\xE9neaux\n                    </button>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>',
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
            associations: [],
            labels: [],
            etape: 1,
            search: ""
        };
    },


    filters: {
        moment: function (_moment) {
            function moment(_x2) {
                return _moment.apply(this, arguments);
            }

            moment.toString = function () {
                return _moment.toString();
            };

            return moment;
        }(function (str) {
            var m = moment(str);
            return m.format('DD MMMM YYYY') + '(' + m.fromNow() + ')';
        })
    },

    components: {
        'datepicker': Datepicker,
        'eventitemimport': EventItemImport
    },

    computed: {
        workpackages: function workpackages() {
            return store.wps;
        },
        existingIcs: function existingIcs() {
            return store.ics;
        },
        packs: function packs() {
            var packs = [];
            this.importedEvents.forEach(function (item) {
                var currentPack = null;
                var currentLabel = item.mmStart.format('DD MMMM YYYY');
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
                        if (!item.description) item.description = from;
                        item.imported = true;
                    }
                });
            }
            this.associations[from] = to;
        },


        /** Charge le fichier ICS depuis l'interface **/
        loadIcsFile: function loadIcsFile(e) {
            var _this8 = this;

            var fr = new FileReader();
            fr.onloadend = function (result) {
                _this8.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },


        /** Parse le contenu ICS **/
        parseFileContent: function parseFileContent(content) {
            var _this9 = this;

            var analyser = new ICalAnalyser(new Date(), [{ startTime: '9:00', endTime: '12:30' }, { startTime: '14:00', endTime: '17:30' }]);
            var events = analyser.parse(ICAL.parse(content));
            var after = this.periodStart ? moment(this.periodStart) : null;
            var before = this.periodEnd ? moment(this.periodEnd) : null;
            var icsName = "";
            this.importedEvents = [];
            this.labels = [];

            // On précalcule les correspondances possibles entre les créneaux trouvés
            // et les informations disponibles sur les Workpackage
            console.log(store.wps);

            events.forEach(function (item) {
                icsName = item.icsfilename;
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);
                item.imported = false;
                item.useLabel = "";
                if ((after == null || item.mmStart > after) && (before == null || item.mmEnd < before)) {
                    _this9.importedEvents.push(item);
                    if (_this9.labels.indexOf(item.label) < 0) _this9.labels.push(item.label);
                } else {
                    console.log('Le créneau est hors limite');
                }
            });

            // En minuscule pour les test de proximité
            var icsNameLC = icsName.toLowerCase();

            var associationParser = function associationParser(label) {
                if (!label) return "";
                var out = "";
                label = label.toLowerCase();

                Object.keys(store.wps).map(function (objectKey, index) {
                    var wpDatas = store.wps[objectKey],
                        wpCode = wpDatas.code.toLowerCase(),
                        acronym = wpDatas.acronym.toLowerCase(),
                        code = wpDatas.activity_code.toLowerCase();
                    if (icsNameLC.indexOf(acronym) >= 0 || icsNameLC.indexOf(code) >= 0 || label.indexOf(acronym) >= 0 || label.indexOf(code) >= 0) {
                        if (label.indexOf(wpCode) >= 0) {
                            out = objectKey;
                        } else {
                            console.log("Pas de code WP");
                        }
                    } else {
                        // ou dans le label ...
                        console.log(icsNameLC, acronym, code, label);
                        console.log(icsNameLC.indexOf(acronym) >= 0 || icsNameLC.indexOf(code) >= 0);
                        console.log(label.indexOf(acronym) >= 0 || label.indexOf(code) >= 0);
                        console.log("Pas de code/acronyme");
                    }
                });
                return out;
            };

            // 'acronym'       => $wpd->getActivity()->getAcronym(),
            //     'activity'      => $wpd->getActivity()->__toString(),
            //     'activity_code' => $wpd->getActivity()->getOscarNum(),
            //     'idactivity'    => $wpd->getActivity()->getId(),
            //     'code' => $wpd->getCode(),


            var associations = {};
            for (var i = 0; i < this.labels.length; i++) {
                var label = this.labels[i];
                var corre = associationParser(label);
                console.log(corre);
                associations[label] = corre ? corre : "";
                if (corre) this.updateLabel(label, corre);else associations[label] = "ignorer";
            }

            /*
            if( store.wps  ){
                Object.keys(store.wps).map((objectKey, index) => {
                    associations[objectKey] = associationParser(store.wps[objectKey], this.labels);
                });
            }
            /****/

            this.associations = associations;

            this.importedEvents = EventDT.sortByStart(this.importedEvents);

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
            if (imported.length > 0) this.$emit('import', imported);
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

var TimesheetView = {
    props: ['withOwner'],
    data: function data() {
        return store;
    },

    computed: {
        colspan: function colspan() {
            return this.workPackageIndex.length;
        },
        structuredDatas: function structuredDatas() {
            return store.timesheetDatas();
        }
    },

    methods: {
        getBase64CSV: function getBase64CSV(datas) {
            var csv = [];
            var header = [datas.label].concat(datas.wps).concat(['comentaires', 'total']);
            csv.push(header);
            for (var month in datas.months) {
                if (datas.months.hasOwnProperty(month)) {
                    var day;

                    (function () {
                        var monthData = datas.months[month];

                        for (day in monthData.days) {
                            if (monthData.days.hasOwnProperty(day)) {
                                (function () {
                                    var dayData = monthData.days[day];
                                    var line = [day];
                                    dayData.wps.forEach(function (dayTotal) {
                                        line.push(dayTotal.toString().replace('.', ','));
                                    });
                                    line.push(dayData.comments);
                                    line.push(dayData.total.toString().replace('.', ','));
                                    csv.push(line);
                                })();
                            }
                        }

                        var monthLine = ['TOTAL pour ' + month];
                        monthData.wps.forEach(function (monthTotal) {
                            monthLine.push(monthTotal.toString().replace('.', ','));
                        });
                        monthLine.push('');
                        monthLine.push(monthData.total.toString().replace('.', ','));
                        csv.push(monthLine);
                    })();
                }
            }

            var finalLine = ["TOTAL"];
            datas.totalWP.forEach(function (totalCol) {
                finalLine.push(totalCol.toString().replace('.', ','));
            });
            finalLine.push('');
            finalLine.push(datas.total.toString().replace('.', ','));
            csv.push(finalLine);

            var str = Papa.unparse({
                data: csv,
                quotes: true,
                delimiter: ",",
                newline: "\r\n"
            });

            return 'data:application/octet-stream;base64,' + btoa(unescape(encodeURIComponent(str)));
        }
    },

    template: '<div class="timesheet"><h1> <i class="icon-file-excel"></i>Feuille de temps</h1>\n        <p class="help-block">Seul les d\xE9clarations <strong>valid\xE9es</strong> sont affich\xE9es ici.</p>\n        <section v-for="activityDatas in structuredDatas"> \n            <h2>\n                <i class="icon-cube"></i>\n                D\xE9clarations valid\xE9es pour <strong>{{ activityDatas.label }}</strong>\n            </h2>\n            <section v-for="personDatas in activityDatas.persons">\n                <table class="table table-bordered table-timesheet">\n                    <thead>\n                        <tr>\n                            <th>{{ personDatas.label }}</th>\n                            <th v-for="w in activityDatas.wps">{{ w }}</th>\n                            <th class="time">Commentaire(s)</th>\n                            <th class="time">Total</th>\n                        </tr>\n                    </thead>\n                    <tbody v-for="monthDatas, month in personDatas.months" class="person-tbody">\n                        <tr class="header-month">\n                            <th :colspan="monthDatas.wps.length + 3">{{ month }}</th>\n                        </tr>\n                        <tr v-for="dayDatas, day in monthDatas.days" class="data-day">\n                            <th>{{ day }}</th>\n                            <td v-for="tpsDay in dayDatas.wps" class="time">{{tpsDay}}</td>\n                            <td class="timesheet-comment">{{ dayDatas.comments }}</td>\n                            <th class="time">{{ dayDatas.total }}</th>\n                        </tr>\n                        <tr class="subtotal">\n                            <th>&nbsp;</th>\n                            <td v-for="tps in monthDatas.wps"  class="time">{{tps}}</td>\n                            <td>&nbsp;</td>\n                            <th class="time">{{ monthDatas.total }}</th>\n                        </tr>\n                    </tbody>\n                    <tfoot class="person-tfoot">\n                        <tr>\n                            <th>Total</th>\n                            <th v-for="totalWP in personDatas.totalWP" class="time">{{totalWP}}</th>\n                            <td>&nbsp;</td>\n                            <th class="time">{{ personDatas.total }}</th>\n                        </tr>\n                    </tfoot>\n                </table>\n                <nav class="text-right">\n                    <a :href="getBase64CSV(personDatas)" :download="\'Feuille-de-temps\' + personDatas.label + \'.csv\'" class="btn btn-primary btn-xs">\n                        <i class="icon-download-outline"></i>\n                        T\xE9l\xE9charger le CSV\n                    </a>\n                </nav>\n            </section>\n        </section>\n</div>'
};

var Calendar = {

    template: '\n        <div class="calendar">\n            \n            <transition name="fade">\n                <div class="calendar-tooltip" :class="\'status-\'+tooltip.event.status" v-if="tooltip">\n                    <h3><i class="picto"></i> {{ tooltip.event.label }}</h3>\n                    <p>Statut : <strong>{{ tooltip.event.status }}</strong></p>\n                    <p>D\xE9clarant : <strong>{{ tooltip.event.owner }}</strong>\n                        <span v-if="tooltip.event.sendAt">Envoy\xE9 le {{ tooltip.event.sendAt | moment }}</span>\n                    </p>\n                    \n                    <p v-if="tooltip.event.icsuid">\n                        N\xB0ICS : <strong>{{ tooltip.event.icsuid }}</strong><br />\n                        ICAL : <strong>{{ tooltip.event.icsfilename }}</strong> <small>({{ tooltip.event.icsfileuid }})</small>\n                    </p>\n                    <p>Dur\xE9e : <strong> {{ tooltip.event.duration }} heure(s)</strong></p>\n                    <p>Commentaire : <strong>{{ tooltip.event.description }}</strong></p>\n   \n                    <template v-if="tooltip.event.rejectedSciAt">\n                        <h4><i class="icon-beaker"></i> Refus scientifique</h4>\n                        Refus\xE9 par <strong>{{ tooltip.event.rejectedSciBy }}</strong> \n                        le <time>{{ tooltip.event.rejectedSciAt | moment }}</time>\n                        <p>Motif : <strong>{{ tooltip.event.rejectedSciComment }}</strong></p>\n                    </template>\n                    <template v-if="tooltip.event.validatedSciAt">\n                        <h4><i class="icon-beaker"></i> Validation scientifique</h4>\n                        Valid\xE9 par <strong>{{ tooltip.event.validatedSciBy }}</strong> \n                        le <time>{{ tooltip.event.validatedSciAt | moment }}</time>\n                    </template>\n                    <template v-if="tooltip.event.status == \'send\' && tooltip.event.validatedSciAt == null && tooltip.event.rejectedSciAt == null">\n                        <h4><i class="icon-archive"></i> Validation scientifique en attente...</h4>\n                    </template>                  \n                   \n                    <template v-if="tooltip.event.rejectedAdminAt">\n                        <h4><i class="icon-archive"></i> Refus administratif</h4>\n                        Refus\xE9 par <strong>{{ tooltip.event.rejectedAdminBy }}</strong> \n                        le <time>{{ tooltip.event.rejectedAdminAt | moment }}</time>\n                        <p>Motif : <strong>{{ tooltip.event.rejectedAdminComment }}</strong></p>    \n                    </template>\n                    <template v-if="tooltip.event.validatedAdminAt">\n                        <h4><i class="icon-archive"></i> Validation administrative</h4>\n                        Valid\xE9 par <strong>{{ tooltip.event.validatedAdminBy }}</strong> \n                        le <time>{{ tooltip.event.validatedAdminAt | moment }}</time>\n                    </template>\n                    <template v-if="tooltip.event.status == \'send\' && tooltip.event.validatedAdminAt == null && tooltip.event.rejectedAdminAt == null">\n                        <h4><i class="icon-archive"></i> Validation administrative en attente...</h4>\n                    </template>\n                    \n                    <template v-if="tooltip.event.status == \'draft\'">\n                        <h4><i class="icon-pencil"></i> Brouillon</h4>\n                        Ce cr\xE9neau n\'a pas encore \xE9t\xE9 soumis \xE0 validation.\n                    </template>\n                    \n                    <template v-if="tooltip.event.status == \'info\'">\n                        <h4><i class="icon-info"></i> Indicatif</h4>\n                        <p>Ce cr\xE9neau est ici \xE0 titre indicatif</p>\n                    </template>\n                </div>\n            </transition>\n\n            <importview :creneaux="labels" \n                    @cancel="importInProgress = false" \n                    @import="importEvents" \n                    v-if="importInProgress"\n                    @deleteics="handlerDeleteImport" \n                    ></importview>\n            \n            <transition name="fade">\n                <div class="vue-loader" v-if="remoteError" @click="remoteError = \'\'">\n                    <div>\n                        <h1>Erreur oscar</h1>\n                        <p>{{ remoteError }}</p>\n                    </div>\n                </div>\n            </transition>\n           \n            \n            <transition name="fade">\n                <div class="vue-loader" v-if="rejectShow">\n                    <div>\n                    <nav><a href="#" @click.prevent="rejectShow = null"><i class="icon-cancel-outline"></i>Fermer</a></nav>\n                    <section v-if="rejectShow.rejectedAdminAt" class="card">\n                        <h2>\n                            <i class="icon-archive">Rejet administratif\n                        </h2>\n                        Ce cr\xE9neau a \xE9t\xE9 refus\xE9 par <strong>{{ rejectShow.rejectedAdminBy }}</strong>  le <time>{{ rejectShow.rejectedAdminAt | moment}}</time> au motif : \n                        <pre>{{ rejectShow.rejectedAdminComment }}</pre>\n                    </section>\n                    <section v-if="rejectShow.rejectedSciAt" class="card">\n                        <h2>\n                            <i class="icon-archive">Rejet scientifique\n                        </h2>\n                        Ce cr\xE9neau a \xE9t\xE9 refus\xE9 par <strong>{{ rejectShow.rejectedSciBy }}</strong>  le <time>{{ rejectShow.rejectedSciAt | moment}}</time> au motif : \n                        <pre>{{ rejectShow.rejectedSciComment }}</pre>\n                    </section>\n                    </div>\n                </div>\n            </transition>\n        \n            <div class="vue-loader" v-if="loading">\n                <span>Chargement</span>\n            </div>\n            \n            <div class="editor" v-show="eventEditDataVisible">\n                <form @submit.prevent="editSave">\n                    <div class="form-group">\n                        <label for="">Intitul\xE9</label>\n                        <selecteditable v-model="eventEditData.label" :chooses="labels"></selecteditable>\n                    </div>\n                    <div v-if="withOwner">\n                        {{ eventEditData.owner_id }}\n                        <select v-model="eventEditData.owner_id">\n                            <option :value="o.id" v-for="o in owners">{{ o.displayname }}</option>\n                        </select>\n                        D\xE9clarant LISTE\n                    </div>\n                    <div>\n                        <label for="">Description</label>\n                        <textarea class="form-control" v-model="eventEditData.description"></textarea>\n                    </div>\n                    <hr />\n                    <button type="button" @click="handlerEditCancelEvent" class="btn btn-primary">Annuler</button>\n                    <button type="cancel" @click="handlerSaveEvent" class="btn btn-default">Enregistrer</button>\n                </form>\n            </div>\n\n            <div class="editor" v-show="displayRejectModal">\n                <form @submit.prevent="handlerSendReject">\n                    <h3>Refuser des cr\xE9neaux</h3>\n                    <div class="row">\n                        <section class="col-md-6 editor-column-fixed">\n                           <article v-for="creneau in rejectedEvents" class="event-inline-simple">\n                            <i class="icon-archive"></i><strong>{{ creneau.label }}</strong><br>\n                            <i class="icon-user"></i><strong>{{ creneau.owner }}</strong><br>\n                            <i class="icon-calendar"></i><strong>{{ creneau.dayTime }}</strong>\n                           </article>\n                        </section>\n                        <section class="col-md-6">\n                            <div class="form-group">\n                                <label for="">Pr\xE9ciser la raison du refus</label>\n                                <textarea class="form-control" v-model="rejectComment" placeholder="Raison du refus"></textarea>\n                            </div>\n                        </section>\n                    </div>\n                    <hr />\n                    <button type="submit" class="btn btn-primary" :class="{disabled: !rejectComment}">Envoyer</button>\n                    <button type="cancel" class="btn btn-default" @click.prevent="displayRejectModal = false">Annuler</button>\n                </form>\n            </div>\n\n            <nav class="calendar-menu">\n                <nav class="views-switcher">\n                    <a href="#" @click.prevent="state = \'week\'" :class="{active: state == \'week\'}"><i class="icon-calendar"></i>{{ trans.labelViewWeek }}</a>\n                    <a href="#" @click.prevent="state = \'list\'" :class="{active: state == \'list\'}"><i class="icon-columns"></i>{{ trans.labelViewList }}</a>\n                    <a href="#" @click.prevent="importInProgress = true" v-if="createNew"><i class="icon-columns"></i>Importer un ICS</a>\n                </nav>\n                 <template v-if="calendarLabelUrl.length">\n                        <span><a :href="calendarLabelUrl">{{ calendarLabel }}</a><span>\n                 </template>\n                 <template v-else><span>{{ calendarLabel }}</span></template>\n                    \n                <span v-if="owners.length"> \n                    <select v-model="filterOwner" class="input-sm">\n                      <option value="">Tous les d\xE9clarants</option>\n                      <option v-for="owner in owners" :value="owner.id">{{ owner.displayname }}</option>\n                    </select>\n                </span>\n                <span v-else>\n                    <select v-model="filterActivity" class="input-sm">\n                        <option value="">Toutes</option>\n                        <option v-for="a in activities" :value="a.id">{{ a.label }}</option>\n                    </select>\n                </span>\n                <select v-model="filterType" class="input-sm">\n                    <option value="">Tous les \xE9tats</option>\n                    <option v-for="label, key in status" :value="key">{{ label }}</option>\n                </select>\n                    \n                <section class="transmission errors">\n\n                    <p class="error" v-for="error in errors">\n                        <i class="icon-warning-empty"></i> {{ error }}\n                        <a href="#" @click.prevent="errors.splice(errors.indexOf(error), 1)" class="fermer">[fermer]</a>\n                    </p>\n                </section>\n                <section class="transmission infos" v-show="transmission">\n                    <span>\n                        <i class="icon-signal"></i>\n                        {{ transmission }}\n                    </span>\n                </section>\n            </nav>\n\n            <weekview v-if="state == \'week\'"\n                :create-new="createNew"\n                :with-owner="withOwner"\n                @editevent="handlerEditEvent"\n                @deleteevent="handlerDeleteEvent"\n                @createpack="handlerCreatePack"\n                @submitevent="handlerSubmitEvent"\n                @validateevent="handlerValidateEvent"\n                @rejectevent="handlerRejectEvent"\n                @createevent="handlerCreateEvent"\n                @savemoveevent="handlerSaveMove"\n                @submitday="submitday"\n                @submitall="submitall"\n                @rejectshow="handlerRejectShow"\n                @saveevent="restSave"></weekview>\n\n            <listview v-if="state == \'list\'"\n                :with-owner="withOwner"\n                @editevent="handlerEditEvent"\n                @deleteevent="handlerDeleteEvent"\n                @validateevent="handlerValidateEvent"\n                @rejectevent="handlerRejectEvent"\n                @submitevent="handlerSubmitEvent"></listview>\n        </div>\n\n    ',

    data: function data() {
        return store;
    },


    props: {
        withOwner: {
            default: false
        },

        createNew: {
            default: false,
            type: Boolean
        },

        calendarLabel: {
            default: "Label par défaut"
        },

        calendarLabelUrl: {
            default: ""
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

    filters: {
        moment: function (_moment2) {
            function moment(_x3) {
                return _moment2.apply(this, arguments);
            }

            moment.toString = function () {
                return _moment2.toString();
            };

            return moment;
        }(function (value) {
            var d = moment(value.date);
            return d.format("dddd, D MMMM YYYY") + " (" + d.fromNow() + ")";
        })
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
            var _this10 = this;

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
                    if (confirm) _this10.restStep(events, status);
                });
            }
        },
        handlerRejectShow: function handlerRejectShow(event) {
            this.rejectShow = event;
        },


        /**
         * Envoi des créneaux de la journée.
         *
         * @param day
         */
        submitday: function submitday(day) {
            var _this11 = this;

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
                    if (confirm) _this11.restStep(events, 'send');
                });
            }
        },
        getEventByIcsUid: function getEventByIcsUid(uid) {
            for (var i = 0; i < this.events.length; i++) {
                if (this.events[i].icsuid == uid) {
                    return this.events[i];
                }
            }
            return null;
        },
        importEvents: function importEvents(events) {
            var _this12 = this;

            var datas = [];
            events.forEach(function (item) {

                var event = JSON.parse(JSON.stringify(item)),
                    exist = _this12.getEventByIcsUid(item.icsuid),
                    itemStart = moment(event.start),
                    itemEnd = moment(event.end),
                    duration = itemEnd - itemStart;

                if (exist) {
                    event.id = exist.id;
                }

                if (event.useLabel) event.label = event.useLabel;

                if (duration / 1000 / 60 / 60 > 9) {
                    _this12.transformLong.forEach(function (transform) {
                        var itemTransformed = JSON.parse(JSON.stringify(event));
                        itemStart.hours(transform.startHours).minutes(transform.startMinutes);
                        itemEnd.hours(transform.endHours).minutes(transform.endMinutes);
                        itemTransformed.start = itemStart.format();
                        itemTransformed.end = itemEnd.format();
                        itemTransformed.mmStart = moment(itemTransformed.start);
                        itemTransformed.mmEnd = moment(itemTransformed.end);;
                        datas.push(itemTransformed);
                    });
                } else {
                    event.mmStart = moment(event.start);
                    event.mmEnd = moment(event.end);

                    datas.push(event);
                }

                if (event.useLabel) event.label = event.useLabel;
            });
            this.importInProgress = false;

            this.restSave(datas);
        },
        handlerCreatePack: function handlerCreatePack(events) {
            this.restSave(events);
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
            var _this13 = this;

            var events = [];
            this.rejectedEvents.forEach(function (event) {
                var e = JSON.parse(JSON.stringify(event));
                if (_this13.rejectValidateType == 'rejectsci') {
                    e.rejectedSciComment = _this13.rejectComment;
                } else if (_this13.rejectValidateType == 'rejectadm') {
                    e.rejectedAdminComment = _this13.rejectComment;
                }
                events.push(e);
            });
            this.restStep(events, this.rejectValidateType);
        },


        /**
         * Déclenche la prodécure de rejet.
         *
         * @param event
         * @param type
         */
        handlerRejectEvent: function handlerRejectEvent(event) {
            var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "unknow";

            // événements reçus
            var eventsArray = !event.length ? [event] : event,
                events = [];

            eventsArray.forEach(function (event) {
                if (type == "sci" && event.validableSci || type == "adm" && event.validableAdm) {
                    events.push(event);
                }
            });

            if (events.length) {
                this.showRejectModal(events, 'reject' + type);
            } else {
                bootbox.alert("Aucun créneaux ne peut être rejeté");
            }
        },
        handlerValidateEvent: function handlerValidateEvent(events) {
            var _this14 = this;

            var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "unknow";

            // événements reçus
            var eventsArray = !events.length ? [events] : events,
                events = [];

            eventsArray.forEach(function (event) {
                if (type == "sci" && event.validableSci || type == "adm" && event.validableAdm) {
                    events.push(event);
                }
            });

            if (events.length) {
                var message = events.length == 1 ? " du créneau " : " des " + events.length + " créneaux ";
                bootbox.confirm(type == 'sci' ? '<i class="icon-beaker"></i> Validation scientifique ' + message : '<i class="icon-archive"></i>   Validation administrative' + message, function (response) {
                    if (response) {
                        _this14.restStep(events, 'validate' + type);
                    }
                });
            } else {
                bootbox.alert("Aucun créneau ne peut être validé");
            }
        },
        showRejectModal: function showRejectModal(events, type) {
            this.displayRejectModal = true;
            this.rejectValidateType = type;
            this.rejectedEvents = events;
        },


        ////////////////////////////////////////////////////////////////////////

        restSave: function restSave(events) {
            var _this15 = this;

            if (this.restUrl) {
                this.transmission = "Enregistrement des données";
                var data = new FormData();

                var datas = [];

                for (var i = 0; i < events.length; i++) {
                    // Fix seconds bug
                    events[i].mmStart.seconds(0);
                    events[i].mmEnd.seconds(0);

                    var jsonData = {
                        'label': events[i].label,
                        'icsuid': events[i].icsuid,
                        'icsfileuid': events[i].icsfileuid,
                        'icsfilename': events[i].icsfilename,
                        'description': events[i].description,
                        'start': events[i].mmStart.format(),
                        'end': events[i].mmEnd.format(),
                        'id': events[i].id || null,
                        'owner_id': events[i].owner_id || ''
                    };

                    if (this.customDatas) {
                        var customs = this.customDatas();
                        for (var k in customs) {
                            if (customs.hasOwnProperty(k) && events[i].label == k) {
                                for (var l in customs[k]) {
                                    if (customs[k].hasOwnProperty(l)) jsonData[l] = customs[k][l];
                                }
                            }
                        }
                    }
                    datas.push(jsonData);
                }
                data.append('events', JSON.stringify(datas));

                store.loading = true;
                this.$http.post(this.restUrl(), data).then(function (response) {
                    store.sync(response.body.timesheets);
                    _this15.handlerEditCancelEvent();
                }, function (error) {
                    require(['bootbox'], function (bootbox) {
                        bootbox.alert("ERROR : " + error);
                    });
                    _this15.errors.push("Impossible d'enregistrer les données : " + error);
                }).then(function () {
                    _this15.transmission = "";
                    store.loading = false;
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
            var _this16 = this;

            if (!Array.isArray(events)) {
                events = [events];
            }
            if (this.restUrl) {
                this.transmission = "Enregistrement en cours...";
                var data = new FormData();
                data.append('do', action);
                var datas = [];
                for (var i = 0; i < events.length; i++) {
                    datas.push({
                        id: events[i].id || null,
                        rejectedSciComment: events[i].rejectedSciComment || null,
                        rejectedAdminComment: events[i].rejectedAdminComment || null
                    });
                }
                data.append('events', JSON.stringify(datas));
                this.loading = true;
                this.$http.post(this.restUrl(), data).then(function (response) {
                    store.sync(response.body.timesheets);
                    _this16.displayRejectModal = false;
                    _this16.handlerEditCancelEvent();
                }, function (error) {
                    _this16.errors.push("Impossible de modifier l'état du créneau : " + error);

                    _this16.remoteError = "Erreur : " + error.statusText;
                }).then(function () {
                    _this16.transmission = "";
                    _this16.loading = false;
                });
            }
        },
        handlerDeleteImport: function handlerDeleteImport(icsuid) {
            var _this17 = this;

            console.log("Suppression des événements issues de l'import", icsuid);
            this.transmission = "Suppression...";
            this.$http.delete(this.restUrl() + "?icsuid=" + icsuid).then(function (response) {
                store.events = [];
                _this17.fetch();
            }, function (error) {
                console.log(error);
                require(['bootbox'], function (bootbox) {
                    bootbox.alert("ERROR : " + error.body);
                });
                store.errors.push(error);
            }).then(function () {
                _this17.transmission = "";
            });
        },


        /** Suppression de l'événement de la liste */
        handlerDeleteEvent: function handlerDeleteEvent(event) {
            var _this18 = this;

            if (this.restUrl) {
                this.transmission = "Suppression...";
                this.$http.delete(this.restUrl() + "?timesheet=" + event.id).then(function (response) {
                    _this18.events.splice(_this18.events.indexOf(event), 1);
                }, function (error) {
                    console.log(error);
                }).then(function () {
                    _this18.transmission = "";
                });
            } else {
                this.events.splice(this.events.indexOf(event), 1);
            }
        },
        handlerSaveMove: function handlerSaveMove(event) {
            var data = JSON.parse(JSON.stringify(event));
            data.mmStart = moment(data.start);
            data.mmEnd = moment(data.end);
            this.restSave([data]);
        },
        handlerSaveEvent: function handlerSaveEvent(event) {
            store.defaultLabel = this.eventEditData.label;
        },


        /** Soumission de l'événement de la liste */
        handlerSubmitEvent: function handlerSubmitEvent(event) {
            var _this19 = this;

            var events;
            if (event.length) {
                events = event;
            } else {
                store.defaultLabel = event.label;
                events = [event];
            }

            // On liste des événements 'soumettable'
            var eventsSend = [];
            for (var i = 0; i < events.length; i++) {
                if (events[i].sendable === true) {
                    eventsSend.push(events[i]);
                }
            }

            if (eventsSend.length) {
                bootbox.confirm("Soumettre le(s) " + eventsSend.length + " créneau(x) ?", function (confirm) {
                    if (confirm) _this19.restSend(eventsSend);
                });
            } else {
                bootbox.alert("Aucun créneau à envoyer.");
            }
        },


        /** Soumission de l'événement de la liste */
        handlerCreateEvent: function handlerCreateEvent(event) {
            this.handlerEditEvent(event);
        },


        /** Charge le fichier ICS depuis l'interface **/
        loadIcsFile: function loadIcsFile(e) {
            var _this20 = this;

            this.transmission = "Analyse du fichier ICS...";
            var fr = new FileReader();
            fr.onloadend = function (result) {
                _this20.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },


        /** Parse le contenu ICS **/
        parseFileContent: function parseFileContent(content) {
            var _this21 = this;

            var analyser = new ICalAnalyser(new Date(), [{ startTime: '9:00', endTime: '12:30' }, { startTime: '14:00', endTime: '17:30' }]);

            var events = analyser.parse(ICAL.parse(content));
            this.importedData = [];

            events.forEach(function (item) {
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);

                var currentPack = null;
                var currentLabel = item.mmStart.format('YYYY-MM-D');
                for (var i = 0; i < _this21.importedData.length && currentPack == null; i++) {
                    if (_this21.importedData[i].label == currentLabel) {
                        currentPack = _this21.importedData[i];
                    }
                }
                if (!currentPack) {
                    currentPack = {
                        label: currentLabel,
                        events: []
                    };
                    _this21.importedData.push(currentPack);
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
            this.newEvent(new EventDT({
                id: null,
                label: this.defaultLabel, start: start.format(), end: end.format(), description: this.defaultDescription, credentials: {
                    editable: true,
                    deletable: true
                }
            }));
        },
        editEvent: function editEvent(event) {
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
        var _this22 = this;

        this.ics = [];
        this.transmission = "Chargement des créneaux...";
        store.loading = true;

        this.$http.get(this.restUrl()).then(function (ok) {
            store.sync(ok.body.timesheets);
            store.loading = false;
        }, function (ko) {
            _this22.errors.push("Impossible de charger les données : " + ko);
            store.remoteError = "Impossible de charger des créneaux";
        }).then(function () {
            _this22.transmission = "";
            store.loading = false;
        });
    }), _defineProperty(_methods, 'post', function post(event) {
        console.log("POST", event);
    }), _methods),

    mounted: function mounted() {
        var allowState = ['week', 'list', 'timesheet'];

        this.state = 'week';
        if (allowState.indexOf(window.location.hash.substring(1)) >= 0) {
            this.state = window.location.hash.substring(1);
        }

        if (this.customDatas) {
            var customs = this.customDatas();
            this.wps = customs;

            for (var k in customs) {
                if (customs.hasOwnProperty(k)) {
                    store.activities.pushIfNot({
                        id: customs[k].idactivity,
                        label: customs[k].activity
                    }, 'id');

                    colorLabels[k] = colorpool[customs[k].color];

                    if (customs[k].active) {
                        if (!store.defaultLabel) {
                            store.defaultLabel = k;
                        }
                        store.labels.push(k);
                    }
                }
            }
            colorIndex++;
        }

        if (this.ownersList) {
            store.owners = this.ownersList();
        }

        if (this.restUrl) {
            this.fetch();
        }
    }
};

/**
 * Ajoute l'objet dans le tableau si une valeur de la clef spécifiée n'est pas déjà présente.
 *
 * @param obj
 * @param testField
 */
Array.prototype.pushIfNot = function (obj, testField) {
    var add = true,
        val = obj[testField];
    for (var i = 0; i < this.length; i++) {
        if (this[i][testField] == obj[testField]) add = false;
    }
    if (add) this.push(obj);
};
return Calendar;
}));
