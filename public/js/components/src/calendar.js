import EventDT from "EventDT";
import moment from "moment-timezone"
import ICalAnalyser from "ICalAnalyser";
import VueResource from "vue-resource";
import Datepicker from "Datepicker";

moment.locale('fr');


var colorLabels = {};
var colorIndex = 0;
var colorpool = ['#fcdc80','#a6cef8','#9fd588','#fb90bb','#e5fbed','#99a0ce','#bca078','#f3cafd','#d9f4c1','#60e3bb','#f2c7f5','#f64bc0','#ffc1b2','#fc9175','#d7fc74','#e3d7f8','#9ffab3','#d6cbac','#4dd03c','#f8f3be'];

var colorLabel = (label) => {
    if( !colorLabels[label] ){
        colorLabels[label] = colorpool[++colorIndex];
        colorIndex = colorIndex%colorpool.length;
    }
    return colorLabels[label];
};



////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// MODEL

class CalendarDatas {
    constructor(){
        this.state = 'week';
        this.events = [];
        this.newID = 1;
        this.transmission = "";
        this.importInProgress = false;
        this.importedEvents = [];
        this.eventEditData = {};
        this.eventEditDataVisible = false;
        this.currentDay = moment()
        this.eventEdit = null;
        this.copyWeekData = null;
        this.copyDayData = null;
        this.generatedId = 0;
        this.defaultLabel = "";
        this.errors = [];
        this.defaultDescription = "";
        this.labels = [];
    }

    get listEvents(){
        EventDT.sortByStart(this.events);
        return this.events;
    }

    get today(){
        return moment();
    }

    get firstEvent(){

    }

    get lastEvent(){

    }

    get currentYear(){
        return this.currentDay.format('YYYY')
    }

    get currentMonth(){
        return this.currentDay.format('MMMM')
    }

    get currentWeekKey(){
        return this.currentDay.format('YYYY-W')
    }

    get currentWeekDays(){
        let days = [], day = moment(this.currentDay.startOf('week'));

        for( let i = 0; i<7; i++ ){
            days.push(moment(day.format()));
            day.add(1, 'day');
        }
        return days;
    }

