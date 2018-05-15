<template>
    <div class="calendar">

    <transition name="fade">

        <div class="calendar-tooltip" :class="'status-'+tooltip.event.status" v-if="tooltip">
            <h3><i class="picto"></i> {{ tooltip.event.label }}</h3>
            <p>Statut : <strong>{{ tooltip.event.status }}</strong></p>
            <p>Déclarant : <strong>{{ tooltip.event.owner }}</strong>
                <span v-if="tooltip.event.sendAt">Envoyé le {{ tooltip.event.sendAt | moment }}</span>
            </p>

            <p v-if="tooltip.event.icsuid">
                N°ICS : <strong>{{ tooltip.event.icsuid }}</strong><br />
                ICAL : <strong>{{ tooltip.event.icsfilename }}</strong> <small>({{ tooltip.event.icsfileuid }})</small>
            </p>
            <p>Durée : <strong> {{ tooltip.event.duration }} heure(s)</strong></p>
            <p>Commentaire : <strong>{{ tooltip.event.description }}</strong></p>

            <template v-if="tooltip.event.rejectedSciAt">
                <h4><i class="icon-beaker"></i> Refus scientifique</h4>
                Refusé par <strong>{{ tooltip.event.rejectedSciBy }}</strong>
                le <time>{{ tooltip.event.rejectedSciAt | moment }}</time>
                <p>Motif : <strong>{{ tooltip.event.rejectedSciComment }}</strong></p>
            </template>

            <template v-if="tooltip.event.validatedSciAt">
                <h4><i class="icon-beaker"></i> Validation scientifique</h4>
                Validé par <strong>{{ tooltip.event.validatedSciBy }}</strong>
                le <time>{{ tooltip.event.validatedSciAt | moment }}</time>
            </template>

            <template v-if="tooltip.event.status == 'send' && tooltip.event.validatedSciAt == null && tooltip.event.rejectedSciAt == null">
                <h4><i class="icon-archive"></i> Validation scientifique en attente...</h4>
            </template>

            <template v-if="tooltip.event.rejectedAdminAt">
                <h4><i class="icon-archive"></i> Refus administratif</h4>
                Refusé par <strong>{{ tooltip.event.rejectedAdminBy }}</strong>
                le <time>{{ tooltip.event.rejectedAdminAt | moment }}</time>
                <p>Motif : <strong>{{ tooltip.event.rejectedAdminComment }}</strong></p>
            </template>

            <template v-if="tooltip.event.validatedAdminAt">
                <h4><i class="icon-archive"></i> Validation administrative</h4>
                Validé par <strong>{{ tooltip.event.validatedAdminBy }}</strong>
                le <time>{{ tooltip.event.validatedAdminAt | moment }}</time>
            </template>

            <template v-if="tooltip.event.status == 'send' && tooltip.event.validatedAdminAt == null && tooltip.event.rejectedAdminAt == null">
                <h4><i class="icon-archive"></i> Validation administrative en attente...</h4>
            </template>

            <template v-if="tooltip.event.status == 'draft'">
                <h4><i class="icon-pencil"></i> Brouillon</h4>
                Ce créneau n'a pas encore été soumis à validation.
            </template>

            <template v-if="tooltip.event.status == 'info'">
                <h4><i class="icon-info"></i> Indicatif</h4>
                <p>Ce créneau est ici à titre indicatif</p>
            </template>
        </div>
    </transition>

    <importview :creneaux="labels"
                @cancel="importInProgress = false"
                @import="importEvents"
                v-if="importInProgress"
                @deleteics="handlerDeleteImport"></importview>

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
                        <i class="icon-archive"></i>Rejet administratif
                    </h2>
                    Ce créneau a été refusé par <strong>{{ rejectShow.rejectedAdminBy }}</strong>  le <time>{{ rejectShow.rejectedAdminAt | moment}}</time> au motif :
                    <pre>{{ rejectShow.rejectedAdminComment }}</pre>
                </section>
                <section v-if="rejectShow.rejectedSciAt" class="card">
                    <h2>
                        <i class="icon-archive"></i>Rejet scientifique
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
            <a href="#" @click.prevent="importInProgress = true" v-if="createNew"><i class="icon-columns"></i>Importer un ICS</a>
        </nav>
        <template v-if="calendarLabelUrl.length">
                        <span><a :href="calendarLabelUrl">{{ calendarLabel }}</a></span>
        </template>
        <template v-else><span>{{ calendarLabel }}</span></template>

        <span v-if="owners.length">
                    <select v-model="filterOwner" class="input-sm">
                      <option value="">Tous les déclarants</option>
                      <option v-for="owner in owners" :value="owner.id">{{ owner.displayname }}</option>
                    </select>
                </span>
        <span v-else>
                    <select v-model="filterActivity" class="input-sm">
                        <option value="">Toutes</option>
                        <option v-for="a in activities" :value="a.id">{{ a.label }}</option>
                    </select>
                </span>
        <select v-model="filterType" class="input-sm">
            <option value="">Tous les états</option>
            <option v-for="label, key in status" :value="key">{{ label }}</option>
        </select>

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
              :store="store"
              :moment="moment"
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

    <!-- <listview v-if="state == 'list'"
              :with-owner="withOwner"
              :store="store"
              :moment="moment"
              @editevent="handlerEditEvent"
              @deleteevent="handlerDeleteEvent"
              @validateevent="handlerValidateEvent"
              @rejectevent="handlerRejectEvent"
              @submitevent="handlerSubmitEvent"></listview> -->
