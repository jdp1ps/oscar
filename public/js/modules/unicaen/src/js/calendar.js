moment.locale('fr');

var colorLabels = {};
var colorIndex = 0;
var colorpool = ['#fcdc80', '#a6cef8', '#9fd588', '#fb90bb', '#e5fbed', '#99a0ce', '#bca078', '#f3cafd', '#d9f4c1', '#60e3bb', '#f2c7f5', '#f64bc0', '#ffc1b2', '#fc9175', '#d7fc74', '#e3d7f8', '#9ffab3', '#d6cbac', '#4dd03c', '#f8f3be'];

var colorLabel = (label) => {
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

class CalendarDatas {
    constructor() {
        this.state = 'week';
        this.filterOwner = "";
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
        this.eventEdit = null;
        this.copyWeekData = null;
        this.copyDayData = null;
        this.generatedId = 0;
        this.defaultLabel = "";
        this.errors = [];
        this.defaultDescription = "";
        this.labels = [];
        this.owners = [];
        this.rejectShow = null;
        this.weekCredentials = this.defaultWeekCredentials();

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

    defaultWeekCredentials() {
        return {
            send: false,
            adm: false,
            sci: false,
            admdaily: [false, false, false, false, false, false, false, false],
            scidaily: [false, false, false, false, false, false, false, false],
            senddaily: [false, false, false, false, false, false, false, false],
            copydaily: [false, false, false, false, false, false, false, false]
        };
    }

    downloadTimesheet(personid, activityid){
        console.log(personid, activityid);
    }

    /**
     * Retourne les données pour afficher la feuille de temps.
     */
    timesheetDatas(){
        console.log('Accès aux données pour la feuille de temps');
        let structuredDatas = {};
        let activityWpsIndex = {};

        for (var k in this.wps) {
            if (this.wps.hasOwnProperty(k)) {
                if( !activityWpsIndex[this.wps[k].activity] ){
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

        this.listEvents.forEach((event) => {
            if (event.isValid) {

                let packActivity, packPerson, packMonth, packWeek, packDay;
                let activityLabel = this.wps[event.label].activity;
                let wpReference = activityWpsIndex[activityLabel];

                // Regroupement par person
                if (!structuredDatas[activityLabel]) {
                    console.log("event",event);
                    structuredDatas[activityLabel] = {
                        label: activityLabel,
                        total: 0.0,
                        persons: {},
                        wps: wpReference
                    }
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
                        months: {}
                    }
                }
                packPerson = packActivity.persons[event.owner_id];
                packPerson.total += event.duration;

                // Regroupement par mois
                let monthKey = event.mmStart.format('MMMM YYYY');


                if (!packPerson.months[monthKey]) {
                    packPerson.months[monthKey] = {
                        total: 0.0,
                        wps: []
                    };
                    wpReference.forEach((value, i) => {
                        packPerson.months[monthKey].wps[i] = 0.0;
                    });
                }
                packMonth = packPerson.months[monthKey];
                packMonth.total += event.duration;
                let wpKey = wpReference.indexOf(this.wps[event.label].code);
                packMonth.wps[wpKey] += event.duration;
            }
        });
        return structuredDatas;
    }

    get listEvents() {
        EventDT.sortByStart(this.events);
        return this.events;
    }

    get today() {
        return moment();
    }

    get firstEvent() {

    }

    get lastEvent() {

    }

    get currentYear() {
        return this.currentDay.format('YYYY')
    }

    get currentMonth() {
        return this.currentDay.format('MMMM')
    }

    get currentWeekKey() {
        return this.currentDay.format('YYYY-W')
    }

    get currentWeekDays() {
        let days = [], day = moment(this.currentDay.startOf('week'));

        for (let i = 0; i < 7; i++) {
            days.push(moment(day.format()));
            day.add(1, 'day');
        }
        return days;
    }

    copyDay(dt) {
        this.copyDayData = [];
        var dDay = dt.format('MMMM D YYYY');
        this.events.forEach((event) => {
            var dayRef = moment(event.start).format('MMMM D YYYY');
            if (dayRef == dDay) {
                this.copyDayData.push(
                    {
                        startHours: event.mmStart.hour(),
                        startMinutes: event.mmStart.minute(),
                        endHours: event.mmEnd.hour(),
                        endMinutes: event.mmEnd.minute(),
                        label: event.label,
                        description: event.description
                    }
                );
            }
        });
    }

    ////////////////////////////////////////////////////////////////////////
    /**
     * Copie les créneaux de la semaine en cours d'affichage.
     */
    copyCurrentWeek() {
        this.copyWeekData = [];
        this.events.forEach((event) => {
            if (this.inCurrentWeek(event)) {
                this.copyWeekData.push({
                    day: event.mmStart.day(),
                    startHours: event.mmStart.hour(),
                    startMinutes: event.mmStart.minute(),
                    endHours: event.mmEnd.hour(),
                    endMinutes: event.mmEnd.minute(),
                    label: event.label,
                    description: event.description
                });
            }
        })
    }

    /**
     * Colle les créneaux en mémoire (jour) dans le jour spécifié.
     *
     * @param day
     * @returns {*}
     */
    pasteDay(day) {
        if (this.copyDayData) {
            var create = [];

            this.copyDayData.forEach((event) => {
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
    pasteWeek() {
        if (this.copyWeekData) {
            var create = [];
            this.copyWeekData.forEach((event) => {
                var start = moment(this.currentDay);
                start.day(event.day).hour(event.startHours).minute(event.startMinutes);

                var end = moment(this.currentDay);
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
    previousWeek() {
        this.currentDay = moment(this.currentDay).add(-1, 'week');
    }

    /**
     * Affiche la semaine suivante.
     */
    nextWeek() {
        this.currentDay = moment(this.currentDay).add(1, 'week');
    }

    /**
     * Création d'un nouveau créneau à partir du EventDT transmis en paramètre.
     * @param evt EventDT
     */
    newEvent(evt) {
        evt.id = this.generatedId++;
        this.events.push(evt)
    }

    inCurrentWeek(event) {
        return event.inWeek(this.currentDay.year(), this.currentDay.week());
    }

    sync(datas) {
        for (var i = 0; i < datas.length; i++) {
            let local = this.getEventById(datas[i].id);
            if (local) {
                local.sync(datas[i]);
            } else {
                this.addNewEvent(datas[i]);
            }
        }
    }

    getEventById(id) {
        for (let i = 0; i < this.events.length; i++) {
            if (this.events[i].id == id) {
                return this.events[i];
            }
        }
        return null;
    }

    addNewEvent(data) {
        this.events.push(
            new EventDT(data)
        );
    }
}
var store = new CalendarDatas();


var TimeEvent = {

    template: `<div class="event" :style="css"

            @mousedown="handlerMouseDown"
            :title="event.label"
            :class="{'event-changing': changing, 'event-moving': moving, 'event-selected': selected, 'event-locked': isLocked, 'status-info': isInfo, 'status-draft': isDraft, 'status-send' : isSend, 'status-valid': isValid, 'status-reject': isReject, 'valid-sci': isValidSci, 'valid-adm': isValidAdm, 'reject-sci':isRejectSci, 'reject-adm': isRejectAdm}">
        <div class="label" data-uid="UID">
          {{ event.label }}
        </div>
        <small>Durée : <strong>{{ labelDuration }}</strong> heure(s)</small>
        <div class="description" v-if="!isInfo">
            <div class="submit-status">
            <span class="admin-status">
                <i class="icon-archive icon-admin" :class="adminState"></i> Admin
            </span>
            <span class="sci-status">
                <i class="icon-beaker icon-sci"></i> Scien.
            </span>
          </div>
            <p v-if="withOwner">Déclarant <strong>{{ event.owner }}</strong></p>
          {{ event.description }}
          
        </div>

        <div class="refus" @mouseover.prevent="showRefus != showRefus">
            <div v-show="showRefus">
                <i class="icon-beaker"></i>
                Refus scientifique :
                <div class="comment">{{ event.rejectedSciComment}}</div>
                <i class="icon-archive"></i>
                Refus administratif :
                <div class="comment">{{ event.rejectedAdminComment}}</div>
            </div>
        </div>

        <nav class="admin">
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="handlerDebug(event)">
                <i class="icon-bug"></i>
                Debug</a>
                
            <a href="#" 
                @mousedown.stop.prevent="" 
                @click.stop.prevent="handlerShowReject(event)" 
                v-if="event.rejectedSciComment || event.rejectedAdminComment">
                <i class="icon-attention"></i>
                Afficher le rejet</a>
                
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('editevent')" v-if="event.editable">
                <i class="icon-pencil-1"></i>
                Modifier</a>
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('deleteevent')" v-if="event.deletable">
                <i class="icon-trash-empty"></i>
                Supprimer</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('submitevent')" v-if="event.sendable">
                <i class="icon-right-big"></i>
                Soumettre</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectscievent')" v-if="event.validableSci">
                <i class="icon-attention-1"></i>
                Refus scientifique</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectadmevent')" v-if="event.validableAdm">
                <i class="icon-attention-1"></i>
                Refus administratif</a>
                
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('validatescievent')" v-if="event.validableSci">
                <i class="icon-beaker"></i>
                Validation scientifique</a>
            
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('validateadmevent')" v-if="event.validableAdm">
                <i class="icon-archive"></i>
                Validation administrative</a>

        </nav>

        <div class="bottom-handler" v-if="event.editable" @mousedown.prevent.stop="handlerStartMovingEnd">
            <span>===</span>
        </div>

        <time class="time start">{{ labelStart }}</time>
        <time class="time end">{{ labelEnd }}</time>
      </div>`,

    props: ['event', 'weekDayRef', 'withOwner'],

    data(){
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
        }
    },

    filters: {
        hour(mm){
            return mm.format('H:mm')
        },
        dateFull(mm){
            return mm.format('D MMMM YYYY, h:mm')
        }
    },

    computed: {

        adminState(){
            return (
                this.event.rejectedAdminAt ? 'rejected' : (this.event.validatedAdminAt ? 'validated' : 'waiting')
            )
        },

        sciStatus(){
            if (this.event.rejectedSciAt) {
                return "Rejet administratif";
            }
            else if (this.event.validatedSciAt) {
                return "Validation administrative le à";
            }
            else {
                return "en attente de validation";
            }
        },

        css(){
            var marge = 0;
            var sizeless = 0;
            if (this.event.intersect > 0) {
                sizeless = 3;
                marge = sizeless / this.event.intersect * this.event.intersectIndex;
            }
            return {
                'pointer-events': this.changing ? 'none' : 'auto',
                height: (this.pixelEnd - this.pixelStart) + 'px',
                background: this.withOwner ? colorLabel(this.event.owner) : colorLabel(this.event.label),
                position: "absolute",
                //'opacity': (this.changing ? '1' : 'inherit'),
                top: this.pixelStart + 'px',
                width: ((100 / 7) - 1) - sizeless + "%",
                left: ((this.weekDay - 1) * 100 / 7) + marge + "%"
            }
        },

        ///////////////////////////////////////////////////////////////// STATUS
        isDraft(){
            return this.event.status == "draft";
        },
        isSend(){
            return this.event.status == "send";
        },
        isValid(){
            return this.event.status == "valid";
        },
        isValidSci(){
            return this.event.validatedSciAt != null;
        },
        isValidAdm(){
            return this.event.validatedAdminAt != null;
        },
        isRejectSci(){
            return this.event.rejectedSciAt != null;
        },
        isRejectAdm(){
            return this.event.rejectedAdminAt != null;
        },

        isReject(){
            return this.event.status == "reject";
        },
        isInfo(){
            return this.event.status == "info";
        },

        colorLabel(){
            return colorLabel(this.event.label);
        },

        isLocked(){
            return this.event.isLocked;
        },

        dateStart(){
            return moment(this.event.start);
        },

        dateEnd(){
            return moment(this.event.end);
        },

        pixelStart(){
            return this.dateStart.hour() * 40 + (40 / 60 * this.dateStart.minutes());
        },

        pixelEnd(){
            return this.dateEnd.hour() * 40 + (40 / 60 * this.dateEnd.minutes());
        },

        weekDay() {
            return this.dateStart.day()
        }
    },

    watch: {
        'event.start': function () {
            this.labelStart = this.dateStart.format('H:mm');
        },
        'event.end': function () {
            this.labelEnd = this.dateEnd.format('H:mm');
        }
    },

    methods: {
        /**
         * Déclenche l'affichage du rejet.
         *
         * @param event
         */
        handlerShowReject(event){
            this.$emit('rejectshow', event);
        },

        handlerDebug(data){
            console.log(data);
        },

        updateWeekDay(value){
            var start = this.dateStart.day(value);
            var end = this.dateEnd.day(value);
            this.event.start = start.format();
            this.event.end = end.format();
        },

        handlerShowRefus(){
            bootbox.alert({
                size: "small",
                title: '<i class="icon-beaker"></i>   Refus scientifique',
                message: '<em>Motif : </em>' + this.event.rejectedSciComment + ""
            })
        },
        handlerShowRefusAdmin(){
            bootbox.alert({
                size: "small",
                title: '<i class="icon-archive"></i>   Refus administratif',
                message: '<em>Motif : </em>' + this.event.rejectedAdminComment + ""
            })
        },

        move(event){
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

        updateLabel(){
            var dtUpdate = this.topToStart();
            this.labelDuration = dtUpdate.duration;
            this.labelStart = dtUpdate.startLabel;
            this.labelEnd = dtUpdate.endLabel;
        },

        handlerEndMovingEnd(){
            if (this.movingBoth) {
                this.movingBoth = false;
            }
        },

        handlerStartMovingEnd(e){
            /*this.movingBoth = false;
             this.startMoving(e);*/
            this.$emit('onstartmoveend', this);
        },

        startMoving(e){
            if (this.event.editable) {
                this.startX = e.clientX;
                this.selected = true;
                this.moving = true;
                this.$el.addEventListener('mousemove', this.move);
                this.$el.addEventListener('mouseup', this.handlerMouseUp);
            }
        },

        handlerMouseDown(e){
            if (this.event.editable) {
                this.changing = true;
                this.$emit('mousedown', this, e);
            }

        },

        handlerMouseUp(e){
            if (this.event.editable) {
                console.log('UPDATE now');
                this.moving = false;
                this.$el.removeEventListener('mousemove', this.move);

                var dtUpdate = this.topToStart();

                this.event.start = this.dateStart
                    .hours(dtUpdate.startHours)
                    .minutes(dtUpdate.startMinutes)
                    .format();

                this.event.end = this.dateEnd
                    .hours(dtUpdate.endHours)
                    .minutes(dtUpdate.endMinutes)
                    .format();

                if (this.change) {
                    console.log('trigger update');
                    this.change = false;
                    this.$emit('savemoveevent', this.event);
                }
            }
        },

        handlerMouseOut(e){
            this.handlerMouseUp()
        },

        roundMinutes(minutes){
            return Math.floor(60 / 40 * minutes / 15) * 15
        },

        formatZero(int){
            return int < 10 ? '0' + int : int;
        },

        ////////////////////////////////////////////////////////////////////////
        topToStart(){
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
                duration: formatDuration(((endHours * 60 + endMinutes) - (startHours * 60 + startMinutes)) * 60),
                startLabel: this.formatZero(startHours) + ':' + this.formatZero(startMinutes),
                endLabel: this.formatZero(endHours) + ':' + this.formatZero(endMinutes)
            };
        }
    },

    mounted(){
        this.labelStart = this.dateStart.format('H:mm');
        this.labelEnd = this.dateEnd.format('H:mm');
        this.labelDuration = formatDuration(this.dateEnd.unix() - this.dateStart.unix());
    }
};


var formatDuration = (milliseconde) => {
    var h = Math.floor(milliseconde / 60 / 60);
    var m = (milliseconde - (h * 60 * 60)) / 60;
    return h + (m ? 'h' + m : '');
}

var WeekView = {
    data(){
        return store
    },

    props: {
        'withOwner': {default: false},
        'createNew': {default: false},
        'pas': {default: 15}
    },

    components: {
        'timeevent': TimeEvent
    },

    template: `<div class="calendar calendar-week">
    <div class="meta">
        <a href="#" @click="previousWeek">
            <i class=" icon-angle-left"></i>
        </a>
        <h3>
            Semaine {{ currentWeekNum}}, {{ currentMonth }} {{ currentYear }}
            
             <nav class="reject-valid-group">
                <i class=" icon-angle-down"></i> actions
                <ul>
                    <li @click="copyCurrentWeek" v-if="createNew && (weekEvents && weekEvents.length > 0)"><i class="icon-docs"></i> Copier la semaine</li>
                    <li @click="pasteWeek" v-if="createNew && copyWeekData"><i class="icon-paste"></i> Coller la semaine</li>
                    <li @click="$emit('submitall', 'send', 'week')" v-if="weekCredentials.send"><i class="icon-right-big"></i> Soumettre les créneaux de la semaine</li>
                    <li @click.prevent="handlerValidateSciWeek" v-if="weekCredentials.sci"><i class="icon-beaker"></i>Valider scientifiquement la semaine</li>
                    <li @click.prevent="handlerRejectSciWeek" v-if="weekCredentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement la semaine</li>
                    <li @click.prevent="handlerValidateAdmWeek" v-if="weekCredentials.adm"><i class="icon-archive"></i>Valider administrativement la semaine</li>
                    <li @click.prevent="handlerRejectAdmWeek" v-if="weekCredentials.adm"><i class="icon-archive"></i>Rejeter administrativement la semaine</li>
                </ul>
            </nav>
            
        </h3>
       <a href="#" @click="nextWeek">
            <i class=" icon-angle-right"></i>
       </a>
    </div>

    <header class="line">
        <div class="content-full" style="margin-right: 12px">
            <div class="labels-time">
                {{currentYear}}
            </div>
            <div class="events">
                <div class="cell cell-day day day-1" :class="{today: isToday(day)}" v-for="day in currentWeekDays">
                    {{ day.format('dddd D') }}
                    <nav class="reject-valid-group">
                        <i class=" icon-angle-down"></i>
                        <ul>
                            <li @click="copyDay(day)" v-if="createNew && weekCredentials.copydaily[day.day()]"><i class="icon-docs"></i> Copier les créneaux</li>
                            <li @click="pasteDay(day)" v-if="createNew && copyDayData && copyDayData.length"><i class="icon-paste"></i> Coller les créneaux</li>
                            <li @click="submitDay(day)" v-if="weekCredentials.senddaily[day.day()]"><i class="icon-right-big"></i> Soumettre les créneaux</li>
                            <li @click.prevent="handlerValidateSciDay(day)" v-if="weekCredentials.scidaily[day.day()]"><i class="icon-beaker"></i>Valider scientifiquement la journée</li>
                            <li @click.prevent="handlerRejectSciDay(day)" v-if="weekCredentials.scidaily[day.day()]"><i class="icon-beaker"></i>Rejeter scientifiquement la journée</li>
                            <li @click.prevent="handlerValidateAdmDay(day)" v-if="weekCredentials.admdaily[day.day()]"><i class="icon-archive"></i>Valider administrativement la journée</li>
                            <li @click.prevent="handlerRejectAdmDay(day)" v-if="weekCredentials.admdaily[day.day()]"><i class="icon-archive"></i>Rejeter administrativement la journée</li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="content-wrapper">
        <div class="content-full">
          <div class="labels-time">
            <div class="unit timeinfo" v-for="time in 24">{{time-1}}:00</div>
          </div>
          <div class="events" :class="{'drawing': (gostDatas.editActive) }"
                   @mouseup.self="handlerMouseUp"
                   @mousedown.self="handlerMouseDown"
                   @mousemove.self="handlerMouseMove">

              <div class="cell cell-day day" v-for="day in 7" style="pointer-events: none">
                <div class="hour houroff" v-for="time in 6">&nbsp;</div>
                <div class="hour" v-for="time in 16"
                    @dblclick="handlerCreate(day, time+5)">&nbsp;</div>
                <div class="hour houroff" v-for="time in 2">&nbsp;</div>
              </div>
              <div class="content-events">
                <div class="gost"
                    :style="gostStyle" v-show="gostDatas.drawing">&nbsp;</div>
                <timeevent v-for="event in weekEvents"
                    :with-owner="withOwner"
                    :weekDayRef="currentDay"
                    v-if="inCurrentWeek(event)"
                    @deleteevent="$emit('deleteevent', event)"
                    @editevent="$emit('editevent', event)"
                    @submitevent="$emit('submitevent', event)"
                    @rejectscievent="$emit('rejectevent', event, 'sci')"
                    @rejectadmevent="$emit('rejectevent', event, 'adm')"
                    @validatescievent="$emit('validateevent', event, 'sci')"
                    @validateadmevent="$emit('validateevent', event, 'adm')"
                    @mousedown="handlerEventMouseDown"
                    @savemoveevent="handlerSaveMove(event)"
                    @onstartmoveend="handlerStartMoveEnd"
                    @rejectshow="handlerRejectShow"
                    :event="event"
                    :key="event.id"></timeevent>
              </div>
          </div>
        </div>
    </div>

    </div>`,

    computed: {
        currentYear(){
            return this.currentDay.format('YYYY')
        },
        currentMonth(){
            return this.currentDay.format('MMMM')
        },
        currentWeekKey(){
            return this.currentDay.format('YYYY-W')
        },
        currentWeekNum(){
            return this.currentDay.format('W')
        },

        currentWeekDays(){
            let days = [], day = moment(this.currentDay.startOf('week'));

            for (let i = 0; i < 7; i++) {
                days.push(moment(day.format()));
                day.add(1, 'day');
            }
            return days;
        },

        /**
         * Retourne la liste des événements de la semaine en cours d'affichage.
         * @returns {Array}
         */
        weekEvents(){


            var weekEvents = [];
            this.weekCredentials = store.defaultWeekCredentials();

            this.events.forEach(event => {
                // On filtre les événements de la semaine et le déclarant si besoin
                if (store.inCurrentWeek(event) && (store.filterOwner == '' || store.filterOwner == event.owner_id )) {
                    if (event.validableSci) {
                        this.weekCredentials.sci = true;
                        this.weekCredentials.scidaily[event.mmStart.day()] = true;
                    }
                    if (event.validableAdm) {
                        this.weekCredentials.adm = true;
                        this.weekCredentials.admdaily[event.mmStart.day()] = true;
                    }
                    if (event.sendable) {
                        this.weekCredentials.send = true;
                        this.weekCredentials.senddaily[event.mmStart.day()] = true;
                    }
                    this.weekCredentials.copydaily[event.mmStart.day()] = true;
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

        gostStyle(){
            return {
                'left': this.gostDatas.x + "px",
                'top': this.gostDatas.y + "px",
                'width': '13.2857%',
                'pointer-events': 'none',
                'height': this.gostDatas.height + "px",
                'position': 'absolute'
            }
        }
    },

    methods: {
        handlerRejectShow(event){
            this.$emit('rejectshow', event);
        },

        handlerValidateSciWeek(){
            this.$emit('validateevent', this.weekEvents, 'sci');
        },
        handlerRejectSciWeek(){
            this.$emit('rejectevent', this.weekEvents, 'sci');
        },
        handlerValidateAdmWeek(){
            this.$emit('validateevent', this.weekEvents, 'adm');
        },
        handlerRejectAdmWeek(){
            this.$emit('rejectevent', this.weekEvents, 'adm');
        },

        getEventsDay(day){
            var events = [];
            this.weekEvents.forEach(event => {
                if (day.day() == event.mmStart.day()) {
                    events.push(event);
                }
            });
            return events;
        },

        handlerValidateSciDay(day){
            this.$emit('validateevent', this.getEventsDay(day), 'sci');
        },
        handlerRejectSciDay(day){
            this.$emit('rejectevent', this.getEventsDay(day), 'sci');
        },
        handlerValidateAdmDay(day){
            this.$emit('validateevent', this.getEventsDay(day), 'adm');
        },
        handlerRejectAdmDay(day){
            this.$emit('rejectevent', this.getEventsDay(day), 'adm');
        },

//        @savemoveevent="handlerSaveMove"
        handlerEventMouseDown(event, evt){
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
        handlerStartMoveEnd(event){
            this.gostDatas.eventMovedEnd = event;
            this.gostDatas.editActive = true;
            this.gostDatas.eventMovedEnd.changing = true;
        },

        handlerSaveMove(event){
            this.$emit('savemoveevent', event);
        },

        handlerMouseUp(e){
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

        handlerMouseDown(e){
            console.log("MouseDown", this.createNew == false);
            if (this.createNew) {
                var roundFactor = 40 / 60 * this.pas;
                this.gostDatas.y = Math.round(e.offsetY / roundFactor) * (roundFactor);
                var pas = $(e.target).width() / 7;
                var day = Math.floor(e.offsetX / pas);
                this.gostDatas.day = day + 1;
                this.gostDatas.x = day * pas;
                this.gostDatas.startX = this.gostDatas.x;
                this.gostDatas.drawing = true;
                this.gostDatas.editActive = true;
            }
        },

        handlerMouseMove(e){
            if (this.gostDatas.drawing) {
                this.gostDatas.height = Math.round((e.offsetY - this.gostDatas.y) / (40 / 60 * this.pas)) * (40 / 60 * this.pas);
            }
            else if (this.gostDatas.eventActive) {
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
            }
            else if (this.gostDatas.eventMovedEnd) {
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

        handlerCreate(day, time){
            this.createEvent(day, time);
        },

        createEvent(day, time, duration = 120){
            var start = moment(this.currentDay).day(day).hour(time);
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

        copyDay(dt){
            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach((event) => {
                var dayRef = moment(event.start).format('MMMM D YYYY');
                if (dayRef == dDay) {
                    this.copyDayData.push(
                        {
                            startHours: event.mmStart.hour(),
                            startMinutes: event.mmStart.minute(),
                            endHours: event.mmEnd.hour(),
                            endMinutes: event.mmEnd.minute(),
                            label: event.label,
                            description: event.description
                        }
                    );
                }
            });
        },

        submitDay(dt){
            this.$emit('submitday', dt);
        },

        copyCurrentWeek(){
            this.copyWeekData = [];
            this.events.forEach((event) => {
                if (this.inCurrentWeek(event)) {
                    this.copyWeekData.push({
                        day: event.mmStart.day(),
                        startHours: event.mmStart.hour(),
                        startMinutes: event.mmStart.minute(),
                        endHours: event.mmEnd.hour(),
                        endMinutes: event.mmEnd.minute(),
                        label: event.label,
                        description: event.description
                    });
                }
            })
        },

        pasteDay(day){
            if (this.copyDayData) {
                this.$emit('createpack', store.pasteDay(day))
            }
        },

        pasteWeek(){
            if (this.copyWeekData) {
                this.$emit('createpack', store.pasteWeek())
            }
        },

        previousWeek(){
            this.currentDay = moment(this.currentDay).add(-1, 'week');
        },

        nextWeek(){
            this.currentDay = moment(this.currentDay).add(1, 'week');
        },

        isToday(day){
            return day.format('YYYY-MM-DD') == store.today.format('YYYY-MM-DD');
        },

        newEvent(evt){
            evt.id = this.generatedId++;
            this.events.push(evt)
        },

        inCurrentWeek(event){
            return event.inWeek(this.currentDay.year(), this.currentDay.week());
        }
    },

    // Lorsque le composant est créé
    mounted(){
        var wrapper = this.$el.querySelector('.content-wrapper');
        wrapper.scrollTop = 280;
    }
};

var MonthView = {
    data(){
        return store
    },
    template: `<div class="calendar calendar-month">
        <h2>Month view</h2>
    </div>`
};

var ListItemView = {
    template: `<article class="list-item" :style="css" :class="{
        'event-editable': event.editable, 
        'status-info': event.isInfo, 
        'status-draft': event.isDraft, 
        'status-send' : event.isSend, 
        'status-valid': event.isValid, 
        'status-reject': event.isReject, 
        'valid-sci': event.isValidSci, 
        'valid-adm': event.isValidAdm, 
        'reject-sci':event.isRejectSci, 
        'reject-adm': event.isRejectAdm
        }">
        <time class="start">{{ beginAt }}</time> -
        <time class="end">{{ endAt }}</time>
        <strong>{{ event.label }}</strong>
        <div class="details">
            <h4>
                <i class="picto" :style="{background: colorLabel}"></i>
                {{ event.label }} {{ event.status }}</h4>
            <p class="time">
                de <time class="start">{{ beginAt }}</time> à <time class="end">{{ endAt }}</time>, <em>{{ event.duration }}</em> heure(s) ~ état : <em>{{ event.status }}</em>
            </p>
            <p v-if="withOwner">Déclarant <strong>{{ event.owner }}</strong></p>
            <p v-if="event.status == 'send'" class="alert alert-warning">Cet événement est en attente de validation</p>
            <p class="description">
                {{ event.description }}
            </p>
            <nav>
                <button class="btn btn-default btn-xs" @click="$emit('selectevent', event)">
                    <i class="icon-calendar"></i>
                Voir la semaine</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('editevent', event)" v-if="event.editable">
                    <i class="icon-pencil-1"></i>
                    Modifier</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('submitevent', event)" v-if="event.sendable">
                    <i class="icon-right-big"></i>
                    Soumettre</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('deleteevent', event)" v-if="event.deletable">
                    <i class="icon-trash-empty"></i>
                    Supprimer</button>
                    
               <button class="btn btn-danger btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectscievent')" v-if="event.validableSci">
                    <i class="icon-attention-1"></i>
                    Refus scientifique</button>
    
                 <button class="btn btn-danger btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectadmevent')" v-if="event.validableAdm">
                    <i class="icon-attention-1"></i>
                    Refus administratif</button>
                    
                <button class="btn btn-success btn-xs"  @mousedown.stop.prevent="" @click.stop.prevent="$emit('validatescievent')" v-if="event.validableSci">
                    <i class="icon-beaker"></i>
                    Validation scientifique</button>
                
                 <button class="btn btn-success btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit('validateadmevent')" v-if="event.validableAdm">
                    <i class="icon-archive"></i>
                    Validation administrative</button>

                <!--<button class="btn btn-primary btn-xs"  @click="handlerValidate" v-if="event.validable">
                    <i class="icon-right-big"></i>
                    Valider</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('rejectevent', event)" v-if="event.validable">
                    <i class="icon-right-big"></i>
                    Rejeter</button>-->
            </nav>
        </div>
    </article>`,
    props: ['event', 'withOwner'],
    methods: {
        handlerValidate(){
            this.$emit('validateevent')
        }
    },
    computed: {
        beginAt(){
            return this.event.mmStart.format('HH:mm');
        },
        endAt(){
            return this.event.mmEnd.format('HH:mm');
        },
        cssClass(){

            return 'status-' + this.event.status;
        },
        colorLabel(){
            return colorLabel(this.event.label);
        },
        css(){
            var percentUnit = 100 / (18 * 60)
                , start = (this.event.mmStart.hour() - 6) * 60 + this.event.mmStart.minutes()
                , end = (this.event.mmEnd.hour() - 6) * 60 + this.event.mmEnd.minutes();

            return {
                left: (percentUnit * start) + '%',
                width: (percentUnit * (end - start)) + '%',
                background: this.colorLabel
            }
        }
    }
};

var ListView = {
    data(){
        return store
    },

    computed: {
        firstDate(){
            return store.firstEvent;
        },
        lastDate(){
            return store.lastEvent;
        },
    },

    props: ['withOwner'],

    components: {
        listitem: ListItemView
    },

    template: `<div class="calendar calendar-list">
        <section v-for="eventsYear, year in listEvents" class="year-pack">
            <h2 class="flex-position">
                <strong>{{year}} <a href="#" @click.prevent="submitYear(eventsYear)" class="small-link">
                            <i class="icon-right-big"></i>                
                        </a></strong> 
                <span class="onright total">{{ eventsYear.total }} heure(s)</span>
            </h2>
            <section v-for="eventsMonth, month in eventsYear.months" class="month-pack">
                <h3 class="flex-position">
                    <strong>{{month}} ~ 
                    <a href="#" @click.prevent="submitMonth(eventsMonth)" class="small-link">
                            <i class="icon-right-big"></i>                
                        </a>
                    </strong> 
                    <span class="onright total">{{eventsMonth.total}} heure(s)</span>
                </h3>
                <section v-for="eventsWeek, week in eventsMonth.weeks" class="week-pack">
                    <h4 class="flex-position">
                        <strong>Semaine {{week}} ~ 
                        <a href="#" @click.prevent="submitWeek(eventsWeek)" class="small-link">
                            <i class="icon-right-big"></i>                
                        </a>
                        </strong> 
                        
                        <span class="onright total">{{eventsWeek.total}} heure(s)</span>
                    </h4>
                     <section v-for="eventsDay, day in eventsWeek.days" class="day-pack events">
                        <h5>{{day}} 
                        <a href="#" @click.prevent="submitDay(eventsDay)" class="small-link">
                            <i class="icon-right-big"></i>                
                        </a>
                        </h5>
                         <section class="events-list">
                            <listitem
                                :with-owner="withOwner"
                                @selectevent="selectEvent"
                                @editevent="$emit('editevent', event)"
                                @deleteevent="$emit('deleteevent', event)"
                                @submitevent="$emit('submitevent', event)"
                                @rejectscievent="$emit('rejectevent', event, 'sci')"
                                @rejectadmevent="$emit('rejectevent', event, 'adm')"
                                @validatescievent="$emit('validateevent', event, 'sci')"
                                @validateadmevent="$emit('validateevent', event, 'adm')"
                           
                                v-bind:event="event" v-for="event in eventsDay.events"></listitem>
                        </section>
                        <div class="total">
                            {{eventsDay.total}} heure(s)
                        </div>
                    </section>
                </section>
            </section>
        </section>
        <div v-if="!listEvents" class="alert alert-danger">
            Aucun créneaux détéctés
        </div>
    </div>`,

    methods: {
        selectEvent(event){
            store.currentDay = moment(event.start);
            store.state = "week";
        },

        getMonthPack(pack){
            var events = [];
            for (var k in pack.weeks) {
                if (pack.weeks.hasOwnProperty(k)) {
                    events = events.concat(this.getWeekPack(pack.weeks[k]));
                }
            }
            return events;
        },

        getWeekPack(pack){
            var events = [];
            for (var k in pack.days) {
                if (pack.days.hasOwnProperty(k)) {
                    events = events.concat(this.getDayPack(pack.days[k]));
                }
            }
            return events;
        },

        getDayPack(pack){
            return pack.events;
        },

        submitYear(yearPack){
            var events = [];
            for (var monthKey in yearPack.months) {
                if (yearPack.months.hasOwnProperty(monthKey)) {
                    events = events.concat(this.getMonthPack(yearPack.months[monthKey]));
                }
            }
            this.$emit('submitevent', events);
        },

        submitMonth(monthPack){
            this.$emit('submitevent', this.getMonthPack(monthPack));
        },

        submitWeek(weekPack){
            this.$emit('submitevent', this.getWeekPack(weekPack));
        },

        submitDay(dayPack){
            this.$emit('submitevent', this.getDayPack(dayPack));
        },

    },

    computed: {
        listEvents(){
            //EventDT.sortByStart(this.events);
            if (!store.events) {
                return null
            }

            var structure = {};
            for (let i = 0; i < this.events.length; i++) {
                let event = this.events[i];
                if (!(store.filterOwner == '' || store.filterOwner == event.owner_id)) continue;

                let currentYear, currentMonth, currentWeek, currentDay;

                let duration = event.duration;

                let labelYear = event.mmStart.format('YYYY');
                let labelMonth = event.mmStart.format('MMMM');
                let labelWeek = event.mmStart.format('W');
                let labelDay = event.mmStart.format('ddd D');

                if (!structure[labelYear]) {
                    structure[labelYear] = {
                        total: 0.0,
                        months: {}
                    };
                }
                currentYear = structure[labelYear];
                currentYear.total += duration;

                if (!currentYear.months[labelMonth]) {
                    currentYear.months[labelMonth] = {
                        total: 0.0,
                        weeks: {}
                    };
                }
                currentMonth = currentYear.months[labelMonth];
                currentMonth.total += duration;

                if (!currentMonth.weeks[labelWeek]) {
                    currentMonth.weeks[labelWeek] = {
                        total: 0.0,
                        days: {}
                    };
                }
                currentWeek = currentMonth.weeks[labelWeek];
                currentWeek.total += duration;

                if (!currentWeek.days[labelDay]) {
                    currentWeek.days[labelDay] = {
                        total: 0.0,
                        events: []
                    };
                }
                currentDay = currentWeek.days[labelDay];
                currentDay.total += duration;

                currentDay.events.push(event);
            }
            return structure;
        }
    }
};

var EventItemImport = {
    template: `<article class="list-item" :class="{ imported: event.imported }" :style="css" @click="event.imported = !event.imported">
                  <time class="start">{{ beginAt }}</time> -
                  <time class="end">{{ endAt }}</time>
                  <span>
                  <em>{{ event.label }}</em>
                  <strong v-show="event.useLabel"> => {{ event.useLabel }}</strong>
                  </span>
               </article>`,
    props: ['event'],
    computed: {
        beginAt(){
            return this.event.mmStart.format('HH:mm');
        },
        endAt(){
            return this.event.mmEnd.format('HH:mm');
        },
        colorLabel(){
            return colorLabel(this.event.label);
        },
        css(){
            var percentUnit = 100 / (18 * 60)
                , start = (this.event.mmStart.hour() - 6) * 60 + this.event.mmStart.minutes()
                , end = (this.event.mmEnd.hour() - 6) * 60 + this.event.mmEnd.minutes();

            return {
                position: "absolute",
                left: (percentUnit * start) + '%',
                width: (percentUnit * (end - start)) + '%',
                background: this.colorLabel
            }
        }
    }
};

var ImportICSView = {
    template: `<div class="importer">
                <div class="importer-ui">
                    <h1><i class="icon-calendar"></i>Importer un ICS</h1>
                    <nav class="steps">
                        <span :class="{active: etape == 1}">Fichier ICS</span>
                        <span :class="{active: etape == 2}">Créneaux à importer</span>
                        <span :class="{active: etape == 3}">Finalisation</span>

                    </nav>

                    <section class="etape1 row" v-if="etape == 1">
                        <div class="col-md-1">Du</div>
                        <div class="col-md-5">
                            <datepicker v-model="periodStart"></datepicker>
                        </div>

                        <div class="col-md-1">au</div>
                        <div class="col-md-5">
                            <datepicker v-model="periodEnd"></datepicker>
                        </div>

                        <!-- Période :
                        <datepicker v-model="periodStart"></datepicker> au <datepicker v-model="periodEnd"></datepicker>
                        -->
                        <p>Choisissez un fichier ICS : </p>
                        <input type="file" @change="loadIcsFile">
                    </section>

                    <section class="etape2" v-if="etape == 2">
                        <h2><i class="icon-download-outline"></i>Aperçu des données chargées</h2>
                        <p>Voici les données chargées depuis le fichier ICS fournis : </p>
                        <div class="calendar calendar-list">
                            <article v-for="pack in packs">
                                <section class="events">
                                    <h3>{{ pack.label }}</h3>
                                    <section class="events-list">
                                        <eventitemimport :event="event" v-for="event in pack.events"></eventitemimport>
                                    </section>
                                </section>
                            </article>
                        </div>
                        <div>
                            <h2><i class="icon-loop-outline"></i>Correspondance des créneaux</h2>
                            <section class="correspondances"">
                                <article v-for="label in labels">
                                    <strong><span :style="{'background': background(label)}" class="square">&nbsp</span>{{ label }}</strong>
                                    <select name="" id="" @change="updateLabel(label, $event.target.value)">
                                        <option value="">Conserver</option>
                                        <option value="ignorer">Ignorer ces créneaux</option>
                                        <option :value="creneau" v-for="creneau in creneaux">Placer dans {{ creneau }}</option>
                                    </select>
                                </article>
                            </section>
                        </div>
                    </section>

                    <div class="buttons">
                        <button class="btn btn-default" @click="$emit('cancel')">Annuler</button>
                        <button class="btn btn-primary" @click="applyImport" v-if="etape==2">
                            Valider l'import de ces créneaux
                        </button>
                    </div>
                </div>
            </div>`,
    props: {
        'creneaux': {
            default: ['test A', 'test B', 'test C']
        }
    },


    data(){
        return {
            periodStart: null,
            periodEnd: null,
            importedEvents: [],
            associations: {},
            labels: [],
            etape: 1
        }
    },

    components: {
        'datepicker': Datepicker,
        'eventitemimport': EventItemImport
    },

    computed: {
        packs(){
            var packs = [];
            this.importedEvents.forEach(item => {
                let currentPack = null;
                let currentLabel = item.mmStart.format('YYYY MMMM DD');
                for (let i = 0; i < packs.length && currentPack == null; i++) {
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
        background(label){
            return colorLabel(label);
        },
        updateLabel(from, to){
            if (to == 'ignorer') {
                this.importedEvents.forEach(item => {
                    if (item.label == from)
                        item.imported = false;
                })
            } else if (to == 'conserver') {
                this.importedEvents.forEach(item => {
                    if (item.label == from)
                        item.useLabel = '';
                    item.imported = true;
                });
            } else {
                this.importedEvents.forEach(item => {
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
        loadIcsFile(e){
            var fr = new FileReader();
            fr.onloadend = (result) => {
                this.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },

        /** Parse le contenu ICS **/
        parseFileContent(content){

            var analyser = new ICalAnalyser();
            var events = analyser.parse(ICAL.parse(content));
            this.importedEvents = [];
            this.labels = [];

            events.forEach(item => {
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);
                item.imported = true;
                item.useLabel = "";
                this.importedEvents.push(item);
                if (this.labels.indexOf(item.label) < 0)
                    this.labels.push(item.label);
            });

            this.etape = 2;
            /****/
        },
        applyImport(){
            var imported = [];
            this.importedEvents.forEach(event => {
                if (event.imported == true) {
                    imported.push(event)
                }
            });
            this.$emit('import', imported);
        }
    }
};

var SelectEditable = {
    template: `<div>
        <select v-model="selectedValue" @change="onSelectChange" class="form-control">
            <option v-for="choose in chooses" :value="choose">{{ choose }}</option>
            <option value="FREE">Autre&hellip;</option>
        </select>
        <input v-show="selectedValue == 'FREE'" v-model="valueIn" @input="onInput" class="form-control" />
    </div>`,

    props: {
        'value': {
            default: ''
        },
        'chooses': {
            default(){
                return ["A", "B", "C"]
            }
        }
    },

    data(){
        return {
            valueIn: this.value,
            editMode: false
        }
    },

    computed: {
        selectedValue(){
            if (this.chooses.indexOf(this.valueIn) >= 0) {
                return this.valueIn;
            } else {
                return 'FREE';
            }
        }
    },

    watch: {
        value(newV, oldV){
            this.valueIn = newV;
        }
    },

    methods: {
        onInput(){
            console.log('change', this.valueIn);
            this.$emit('input', this.valueIn, this.model);
        },
        onSelectChange(e){
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
    data(){
        return store
    },
    computed: {
        colspan(){
            return this.workPackageIndex.length;
        },
        structuredDatas(){
            return store.timesheetDatas();
        }
    },

    methods: {
        handlerDowloadTimesheet(datas){
            console.log(datas);
            let headers = [];
            require(["papa-parse"], function(Papa){
               console.log(Papa);
            });
        }
    },

    template: `<div class="timesheet"><h1>Feuille de temps</h1>
        <section v-for="activityDatas in structuredDatas"> 
            <h1>Activité sur <strong>{{ activityDatas.label }}</strong></h1>
            <section v-for="personDatas in activityDatas.persons">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ personDatas.label }}</th>
                            <th v-for="w in activityDatas.wps">{{ w }}</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="monthDatas, month in personDatas.months">
                            <th>{{ month }}</th>
                            <td v-for="tps in monthDatas.wps">{{tps}}</td>
                            <th>{{ monthDatas.total }}</th>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td :colspan="activityDatas.wps.length + 1">&nbsp;</td>
                            <th>{{ personDatas.total }}</th>
                        </tr>
                    </tfoot>
                </table>
                <button @click="handlerDowloadTimesheet(personDatas)" class="btn btn-primary">
                    <i class="icon-download-outline"></i>
                    Télécharger le CSV
                 </button>
            </section>
        </section>
</div>`
};

var Calendar = {

    template: `
        <div class="calendar">

            <importview :creneaux="labels" @cancel="importInProgress = false" @import="importEvents" v-if="importInProgress"></importview>
            
            <transition name="fade">
                <div class="vue-loader" v-if="remoteError" @click="remoteError = ''">
                    <div>
                        <h1>Erreur oscar</h1>
                        <p>{{ remoteError }}</p>
                    </div>
                </div>
            </transition>
           
            
            <transition name="fade">
                <div class="vue-loader" v-if="rejectShow">
                    <div>
                    <nav><a href="#" @click.prevent="rejectShow = null"><i class="icon-cancel-outline"></i>Fermer</a></nav>
                    <section v-if="rejectShow.rejectedAdminAt" class="card">
                        <h2>
                            <i class="icon-archive">Rejet administratif
                        </h2>
                        Ce créneau a été refusé par <strong>{{ rejectShow.rejectedAdminBy }}</strong>  le <time>{{ rejectShow.rejectedAdminAt | moment}}</time> au motif : 
                        <pre>{{ rejectShow.rejectedAdminComment }}</pre>
                    </section>
                    <section v-if="rejectShow.rejectedSciAt" class="card">
                        <h2>
                            <i class="icon-archive">Rejet scientifique
                        </h2>
                        Ce créneau a été refusé par <strong>{{ rejectShow.rejectedSciBy }}</strong>  le <time>{{ rejectShow.rejectedSciAt | moment}}</time> au motif : 
                        <pre>{{ rejectShow.rejectedSciComment }}</pre>
                    </section>
                    </div>
                </div>
            </transition>
        
            <div class="vue-loader" v-if="loading">
                <span>Chargement</span>
            </div>
            
            <div class="editor" v-show="eventEditDataVisible">
                <form @submit.prevent="editSave">
                    <div class="form-group">
                        <label for="">Intitulé</label>
                        <selecteditable v-model="eventEditData.label" :chooses="labels"></selecteditable>
                    </div>
                    <div v-if="withOwner">
                        {{ eventEditData.owner_id }}
                        <select v-model="eventEditData.owner_id">
                            <option :value="o.id" v-for="o in owners">{{ o.displayname }}</option>
                        </select>
                        Déclarant LISTE
                    </div>
                    <div>
                        <label for="">Description</label>
                        <textarea class="form-control" v-model="eventEditData.description"></textarea>
                    </div>
                    <hr />
                    <button type="button" @click="handlerEditCancelEvent" class="btn btn-primary">Annuler</button>
                    <button type="cancel" @click="handlerSaveEvent" class="btn btn-default">Enregistrer</button>
                </form>
            </div>

            <div class="editor" v-show="displayRejectModal">
                <form @submit.prevent="handlerSendReject">
                    <h3>Refuser des créneaux</h3>
                    <div class="row">
                        <section class="col-md-6 editor-column-fixed">
                           <article v-for="creneau in rejectedEvents" class="event-inline-simple">
                            <i class="icon-archive"></i><strong>{{ creneau.label }}</strong><br>
                            <i class="icon-user"></i><strong>{{ creneau.owner }}</strong><br>
                            <i class="icon-calendar"></i><strong>{{ creneau.dayTime }}</strong>
                           </article>
                        </section>
                        <section class="col-md-6">
                            <div class="form-group">
                                <label for="">Préciser la raison du refus</label>
                                <textarea class="form-control" v-model="rejectComment" placeholder="Raison du refus"></textarea>
                            </div>
                        </section>
                    </div>
                    <hr />
                    <button type="submit" class="btn btn-primary" :class="{disabled: !rejectComment}">Envoyer</button>
                    <button type="cancel" class="btn btn-default" @click.prevent="displayRejectModal = false">Annuler</button>
                </form>
            </div>

            <nav class="calendar-menu">
                <nav class="views-switcher">
                    <a href="#" @click.prevent="state = 'week'" :class="{active: state == 'week'}"><i class="icon-calendar"></i>{{ trans.labelViewWeek }}</a>
                    <a href="#" @click.prevent="state = 'list'" :class="{active: state == 'list'}"><i class="icon-columns"></i>{{ trans.labelViewList }}</a>
                    <a href="#" @click.prevent="state = 'timesheet'" :class="{active: state == 'timesheet'}" v-if="this.wps"><i class="icon-file-excel"></i>Feuille de temps</a>
                    
                    <a href="#" @click.prevent="importInProgress = true" v-if="createNew"><i class="icon-columns"></i>Importer un ICS</a>
                    <span class="calendar-label">
                       {{ calendarLabel }}
                    </span>
                    
                    <span v-if="owners.length">
                    Déclarants : 
                    <select v-model="filterOwner" class="input-sm">
                      <option value="">Tous les déclarants</option>
                      <option v-for="owner in owners" :value="owner.id">{{ owner.displayname }}</option>
                    </select>
                    </span>
                    
                </nav>
                <section class="transmission errors">

                    <p class="error" v-for="error in errors">
                        <i class="icon-warning-empty"></i> {{ error }}
                        <a href="#" @click.prevent="errors.splice(errors.indexOf(error), 1)" class="fermer">[fermer]</a>
                    </p>
                </section>
                <section class="transmission infos" v-show="transmission">
                    <span>
                        <i class="icon-signal"></i>
                        {{ transmission }}
                    </span>
                </section>
            </nav>

            <weekview v-if="state == 'week'"
                :create-new="createNew"
                :with-owner="withOwner"
                @editevent="handlerEditEvent"
                @deleteevent="handlerDeleteEvent"
                @createpack="handlerCreatePack"
                @submitevent="handlerSubmitEvent"
                @validateevent="handlerValidateEvent"
                @rejectevent="handlerRejectEvent"
                @createevent="handlerCreateEvent"
                @savemoveevent="handlerSaveMove"
                @submitday="submitday"
                @submitall="submitall"
                @rejectshow="handlerRejectShow"
                @saveevent="restSave"></weekview>

            <listview v-if="state == 'list'"
                :with-owner="withOwner"
                @editevent="handlerEditEvent"
                @deleteevent="handlerDeleteEvent"
                @validateevent="handlerValidateEvent"
                @rejectevent="handlerRejectEvent"
                @submitevent="handlerSubmitEvent"></listview>
                
            <timesheetview v-if="state == 'timesheet'"
                :with-owner="withOwner"
                ></timesheet>
        </div>

    `,

    data(){
        return store
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

        // Texts
        trans: {
            default() {
                return {
                    labelViewWeek: "Semaine",
                    labelViewMonth: "Mois",
                    labelViewList: "Liste"
                }
            }
        }
    },

    filters: {
        moment(value){
            var d = moment(value.date);
            return d.format("dddd, D MMMM YYYY") + " (" + d.fromNow() + ")";
        }
    },

    components: {
        weekview: WeekView,
        monthview: MonthView,
        listview: ListView,
        timesheetview: TimesheetView,
        eventitemimport: EventItemImport,
        importview: ImportICSView,
        selecteditable: SelectEditable
    },

    methods: {
        /**
         * Envoi des données (de la semaine), @todo Faire la variante pour les mois.
         * @param status
         * @param period
         */
        submitall(status, period){
            var events = [];
            if (period == 'week') {
                this.events.forEach(event => {
                    if (store.inCurrentWeek(event) && event.sendable) {
                        events.push(event);
                    }
                });
            }
            if (events.length) {
                bootbox.confirm("Soumettre le(s) créneau(x) ?", (confirm) => {
                    if (confirm)
                        this.restStep(events, status);
                });
            }
        },

        handlerRejectShow(event){
            console.log('AFFICHAGE DU REJET', event);
            this.rejectShow = event;
        },

        /**
         * Envoi des créneaux de la journée.
         *
         * @param day
         */
        submitday(day){

            // Liste des événements éligibles
            var events = [];
            this.events.forEach(event => {
                if (event.mmStart.format('YYYYMMDD') == day.format('YYYYMMDD') && event.sendable) {
                    events.push(event);
                }
            });

            // Envoi
            if (events.length) {
                bootbox.confirm("Soumettre le(s) créneau(x) ?", (confirm) => {
                    if (confirm)
                        this.restStep(events, 'send');
                });
            }
        },

        importEvents(events){
            var datas = [];
            events.forEach(item => {
                var event = JSON.parse(JSON.stringify(item));
                if (event.useLabel) event.label = event.useLabel;
                event.mmStart = moment(event.start);
                event.mmEnd = moment(event.end);
                datas.push(event);
            })
            this.importInProgress = false;
            this.restSave(datas);
        },

        handlerCreatePack(events){
            this.restSave(events);
        },

        confirmImport(){
            console.log('Tous ajouter');
        },

        handleradd(pack, event){
            var packIndex = this.importedData.indexOf(pack);
            this.importedData[packIndex].splice(this.importedData[packIndex].indexOf(event));
        },

        editSave(){
            this.defaultLabel = this.eventEdit.label = this.eventEditData.label;
            this.defaultDescription = this.eventEdit.description = this.eventEditData.description;
            this.handlerEditCancelEvent();
        },

        handlerEditCancelEvent(){
            this.eventEditDataVisible = false;
            this.eventEdit = this.eventEditData = {};
        },

        /** Edition de l'événement de la liste */
        handlerEditEvent(event){
            this.eventEdit = event;
            this.eventEditDataVisible = true;
            this.eventEditData = JSON.parse(JSON.stringify(event));
        },

        ////////////////////////////////////////////////////////////////////////

        handlerSendReject(){
            console.log('Envoi du rejet', this.rejectComment);
            var events = [];
            this.rejectedEvents.forEach((event) => {
                var e = JSON.parse(JSON.stringify(event));
                if (this.rejectValidateType == 'rejectsci') {
                    e.rejectedSciComment = this.rejectComment;
                } else if (this.rejectValidateType == 'rejectadm') {
                    e.rejectedAdminComment = this.rejectComment;
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
        handlerRejectEvent(event, type = "unknow"){
            // événements reçus
            var eventsArray = !event.length ? [event] : event,
                events = [];

            eventsArray.forEach((event) => {
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


        handlerValidateEvent(events, type = "unknow"){
            // événements reçus
            var eventsArray = !events.length ? [events] : events,
                events = [];

            eventsArray.forEach((event) => {
                if (type == "sci" && event.validableSci || type == "adm" && event.validableAdm) {
                    events.push(event);
                }
            });

            if (events.length) {
                let message = events.length == 1 ? " du créneau " : " des " + events.length + " créneaux ";
                bootbox.confirm(type == 'sci' ?
                        '<i class="icon-beaker"></i> Validation scientifique ' + message
                        : '<i class="icon-archive"></i>   Validation administrative' + message
                    , (response) => {
                        if (response) {
                            this.restStep(events, 'validate' + type)
                        }
                    })
            } else {
                bootbox.alert("Aucun créneau ne peut être validé");
            }
        },

        showRejectModal(events, type){
            this.displayRejectModal = true;
            this.rejectValidateType = type;
            this.rejectedEvents = events;
        },

        ////////////////////////////////////////////////////////////////////////

        restSave(events){
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
                store.loading = true;
                this.$http.post(this.restUrl(), data).then(
                    response => {
                        store.sync(response.body.timesheets);
                        this.handlerEditCancelEvent();
                    },
                    error => {
                        this.errors.push("Impossible d'enregistrer les données : " + error)
                    }
                ).then(() => {
                    this.transmission = "";
                    store.loading = false;
                });
                ;
            }
        },

        restSend(events){
            this.restStep(events, 'send')
        },

        restValidate(events){
            this.restStep(events, 'validate')
        },

        restStep(events, action){
            if (!Array.isArray(events)) {
                events = [events];
            }
            if (this.restUrl) {
                this.transmission = "Enregistrement en cours...";
                var data = new FormData();
                data.append('do', action);
                for (var i = 0; i < events.length; i++) {
                    data.append('events[' + i + '][id]', events[i].id || null);
                    data.append('events[' + i + '][rejectedSciComment]', events[i].rejectedSciComment || null);
                    data.append('events[' + i + '][rejectedAdminComment]', events[i].rejectedAdminComment || null);
                }
                this.loading = true;
                this.$http.post(this.restUrl(), data).then(
                    response => {
                        store.sync(response.body.timesheets);
                        this.displayRejectModal = false;
                        this.handlerEditCancelEvent();
                    },
                    error => {
                        console.log(error);
                        this.errors.push("Impossible de modifier l'état du créneau : " + error);

                        this.remoteError = "Erreur : " + error.statusText;
                    }
                ).then(() => {
                    this.transmission = "";
                    this.loading = false;
                });
            }
        },

        /** Suppression de l'événement de la liste */
        handlerDeleteEvent(event){
            if (this.restUrl) {
                this.transmission = "Suppression...";
                this.$http.delete(this.restUrl() + "?timesheet=" + event.id).then(
                    response => {
                        this.events.splice(this.events.indexOf(event), 1);
                    },
                    error => {
                        console.log(error)
                    }
                ).then(() => {
                    this.transmission = "";
                });
            } else {
                this.events.splice(this.events.indexOf(event), 1);
            }
        },

        handlerSaveMove(event){
            var data = JSON.parse(JSON.stringify(event));
            data.mmStart = moment(data.start);
            data.mmEnd = moment(data.end);
            this.restSave([data]);
        },

        handlerSaveEvent(event){
            store.defaultLabel = this.eventEditData.label;
        },

        /** Soumission de l'événement de la liste */
        handlerSubmitEvent(event){
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
                bootbox.confirm("Soumettre le(s) " + eventsSend.length + " créneau(x) ?", (confirm) => {
                    if (confirm)
                        this.restSend(eventsSend);
                });
            } else {
                bootbox.alert("Aucun créneau à envoyer.");
            }
        },

        /** Soumission de l'événement de la liste */
        handlerCreateEvent(event){
            this.handlerEditEvent(event);
        },

        /** Charge le fichier ICS depuis l'interface **/
        loadIcsFile(e){
            this.transmission = "Analyse du fichier ICS...";
            var fr = new FileReader();
            fr.onloadend = (result) => {
                this.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },

        /** Parse le contenu ICS **/
        parseFileContent(content){
            var analyser = new ICalAnalyser();
            var events = analyser.parse(ICAL.parse(content));
            this.importedData = [];

            events.forEach(item => {
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);

                let currentPack = null;
                let currentLabel = item.mmStart.format('YYYY-MM-D');
                for (let i = 0; i < this.importedData.length && currentPack == null; i++) {
                    if (this.importedData[i].label == currentLabel) {
                        currentPack = this.importedData[i];
                    }
                }
                if (!currentPack) {
                    currentPack = {
                        label: currentLabel,
                        events: []
                    };
                    this.importedData.push(currentPack);
                }
                currentPack.events.push(item);
            })
            this.importInProgress = true;
        },

        /** Ajoute la liste d'événement **/
        hydrateEventWith(arrayOfObj){

            arrayOfObj.forEach((obj) => {
                store.addNewEvent(obj.id, obj.label,
                    obj.start, obj.end, obj.description,
                    {editable: true, deletable: true},
                    'draft');
            })
        },

        deleteEvent(event){
            this.events.splice(this.events.indexOf(event), 1);
        },

        createEvent(day, time){
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

        editEvent(event){
            this.eventEdit = event;
            this.eventEditData = JSON.parse(JSON.stringify(event));
        },

        editSave(){
            var event = JSON.parse(JSON.stringify(this.eventEditData));
            event.mmStart = moment(event.start);
            event.mmEnd = moment(event.end);
            this.restSave([event]);
        },

        editCancel(){
            this.eventEdit = this.eventEditData = null;
        },

        /////////////////////////////////////////////////////////////////// REST
        fetch(){
            this.transmission = "Chargement des créneaux...";
            store.loading = true;

            this.$http.get(this.restUrl()).then(
                ok => {
                    store.sync(ok.body.timesheets);
                    store.loading = false;
                },
                ko => {
                    this.errors.push("Impossible de charger les données : " + ko);
                    store.remoteError = "Impossible de charger des créneaux";
                }
            ).then(() => {
                this.transmission = "";
                store.loading = false;
            });
        },

        post(event){
            console.log("POST", event);
        }
    },

    mounted(){
        console.log('MOUNTED', this.customDatas);
        if (this.customDatas) {
            console.log("CustomDatas", this.customDatas());
            var customs = this.customDatas();
            this.wps = customs;
            for (var k in customs) {
                if (customs.hasOwnProperty(k)) {
                    let wp = customs[k];
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