    copyDay(dt){
        this.copyDayData = [];
        var dDay = dt.format('MMMM D YYYY');
        this.events.forEach((event) => {
            var dayRef = moment(event.start).format('MMMM D YYYY');
            if( dayRef == dDay ){
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
    copyCurrentWeek(){
        this.copyWeekData = [];
        this.events.forEach((event) => {
            if( this.inCurrentWeek(event) ){
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

    pasteDay(day){
        if( this.copyDayData ){
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

    pasteWeek(){
        if( this.copyWeekData ){
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

    previousWeek(){
        this.currentDay = moment(this.currentDay).add(-1, 'week');
    }

    nextWeek(){
        this.currentDay = moment(this.currentDay).add(1, 'week');
    }

    newEvent(evt){
        evt.id = this.generatedId++;
        this.events.push(evt)
    }

    inCurrentWeek(event){
        return event.inWeek(this.currentDay.year(), this.currentDay.week());
    }

    sync(datas){
        for( var i=0; i< datas.length; i++ ){
            let local = this.getEventById(datas[i].id);
            if( local ){
                local.sync(datas[i]);
            } else {
                this.addNewEvent(
                    datas[i].id,
                    datas[i].label,
                    datas[i].start,
                    datas[i].end,
                    datas[i].description,
                    datas[i].credentials,
                    datas[i].status,
                    datas[i].owner
                );
            }
        }
    }

    getEventById( id ){
        for( let i=0; i< this.events.length; i++ ){
            if( this.events[i].id == id ){
                return this.events[i];
            }
        }
        return null;
    }

    addNewEvent(id, label, start, end, description, credentials = undefined, status="draft", owner=""){
        this.events.push(
            new EventDT(
                id,
                label,
                start, end,
                description,
                credentials,
                status,
                owner
            )
        );
    }
}
var store = new CalendarDatas();


var TimeEvent = {

    template: `<div class="event" :style="css"
            @mouseleave="handlerMouseOut"
            @mousedown="handlerMouseDown"
            :title="event.label"
            :class="{'event-moving': moving, 'event-selected': selected, 'event-locked': isLocked, 'status-info': isInfo, 'status-draft': isDraft, 'status-send' : isSend, 'status-valid': isValid, 'status-reject': isReject}">
        <div class="label" data-uid="UID">
          {{ event.label }}
        </div>
        <small>Durée : <strong>{{ labelDuration }}</strong> heure(s)</small>
        <div class="description">
            <p v-if="withOwner">Déclarant <strong>{{ event.owner }}</strong></p>
          {{ event.description }}
        </div>

        <nav class="admin">
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('editevent')" v-if="event.editable">
                <i class="icon-pencil-1"></i>
                Modifier</a>
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('deleteevent')" v-if="event.deletable">
                <i class="icon-trash-empty"></i>
                Supprimer</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('submitevent')" v-if="event.sendable">
                <i class="icon-right-big"></i>
                Soumettre</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('validateevent')" v-if="event.validable">
                <i class="icon-right-big"></i>
                Valider</a>
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectevent')" v-if="event.validable">
                <i class="icon-right-big"></i>
                Rejeter</a>
        </nav>

        <div class="bottom-handler" v-if="event.editable"
            @mouseleave="handlerEndMovingEnd"
            @mousedown.prevent.stop="handlerStartMovingEnd">
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
            change: false,
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

        css(){
            var marge = 0;
            var sizeless = 0;
            if( this.event.intersect > 0 ){
                sizeless = 3;
                marge = sizeless / this.event.intersect * this.event.intersectIndex;
            }
            return {
                height: (this.pixelEnd - this.pixelStart) + 'px',
                background: this.colorLabel,
                position: "absolute",
                top: this.pixelStart + 'px',
                width: ((100 / 7)-1) - sizeless + "%",
                left: ((this.weekDay-1) * 100 / 7)+marge + "%"
            }
        },

        ///////////////////////////////////////////////////////////////// STATUS
        isDraft(){ return this.event.status == "draft";},
        isSend(){ return this.event.status == "send";},
        isValid(){ return this.event.status == "valid";},
        isReject(){ return this.event.status == "reject";},
        isInfo(){ return this.event.status == "info";},

        colorLabel(){
            return colorLabel(this.event.label);
        },

        isLocked(){
            return !this.event.editable;
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

        weekDay(){
            return this.dateStart.day()
        }
    },

    watch: {
        'event.start': function(){
            this.labelStart = this.dateStart.format('H:mm');
        },
        'event.end': function(){
            this.labelEnd = this.dateEnd.format('H:mm');
        }
    },

    methods: {
        move(event){
            if( this.event.editable && event.movementY != 0) {
                this.change = true;

                var currentTop = parseInt(this.$el.style.top);
                var currentHeight = parseInt(this.$el.style.height);

                if( this.movingBoth ) {
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

        handlerEndMovingEnd(){
            if( this.movingBoth ){
                this.movingBoth = false;
            }
        },

        handlerStartMovingEnd(e){
            this.movingBoth = false;
            this.startMoving(e);
        },

        startMoving(e){
            if( this.event.editable ) {
                this.startX = e.clientX;
                this.selected = true;
                this.moving = true;
                this.$el.addEventListener('mousemove', this.move);
                this.$el.addEventListener('mouseup', this.handlerMouseUp);
            }
        },

        handlerMouseDown(e){
            this.movingBoth = true;
            this.startMoving(e);

        },

        handlerMouseUp(e){
            if( this.event.editable ) {
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
                    .format()
                if( this.change ) {
                    this.change = false;
                    this.$emit('savemoveevent', this.event);
                }
            }
        },

        handlerMouseOut(e){
            this.handlerMouseUp()
        },

        roundMinutes(minutes){
            return Math.floor(60/40*minutes/15)*15
        },

        formatZero(int){
            return int < 10 ? '0'+int : int;
        },

        ////////////////////////////////////////////////////////////////////////
        topToStart(){
            var round = 40/12;

            var minutesStart = parseInt(this.$el.style.top);
            var minutesEnd = minutesStart + parseInt(this.$el.style.height);

            var startHours = Math.floor(minutesStart/40);
            var startMinutes = this.roundMinutes(minutesStart - startHours*40);

            var endHours = Math.floor(minutesEnd/40);
            var endMinutes = this.roundMinutes(minutesEnd- endHours*40);

            return {
                startHours: startHours,
                startMinutes: startMinutes,
                endHours: endHours,
                endMinutes: endMinutes,
                duration: formatDuration(((endHours*60 + endMinutes) - (startHours*60 + startMinutes))*60),
                startLabel: this.formatZero(startHours)+':'+this.formatZero(startMinutes),
                endLabel: this.formatZero(endHours)+':'+this.formatZero(endMinutes)
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
    var m = (milliseconde - (h*60*60))/60;
    return h + (m ? 'h' + m : '');
}

var WeekView = {
    data(){
        return store
    },

    props: {
        'withOwner': { default: false },
        'createNew': { default: false },
        'pas': { default: 15 }
    },

    components: {
        'timeevent': TimeEvent
    },

    template: `<div class="calendar calendar-week">
    <div class="meta">
        <a href="#" @click="previousWeek">
            <i class="icon-left-big"></i>
        </a>
        <h3>
            Semaine {{ currentWeekNum}}, {{ currentMonth }} {{ currentYear }}
            <nav class="copy-paste" v-if="createNew">
                <span href="#" @click="copyCurrentWeek"><i class="icon-docs"></i></span>
                <span href="#" @click="pasteWeek"><i class="icon-paste"></i></span>
                <span href="#" @click="$emit('submitall', 'send', 'week')"><i class="icon-right-big"></i></span>
            </nav>
        </h3>
       <a href="#" @click="nextWeek">
            <i class="icon-right-big"></i>
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
                    <nav class="copy-paste" v-if="createNew">
                        <span href="#" @click="copyDay(day)"><i class="icon-docs"></i></span>
                        <span href="#" @click="pasteDay(day)"><i class="icon-paste"></i></span>
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
          <div class="events">

              <div class="cell cell-day day" v-for="day in 7">
                <div class="hour houroff" v-for="time in 6">&nbsp;</div>
                <div class="hour" v-for="time in 16"
                    @mouseup="handlerMouseUp"
                    @mousedown="handlerMouseDown"
                    @dblclick="createEvent(day, time+5)">&nbsp;</div>
                <div class="hour houroff" v-for="time in 2">&nbsp;</div>
              </div>
              <div class="content-events">
                <timeevent v-for="event in weekEvents"
                    :with-owner="withOwner"
                    :weekDayRef="currentDay"
                    v-if="inCurrentWeek(event)"
                    @deleteevent="$emit('deleteevent', event)"
                    @editevent="$emit('editevent', event)"
                    @submitevent="$emit('submitevent', event)"
                    @validateevent="$emit('validateevent', event)"
                    @savemoveevent="handlerSaveMove"
                    :event="event"
                    :key="event.id"></timeevent>
              </div>
          </div>
        </div>
    </div>

    <footer class="line">
      FOOTER
    </footer>
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

            for( let i = 0; i<7; i++ ){
                days.push(moment(day.format()));
                day.add(1, 'day');
            }
            return days;
        },

        weekEvents(){
            var weekEvents = []
            this.events.forEach( event => {
                if( store.inCurrentWeek(event) ){
                    event.intersect = 0;
                    event.intersectIndex = 0;
                    weekEvents.push(event);
                }
            });

            // Détection des collapses
            for( var i=0; i<weekEvents.length; i++ ){
                var u1 = weekEvents[i];

                for( var j=i+1; j<weekEvents.length; j++ ){
                    var u2 = weekEvents[j];
                    if( u2 == u1 ){
                        continue;
                    }
                    if( u2.overlap(u1) ){
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
        handlerSaveMove(event){
            this.$emit('savemoveevent', event);
        },

        handlerMouseUp(){
            console.log('mouse up');
        },

        handlerMouseDown(){
            console.log('mouse down');
        },

        createEvent(day,time){
            var start = moment(this.currentDay).day(day).hour(time);
            var end = moment(start).add(2, 'hours');
            var newEvent = new EventDT(null, this.defaultLabel, start.format(), end.format(), this.defaultDescription, { editable: true, deletable: true});
            this.$emit('createevent', newEvent);
        },

        copyDay(dt){
            this.copyDayData = [];
            var dDay = dt.format('MMMM D YYYY');
            this.events.forEach((event) => {
                var dayRef = moment(event.start).format('MMMM D YYYY');
                if( dayRef == dDay ){
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

        copyCurrentWeek(){
            this.copyWeekData = [];
            this.events.forEach((event) => {
                if( this.inCurrentWeek(event) ){
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
            if( this.copyDayData ){
                this.$emit('createpack', store.pasteDay(day))
            }
        },

        pasteWeek(){
            if( this.copyWeekData ){
                this.$emit('createpack', store.pasteWeek())
            }
        },

        previousWeek(){
            this.currentDay = moment(this.currentDay).add(-1, 'week');
        },

        nextWeek(){
            this.currentDay = moment(this.currentDay).add(1, 'week');
        },

        isToday( day ){
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
    template: `<article class="list-item" :style="css" :class="cssClass">
        <time class="start">{{ beginAt }}</time> -
        <time class="end">{{ endAt }}</time>
        <strong>{{ event.label }}</strong>
        <div class="details">
            <h4>
                <i class="picto" :style="{background: colorLabel}"></i>
                [ {{ event.id }}/{{ event.uid }}]
                {{ event.label }}</h4>
            <p class="time">
                de <time class="start">{{ beginAt }}</time> à <time class="end">{{ endAt }}</time>, <em>{{ event.duration }}</em> heure(s) ~ état : <em>{{ event.status }}</em>
            </p>
            <p v-if="withOwner">Déclarant <strong>{{ event.owner }}</strong></p>
            <p v-if="event.status == 'send'" class="alert alert-warning">Cet événement est en attente de validation</p>
            <p class="description">
                {{ event.description }}
            </p>
            <nav>
                <button class="btn btn-primary btn-xs" @click="$emit('selectevent', event)">
                    <i class="icon-calendar"></i>
                Voir la semaine</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('editevent', event)" v-if="event.editable">
                    <i class="icon-pencil-1"></i>
                    Modifier</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('submitevent', event)" v-if="event.sendable">
                    <i class="icon-pencil-1"></i>
                    Soumettre</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('deleteevent', event)" v-if="event.deletable">
                    <i class="icon-trash-empty"></i>
                    Supprimer</button>

                <button class="btn btn-primary btn-xs"  @click="handlerValidate" v-if="event.validable">
                    <i class="icon-right-big"></i>
                    Valider</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('rejectevent', event)" v-if="event.validable">
                    <i class="icon-right-big"></i>
                    Rejeter</button>
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
            var percentUnit = 100 / (18*60)
                , start = (this.event.mmStart.hour()-6)*60 + this.event.mmStart.minutes()
                , end = (this.event.mmEnd.hour()-6)*60 + this.event.mmEnd.minutes();

            return {
                left: (percentUnit * start) +'%',
                width: (percentUnit * (end - start)) +'%',
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
        <h2>Liste des créneaux</h2>
        <article v-for="pack in listEvents">
            <section class="events">
                <h3>{{ pack.label }}</h3>
                <section class="events-list">
                <listitem
                    :with-owner="withOwner"
                    @selectevent="selectEvent"
                    @editevent="$emit('editevent', event)"
                    @deleteevent="$emit('deleteevent', event)"
                    @submitevent="$emit('submitevent', event)"
                    @validateevent="$emit('validateevent', event)"
                    @rejectevent="$emit('rejectevent', event)"
                    v-bind:event="event" v-for="event in pack.events"></listitem>
                </section>
                <div class="total">
                    {{ pack.totalHours }} heure(s)
                </div>
            </section>

        </article>
    </div>`,

    methods: {
        selectEvent(event){
            store.currentDay = moment(event.start);
            store.state = "week";
        }
    },

    computed: {
        listEvents(){
            EventDT.sortByStart(this.events);
            var pack = [];
            var packerFormat = 'ddd D MMMM YYYY';
            var packer = null;

            var currentPack = null;

            if( !store.events ){
                return null
            }

            for( let i=0; i<this.events.length; i++ ){
                let event = this.events[i];
                let label = event.mmStart.format(packerFormat);

                if( packer == null || packer.label != label ){
                    packer = {
                        label: label,
                        events: [],
                        totalHours: 0
                    }
                    pack.push(packer);
                }
                packer.totalHours += event.duration;
                packer.events.push(event);
            }

            return pack;
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
            var percentUnit = 100 / (18*60)
                , start = (this.event.mmStart.hour()-6)*60 + this.event.mmStart.minutes()
                , end = (this.event.mmEnd.hour()-6)*60 + this.event.mmEnd.minutes();

            return {
                position: "absolute",
                left: (percentUnit * start) +'%',
                width: (percentUnit * (end - start)) +'%',
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
        'eventitemimport' : EventItemImport
    },

    computed: {
        packs(){
            var packs = [];
            this.importedEvents.forEach( item => {
                let currentPack = null;
                let currentLabel = item.mmStart.format('YYYY MMMM DD');
                for( let i=0; i<packs.length && currentPack == null ; i++ ){
                    if( packs[i].label == currentLabel ){
                        currentPack = packs[i];
                    }
                }
                if( !currentPack ){
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
        updateLabel( from, to ){
            if( to == 'ignorer' ) {
                this.importedEvents.forEach(item => {
                    if (item.label == from)
                        item.imported = false;
                })
            } else if( to == 'conserver' ){
                    this.importedEvents.forEach( item => {
                        if( item.label == from )
                            item.useLabel = '';
                            item.imported = true;
                    });
            } else {
                this.importedEvents.forEach( item => {
                    if( item.label == from ) {
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
            fr.onloadend = (result)=> {
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

            events.forEach( item => {
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);
                item.imported = true;
                item.useLabel = "";
                this.importedEvents.push(item);
                if( this.labels.indexOf(item.label) < 0 )
                    this.labels.push(item.label);
            });

            this.etape = 2;
            /****/
        },
        applyImport(){
            var imported = [];
            this.importedEvents.forEach( event => {
                if( event.imported == true ){
                    imported.push(event)
                }
            });
            this.$emit('import', imported);
        }
    }
};

var Calendar = {

    template: `
        <div class="calendar">

            <importview :creneaux="labels" @cancel="importInProgress = false" @import="importEvents" v-if="importInProgress"></importview>

            <div class="editor" v-show="eventEditDataVisible">
                <form @submit.prevent="editSave">
                    <div class="form-group">
                        <label for="">Intitulé</label>
                        <input type="text" v-model="eventEditData.label" />
                        <select v-model="eventEditData.label" class="select2">
                            <option v-for="label in labels" :value="label">{{label}}</option>
                        </select>
                    </div>
                    <div>
                        <label for="">Description</label>
                        <textarea class="form-control" v-model="eventEditData.description"></textarea>
                    </div>

                    <button type="button" @click="handlerEditCancelEvent">Annuler</button>
                    <button type="cancel" @click="handlerSaveEvent">Enregistrer</button>
                </form>
            </div>

            <nav class="calendar-menu">
                <nav class="views-switcher">
                    <a href="#" @click.prevent="state = 'week'" :class="{active: state == 'week'}"><i class="icon-calendar"></i>{{ trans.labelViewWeek }}</a>
                    <a href="#" @click.prevent="state = 'list'" :class="{active: state == 'list'}"><i class="icon-columns"></i>{{ trans.labelViewList }}</a>
                    <a href="#" @click.prevent="importInProgress = true"><i class="icon-columns"></i>Importer un ICS</a>
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

            <weekview v-show="state == 'week'"
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
                @submitall="submitall"
                @saveevent="restSave"></weekview>

            <listview v-show="state == 'list'"
                :with-owner="withOwner"
                @editevent="handlerEditEvent"
                @deleteevent="handlerDeleteEvent"
                @validateevent="handlerValidateEvent"
                @rejectevent="handlerRejectEvent"
                @submitevent="handlerSubmitEvent"></listview>
        </div>

    `,

    //                <!-- <a href="#" @click.prevent="state = 'month'"><i class="icon-table"></i>{{ trans.labelViewMonth }}</a> -->            <monthview v-show="state == 'month'"></monthview>

    data(){
        return store
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
            default() { return {
                labelViewWeek: "Semaine",
                labelViewMonth: "Mois",
                labelViewList: "Liste"
            }}
        }
    },

    components: {
        weekview: WeekView,
        monthview: MonthView,
        listview: ListView,
        eventitemimport: EventItemImport,
        importview: ImportICSView
    },

    methods: {
        submitall(status, period){
            var events = [];
            if( period == 'week' ){
                this.events.forEach( event => {
                   if( store.inCurrentWeek(event) && event.sendable ){
                       events.push(event);
                   }
                });
            }
            if( events.length ){
                this.restStep(events, status);
            }
        },

        importEvents(events){
            var datas = [];
            events.forEach( item => {
                var event = JSON.parse(JSON.stringify(item));
                if( event.useLabel ) event.label = event.useLabel;
                event.mmStart = moment(event.start);
                event.mmEnd = moment(event.end);
                datas.push(event);
            })
            this.importInProgress = false;
            this.restSave(datas);
        },

        handlerCreatePack(events){
            console.log("create pack !");
            this.restSave(events);
        },

        confirmImport(){
          console.log('Tous ajouter');
        },

        handleradd(pack, event){
            console.log("Ajout unitaire", event);
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

        handlerValidateEvent(event){
            this.restValidate([event])
        },

        handlerRejectEvent(event){
            this.restStep([event], 'reject');
        },

        restSave(events){
            if( this.restUrl ){
                this.transmission = "Enregistrement des données";
                var data = new FormData();
                for( var i=0; i<events.length; i++ ){
                    data.append('events['+i+'][label]', events[i].label);
                    data.append('events['+i+'][description]', events[i].description);
                    data.append('events['+i+'][start]', events[i].mmStart.format());
                    data.append('events['+i+'][end]', events[i].mmEnd.format());
                    data.append('events['+i+'][id]', events[i].id || null);
                    if( this.customDatas ){
                        var customs = this.customDatas();
                        for (var k in customs) {
                            if (customs.hasOwnProperty(k) && events[i].label == k) {
                                for (var l in customs[k]) {
                                    if (customs[k].hasOwnProperty(l)) {
                                        data.append('events['+i+']['+l+']', customs[k][l]);
                                    }
                                }
                            }
                        }
                    }
                }

                this.$http.post(this.restUrl(), data).then(
                    response => {
                        store.sync(response.body.timesheets);
                        this.handlerEditCancelEvent();
                    },
                    error => {
                        this.errors.push("Impossible d'enregistrer les données : " + error)
                    }
                ).then(()=> this.transmission = "" );;
            }
        },

        restSend(events){
            this.restStep(events, 'send')
        },

        restValidate(events){
            this.restStep(events, 'validate')
        },

        restStep(events, action){
            if( this.restUrl ){
                this.transmission = "Enregistrement en cours...";
                var data = new FormData();
                data.append('do', action);
                for( var i=0; i<events.length; i++ ){
                    data.append('events['+i+'][id]', events[i].id || null);
                }

                this.$http.post(this.restUrl(), data).then(
                    response => {
                        store.sync(response.body.timesheets);
                        this.handlerEditCancelEvent();
                    },
                    error => {
                        this.errors.push("Impossible de modifier l'état du créneau : " + error)
                    }
                ).then(()=> { this.transmission = ""; });
            }
        },

        /** Suppression de l'événement de la liste */
        handlerDeleteEvent(event){
            if( this.restUrl ){
                this.transmission = "Suppression...";
                this.$http.delete(this.restUrl()+"?timesheet="+event.id).then(
                    response => {
                        this.events.splice(this.events.indexOf(event), 1);
                    },
                    error => {
                        console.log(error)
                    }
                ).then(()=> { this.transmission = ""; });
            } else {
                this.events.splice(this.events.indexOf(event), 1);
            }
        },

        handlerSaveMove(event){
            console.log('handlerSaveMove(',event,')');
            var data = JSON.parse(JSON.stringify(event));
            data.mmStart = moment(data.start);
            data.mmEnd = moment(data.end);;
            this.restSave([data]);
        },

        handlerSaveEvent(event){
            console.log('handlerSaveEvent(event)');
            var data = JSON.parse(JSON.stringify(this.eventEditData));
            data.mmStart = this.eventEdit.mmStart;
            data.mmEnd = this.eventEdit.mmEnd;
            this.restSave([data]);
        },

        /** Soumission de l'événement de la liste */
        handlerSubmitEvent(event){
            console.log('Envoi', arguments)
            this.restSend([event]);
        },

        /** Soumission de l'événement de la liste */
        handlerCreateEvent(event){
            this.restSave([event]);
//            this.events.push(event);
        },

        /** Charge le fichier ICS depuis l'interface **/
        loadIcsFile(e){
            this.transmission = "Analyse du fichier ICS...";
            var fr = new FileReader();
            fr.onloadend = (result)=> {
                this.parseFileContent(fr.result);
            };
            fr.readAsText(e.target.files[0]);
        },

        /** Parse le contenu ICS **/
        parseFileContent(content){
            var analyser = new ICalAnalyser();
            var events = analyser.parse(ICAL.parse(content));
            this.importedData = [];

            events.forEach( item => {
                item.mmStart = moment(item.start);
                item.mmEnd = moment(item.end);

                let currentPack = null;
                let currentLabel = item.mmStart.format('YYYY-MM-D');
                for( let i=0; i<this.importedData.length && currentPack == null ; i++ ){
                    if( this.importedData[i].label == currentLabel ){
                        currentPack = this.importedData[i];
                    }
                }
                if( !currentPack ){
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
                  { editable: true, deletable: true},
                  'draft');
          })
        },

        deleteEvent(event){
            this.events.splice(this.events.indexOf(event), 1);
        },

        createEvent(day,time){
            var start = moment(this.currentDay).day(day).hour(time);
            var end = moment(start).add(2, 'hours');
            this.newEvent(new EventDT(null, this.defaultLabel, start.format(), end.format(), this.defaultDescription, { editable: true, deletable: true}));
        },

        editEvent(event){
            this.eventEdit = event;
            this.eventEditData = JSON.parse(JSON.stringify(event));
        },

        editSave(){
            console.log('deprecated');
        },

        editCancel(){
            this.eventEdit = this.eventEditData = null;
        },

        /////////////////////////////////////////////////////////////////// REST
        fetch(){
            this.transmission = "Chargement des créneaux...";

            this.$http.get(this.restUrl()).then(
                ok => {
                    store.sync(ok.body.timesheets);
                },
                ko => {
                    this.errors.push("Impossible de charger les données : " + ko)
                }
            ).then(()=> { this.transmission = "";});
        },

        post(event){
            console.log("POST", event);
        }
    },

    mounted(){
        if( this.customDatas ){
            var customs = this.customDatas();
            for (var k in customs) {
                if (customs.hasOwnProperty(k)) {
                    colorLabels[k] = colorpool[colorIndex];
                    if( !store.defaultLabel ){
                        store.defaultLabel = k;
                    }
                    store.labels.push(k);
                }
            }
            colorIndex++;
        }

        if( this.restUrl ){
            this.fetch();
        }
    }
};