</div>
</template>
<script>
    import WeekView from './WeekView.vue';
    import ListView from './ListView.vue';
    import EventItemImport from './EventItemImport.vue';
    import ImportICSView from './ImportICSView.vue';
    import SelectEditable from './SelectEditable.vue';

    import Datepicker from './Datepicker.vue';
    import TimeEvent from './TimeEvent.vue';
    import moment from 'moment';

    class EventDT {
        constructor(data) {
            this.sync(data);
        }

        get isLocked(){
            return !(this.sendable || this.validableAdm || this.validableSci || this.editable || this.deletable);
        }

        get isSend(){
            return this.status == 'send';
        }

        get isInfo(){
            return this.status == 'info';
        }

        get isValidSci(){
            return this.validatedSciAt != null;
        }

        get isValidAdmin(){
            return this.validatedAdminAt != null;
        }

        get isRejecteSci(){
            return this.rejectedSciAt != null;
        }

        get isRejecteAdmin(){
            return this.rejectedAdminAt != null;
        }

        get isValid(){
            return this.isValidAdmin && this.isValidSci;
        }

        get isReject(){
            return this.isRejecteAdmin || this.isRejecteSci;
        }

        /**
         * Retourne un objet moment pour la date de début.
         */
        get mmStart() {
            return moment(this.start)
        }

        /**
         * Retourne un objet moment pour la date de fin.
         */
        get mmEnd() {
            return moment(this.end)
        }

        /**
         * Retourne la durée de l'événement en minutes.
         * @returns {number}
         */
        get durationMinutes() {
            return (this.mmEnd.unix() - this.mmStart.unix()) / 60;
        }

        /**
         * Retourne la durée de l'événement en heure.
         * @returns {number}
         */
        get duration() {
            return this.durationMinutes / 60;
        }

        get dayTime() {
            return "de " + this.mmStart.format('hh:mm')
                + " à " + this.mmEnd.format('hh:mm')
                + ", le " + this.mmStart.format('dddd D MMMM YYYY');
        }

        /**
         * Test si l'événement est présent dans la semaine.
         * @return boolean
         */
        inWeek(year, week) {
            let mmStart = this.mmStart.unix(),
                mmEnd = this.mmEnd.unix();

            // Récupération de la plage de la semaine
            let weekStart = moment().year(year).week(week).startOf('week'),
                plageStart = weekStart.unix(),
                plageFin = weekStart.endOf('week').unix();

            if (mmStart > plageFin || mmEnd < plageStart)
                return false

            return mmStart < plageFin || mmEnd > plageStart;
        }

        overlap(otherEvent) {
            let startU1 = this.mmStart.unix()
                , endU1 = this.mmEnd.unix()
                , startU2 = otherEvent.mmStart.unix()
                , endU2 = otherEvent.mmEnd.unix()
            ;
            return startU1 < endU2 && startU2 < endU1;
        }

        isBefore(eventDT) {
            if (this.mmStart < eventDT.mmStart) {
                return true;
            }
            return false;
        }

        sync(data) {
            this.id = data.id;
            this.label = data.label;
            this.description = data.description == 'undefined' ? '' : data.description;
            this.start = data.start;
            this.end = data.end;
            this.icsfileuid = data.icsfileuid;
            this.icsfilename = data.icsfilename;
            this.icsfiledateadded = data.icsfiledateadded;
            this.icsuid = data.icsuid;
            this.status = data.status;

            if( !this.uid ){
                this.uid = EventDT.UID++;
            }

            this.workpackageId = data.workpackage_id || null;
            this.workpackageCode = data.workpackage_code || null;
            this.workpackageLabel = data.workpackage_label || null;

            this.activityId = data.activity_id || null;
            this.activityLabel = data.activity_label || null;

            this.owner = data.owner;
            this.owner_id = data.owner_id;
            this.decaleY = 0;

            this.rejectedComment = data.rejectedComment;
            this.rejectedCommentAt = data.rejectedCommentAt;
            this.rejectedAdminComment = data.rejectedAdminComment;
            this.rejectedAdminCommentAt = data.rejectedAdminCommentAt;

            this.rejectedSciComment = data.rejectedSciComment;
            this.rejectedSciAt = data.rejectedSciAt;
            this.rejectedSciBy = data.rejectedSciBy;
            this.rejectedAdminComment = data.rejectedAdminComment;
            this.rejectedAdminAt = data.rejectedAdminAt;
            this.rejectedAdminBy = data.rejectedAdminBy;

            this.validatedSciAt = data.validatedSciAt;
            this.validatedSciBy = data.validatedSciBy;
            this.validatedAdminAt = data.validatedAdminAt;
            this.validatedAdminBy = data.validatedAdminBy;

            this.editable = false;
            this.deletable = false;
            this.validable = false;
            this.validableAdm = false;
            this.validableSci = false;
            this.sendable = false;

            if (data.credentials) {
                this.editable = data.credentials.editable;
                this.deletable = data.credentials.deletable;
                this.validable = false;
                this.validableAdm = data.credentials.validableAdm;
                this.validableSci = data.credentials.validableSci;
                this.sendable = data.credentials.sendable;
            }
        }

        static first(events) {
            var first = null;
            events.forEach((e1) => {
                if (first == null) {
                    first = e1;
                } else {
                    if (e1.isBefore(first)) {
                        first = e1;
                    }
                }
            });
            return first;
        }


        static sortByStart(events) {
            var sorted = events.sort((e1, e2) => {
                if (e1.mmStart < e2.mmStart)
                    return -1

                else if (e1.mmStart > e2.mmStart)
                    return 1

                return 0;
            });
            return sorted;
        }
    }

    EventDT.UID = 1;

    class CalendarDatas {
        constructor() {
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
            this.transformLong = [
                { startHours: 8, startMinutes: 0, endHours: 12, endMinutes: 0 },
                { startHours: 13, startMinutes: 0, endHours: 17, endMinutes: 0 }
            ];
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

        tooltipUpdate(){
            console.log(arguments);
        }

        defaultWeekCredentials() {
            return {
                send: false,
                adm: false,
                sci: false,
                admdaily: [false, false, false, false, false, false, false, false],
                scidaily: [false, false, false, false, false, false, false, false],
                senddaily: [false, false, false, false, false, false, false, false],
                copydaily: [false, false, false, false, false, false, false, false],
                total: [0,0,0,0,0,0,0]
            };
        }

        /**
         * Retourne les données pour afficher la feuille de temps.
         */
        timesheetDatas(){
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
                            totalWP: [],
                            months: {}
                        };
                        wpReference.forEach((value, i) => {
                            packActivity.persons[event.owner_id].totalWP[i] = 0.0;
                        });
                    }
                    packPerson = packActivity.persons[event.owner_id];
                    packPerson.total += event.duration;

                    // Regroupement par mois
                    let monthKey = event.mmStart.format('MMMM YYYY');
                    if (!packPerson.months[monthKey]) {
                        packPerson.months[monthKey] = {
                            total: 0.0,
                            wps: [],
                            days: {}
                        };
                        wpReference.forEach((value, i) => {
                            packPerson.months[monthKey].wps[i] = 0.0;
                        });
                    }
                    packMonth = packPerson.months[monthKey];
                    packMonth.total += event.duration;
                    let wpKey = wpReference.indexOf(this.wps[event.label].code);
                    packMonth.wps[wpKey] += event.duration;
                    packPerson.totalWP[wpKey] += event.duration;

                    let dayKey = event.mmStart.format('dddd D MMMM YYYY');
                    if (!packMonth.days[dayKey]) {
                        packMonth.days[dayKey] = {
                            total: 0.0,
                            comments: "",
                            wps: []
                        };
                        wpReference.forEach((value, i) => {
                            packMonth.days[dayKey].wps[i] = 0.0;
                        });
                    }

                    packDay = packMonth.days[dayKey];
                    packDay.wps[wpKey] += event.duration;
                    packDay.total += event.duration;
                    if( event.description ){
                        packDay.comments += event.description + "\n";
                    }



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

        getIcsByUid( uid ){
            for( let i=0; i<this.ics.length; i++ ){
                if( this.ics[i].icsfileuid == uid ){
                    return this.ics[i];
                }
            }
            return null;
        }

        addIcsRef(event){
            this.ics.push({
                icsfileuid: event.icsfileuid,
                icsfilename: event.icsfilename,
                icsfiledateAdded: event.icsfiledateadded
            })
        }

        addNewEvent(data) {
            if( data.icsfileuid && !this.getIcsByUid(data.icsfileuid) )
                this.addIcsRef(data);

            this.events.push(
                new EventDT(data)
            );
        }
    }

    var store = new CalendarDatas();
    console.log(store);

    export default {

        data(){
            return store
        },

        props: {
            moment: {
                required: true
            },

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
                default() {
                    return {
                        labelViewWeek: "Semaine",
                        labelViewMonth: "Mois",
                        labelViewList: "Liste"
                    }
                }
            }
        },

        computed: {
            store(){
                return store
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
            listview: ListView,
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
//                    bootbox.confirm("Soumettre le(s) créneau(x) ?", (confirm) => {
//                        if (confirm)
//                            this.restStep(events, status);
//                    });
                }
            },

            handlerRejectShow(event){
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
//                    bootbox.confirm("Soumettre le(s) créneau(x) ?", (confirm) => {
//                        if (confirm)
//                            this.restStep(events, 'send');
//                    });
                }
            },

            getEventByIcsUid( uid ){
                for( let i = 0; i<this.events.length; i++ ){
                    if( this.events[i].icsuid == uid ){
                        return this.events[i];
                    }
                }
                return null;
            },

            importEvents(events){
                var datas = [];
                events.forEach(item => {


                    var  event = JSON.parse(JSON.stringify(item)),
                        exist = this.getEventByIcsUid(item.icsuid),
                        itemStart = moment(event.start),
                        itemEnd = moment(event.end),
                        duration = itemEnd - itemStart;

                    if( exist ){
                        event.id = exist.id;
                    }

                    if (event.useLabel) event.label = event.useLabel;

                    if( duration / 1000 / 60 / 60 > 9 ){
                        this.transformLong.forEach(transform => {
                            var itemTransformed =  JSON.parse(JSON.stringify(event));
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
                })
                this.importInProgress = false;

                this.restSave(datas);
            },

            handlerCreatePack(events){
                this.restSave(events);
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
                    // bootbox.alert("Aucun créneaux ne peut être rejeté");
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
//                    bootbox.confirm(type == 'sci' ?
//                        '<i class="icon-beaker"></i> Validation scientifique ' + message
//                        : '<i class="icon-archive"></i>   Validation administrative' + message
//                        , (response) => {
//                            if (response) {
//                                this.restStep(events, 'validate' + type)
//                            }
//                        })
                } else {
                    // bootbox.alert("Aucun créneau ne peut être validé");
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

                    var datas =  [];

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
                    var datas = [];
                    for (var i = 0; i < events.length; i++) {
                        datas.push({
                            id:  events[i].id || null,
                            rejectedSciComment: events[i].rejectedSciComment || null,
                            rejectedAdminComment: events[i].rejectedAdminComment || null
                        });
                    }
                    data.append('events', JSON.stringify(datas));
                    this.loading = true;
                    this.$http.post(this.restUrl(), data).then(
                        response => {
                            store.sync(response.body.timesheets);
                            this.displayRejectModal = false;
                            this.handlerEditCancelEvent();
                        },
                        error => {
                            this.errors.push("Impossible de modifier l'état du créneau : " + error);

                            this.remoteError = "Erreur : " + error.statusText;
                        }
                    ).then(() => {
                        this.transmission = "";
                        this.loading = false;
                    });
                }
            },

            handlerDeleteImport(icsuid){
                console.log("Suppression des événements issues de l'import", icsuid);
                this.transmission = "Suppression...";
                this.$http.delete(this.restUrl() + "?icsuid=" + icsuid).then(
                    response => {
                        store.events = [];
                        this.fetch();
                    },
                    error => {
                        store.errors.push(error);
                    }
                ).then(() => {
                    this.transmission = "";
                });
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
//                    bootbox.confirm("Soumettre le(s) " + eventsSend.length + " créneau(x) ?", (confirm) => {
//                        if (confirm)
//                            this.restSend(eventsSend);
//                    });
                } else {
                    //bootbox.alert("Aucun créneau à envoyer.");
                }
            },

            /** Soumission de l'événement de la liste */
            handlerCreateEvent(event){
                this.handlerEditEvent(new EventDT(event));
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

                var analyser = new ICalAnalyser(
                    new Date(),
                    [{startTime: '9:00', endTime: '12:30'}, {startTime: '14:00', endTime: '17:30'}]
                );

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
                this.ics = [];
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
            var allowState = ['week', 'list', 'timesheet'];

            var colorLabels = {};
            var colorIndex = 0;
            var colorpool = ['#fcdc80', '#a6cef8', '#9fd588', '#fb90bb', '#e5fbed', '#99a0ce', '#bca078', '#f3cafd', '#d9f4c1', '#60e3bb', '#f2c7f5', '#f64bc0', '#ffc1b2', '#fc9175', '#d7fc74', '#e3d7f8', '#9ffab3', '#d6cbac', '#4dd03c', '#f8f3be'];

            var colorLabel = (label) => {
                if (!colorLabels[label]) {
                    colorLabels[label] = colorpool[++colorIndex];
                    colorIndex = colorIndex % colorpool.length;
                }
                return colorLabels[label];
            };


            this.state = 'week';
            if( allowState.indexOf(window.location.hash.substring(1)) >= 0 ){
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

                        if( customs[k].active ){
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
    }
</script>