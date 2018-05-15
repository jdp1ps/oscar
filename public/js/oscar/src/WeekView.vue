<template>
    <div class="calendar calendar-week">
        <div class="meta">
            <a href="#" @click="previousWeek"><i class=" icon-angle-left"></i></a>
            <h3>
                Semaine {{ currentWeekNum}}, {{ currentMonth }} {{ currentYear }}
                <small class="total-heures-semaine"> ({{ totalWeek }} heures)</small>
                <nav class="reject-valid-group">
                    <i class=" icon-angle-down"></i>
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
            <a href="#" @click="nextWeek"><i class=" icon-angle-right"></i></a>
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
                        <div class="hour" v-for="time in 16" @dblclick="handlerCreate(day, time+5)">&nbsp;</div>
                        <div class="hour houroff" v-for="time in 2">&nbsp;</div>
                    </div>
                    <div class="content-events">
                        <div class="gost" :style="gostStyle" v-show="gostDatas.drawing">&nbsp;</div>
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

        <header class="line week-days-total">
            <div class="content-full" style="margin-right: 12px">
                <div class="labels-time">-</div>
                <div class="events">
                    <div class="cell cell-day day day-1" v-for="t in weekCredentials.total">
                        <strong>{{ t }}</strong> heure(s)
                    </div>
                </div>
            </div>
        </header>
    </div>
</template>
<script>
    export default {
        data(){
            return this.store
        },

        props: {
            'withOwner': {default: false},
            'createNew': {default: false},
            'pas': {default: 15},
            'moment': {require: true},
            'store': { require: true }
        },

        components: {
            'timeevent': TimeEvent
        },

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
                let days = [], day = this.moment(this.currentDay.startOf('week'));

                for (let i = 0; i < 7; i++) {
                    days.push(this.moment(day.format()));
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
                var store = this.store;

                this.weekCredentials = store.defaultWeekCredentials();
                var totalW = 0;

                this.events.forEach(event => {
                    // On filtre les événements de la semaine et le déclarant si besoin
                    if (
                        store.inCurrentWeek(event)
                        && (store.filterActivity == '' || store.filterActivity == event.activityId)
                        && (store.filterOwner == '' || store.filterOwner == event.owner_id )
                        && (store.filterType == '' || store.filterType == event.status )
                    ){
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
                        this.weekCredentials.total[event.mmStart.day()-1] += event.duration;
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

            handlerMouseDown(e){
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
                var hours = Math.floor(time);
                var minutes = Math.round((time - hours)*60);
                var start = this.moment(this.currentDay).day(day).hour(time).minutes(minutes);
                var end = this.moment(start).add(duration, 'minutes');
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
                    var dayRef = this.moment(event.start).format('MMMM D YYYY');
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
                    this.$emit('createpack', this.store.pasteDay(day))
                }
            },

            pasteWeek(){
                if (this.copyWeekData) {
                    this.$emit('createpack', this.store.pasteWeek())
                }
            },

            previousWeek(){
                this.currentDay = this.moment(this.currentDay).add(-1, 'week');
            },

            nextWeek(){
                this.currentDay = this.moment(this.currentDay).add(1, 'week');
            },

            isToday(day){
                return day.format('YYYY-MM-DD') == this.store.today.format('YYYY-MM-DD');
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
    }
</script>