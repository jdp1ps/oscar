<template>
    <div class="calendar calendar-list">
        <section v-for="eventsYear, year in listEvents" class="year-pack">
            <h2 class="flex-position">
                <strong>
                    <span @click="toggle(year)">
                        <i class="icon-right-dir" v-show="listEventsOpen.indexOf(year) == -1"></i>
                        <i class="icon-down-dir" v-show="listEventsOpen.indexOf(year) >= 0"></i>
                        {{year}}
                    </span>
                    <nav class="reject-valid-group" v-if="eventsYear.credentials.actions">
                        <i class=" icon-angle-down"></i>
                        <ul>
                            <li @click.prevent="performYear(eventsYear, 'submit')" v-if="eventsYear.credentials.send"><i class="icon-right-big"></i> Soumettre les créneaux de l'année</li>
                            <li @click.prevent="performYear(eventsYear, 'validatesci')" v-if="eventsYear.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement l'année</li>
                            <li @click.prevent="performYear(eventsYear, 'rejectsci')" v-if="eventsYear.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement l'année</li>
                            <li @click.prevent="performYear(eventsYear, 'validateadm')" v-if="eventsYear.credentials.adm"><i class="icon-archive"></i>Valider administrativement l'année</li>
                            <li @click.prevent="performYear(eventsYear, 'rejectadm')" v-if="eventsYear.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement l'année</li>
                            <li><i class="icon-archive"></i> Suprimmer les créneaux affichés</li>
                        </ul>
                    </nav>
                </strong>
                <span class="onright total">{{ eventsYear.total }} heure(s)</span>
            </h2>
            <section v-for="eventsMonth, month in eventsYear.months" class="month-pack" v-show="listEventsOpen.indexOf(year) >= 0">
                <h3 class="flex-position">
                    <strong>
                    <span  @click="toggle(year+'-'+month)">
                        <i class="icon-right-dir" v-show="listEventsOpen.indexOf(year+'-'+month) == -1"></i>
                        <i class="icon-down-dir" v-show="listEventsOpen.indexOf(year+'-'+month) >= 0"></i>
                        {{month}}
                    </span>
                        <nav class="reject-valid-group" v-if="eventsMonth.credentials.actions">
                            <i class=" icon-angle-down"></i>
                            <ul>
                                <li @click.prevent="performMonth(eventsMonth, 'submit')" v-if="eventsMonth.credentials.send"><i class="icon-right-big"></i> Soumettre les créneaux du mois</li>
                                <li @click.prevent="performMonth(eventsMonth, 'validatesci')" v-if="eventsMonth.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement le mois</li>
                                <li @click.prevent="performMonth(eventsMonth, 'rejectsci')" v-if="eventsMonth.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement le mois</li>
                                <li @click.prevent="performMonth(eventsMonth, 'validateadm')" v-if="eventsMonth.credentials.adm"><i class="icon-archive"></i>Valider administrativement le mois</li>
                                <li @click.prevent="performMonth(eventsMonth, 'rejectadm')" v-if="eventsMonth.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement le mois</li>
                            </ul>
                        </nav>
                    </strong>
                    <span class="onright total">{{eventsMonth.total}} heure(s)</span>
                </h3>
                <section v-for="eventsWeek, week in eventsMonth.weeks" class="week-pack" v-show="listEventsOpen.indexOf(year+'-'+month) >= 0">
                    <h4 class="flex-position">
                        <strong>Semaine {{week}} ~
                            <nav class="reject-valid-group" v-if="eventsWeek.credentials.actions">
                                <i class=" icon-angle-down"></i>
                                <ul>
                                    <li @click.prevent="performWeek(eventsWeek, 'submit')" v-if="eventsWeek.credentials.send"><i class="icon-right-big"></i> Soumettre les créneaux de la semaine</li>
                                    <li @click.prevent="performWeek(eventsWeek, 'validatesci')" v-if="eventsWeek.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement la semaine</li>
                                    <li @click.prevent="performWeek(eventsWeek, 'rejectsci')" v-if="eventsWeek.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement la semaine</li>
                                    <li @click.prevent="performWeek(eventsWeek, 'validateadm')" v-if="eventsWeek.credentials.adm"><i class="icon-archive"></i>Valider administrativement la semaine</li>
                                    <li @click.prevent="performWeek(eventsWeek, 'rejectadm')" v-if="eventsWeek.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement la semaine</li>
                                </ul>
                            </nav>
                        </strong>
                        <span class="onright total">{{eventsWeek.total}} heure(s)</span>
                    </h4>
                    <section v-for="eventsDay, day in eventsWeek.days" class="day-pack events">
                        <h5>{{day}}
                            <nav class="reject-valid-group" v-if="eventsDay.credentials.actions">
                                <i class=" icon-angle-down"></i>
                                <ul>
                                    <li @click.prevent="performDay(eventsDay, 'submit')" v-if="eventsDay.credentials.send"><i class="icon-right-big"></i> Soumettre les créneaux de la journée</li>
                                    <li @click.prevent="performDay(eventsDay, 'validatesci')" v-if="eventsDay.credentials.sci"><i class="icon-beaker"></i>Valider scientifiquement la journée</li>
                                    <li @click.prevent="performDay(eventsDay, 'rejectsci')" v-if="eventsDay.credentials.sci"><i class="icon-beaker"></i>Rejeter scientifiquement la journée</li>
                                    <li @click.prevent="performDay(eventsDay, 'validateadm')" v-if="eventsDay.credentials.adm"><i class="icon-archive"></i>Valider administrativement la journée</li>
                                    <li @click.prevent="performDay(eventsDay, 'rejectadm')" v-if="eventsDay.credentials.adm"><i class="icon-archive"></i>Rejeter administrativement la journée</li>
                                </ul>
                            </nav>
                        </h5>
                        <section class="events-list" :style="{ 'height': eventsDay.persons.length*1.8 +'em' }">
                            <listitem
                                    :with-owner="withOwner" :key="event.id"
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
    </div>
</template>
<script>
    import ListItemView from './ListItemView.vue';

    export default {
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
            open(){
                return store.listEventsOpen;
            }
        },

        props: ['withOwner'],

        components: {
            listitem: ListItemView
        },

        methods: {

            toggle(tag){
                if( store.listEventsOpen.indexOf(tag) == -1 ){
                    store.listEventsOpen.push(tag);
                } else {
                    store.listEventsOpen.splice(store.listEventsOpen.indexOf(tag), 1);
                }
            },

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

            performYear(yearPack, action){
                var events = [];
                for (var monthKey in yearPack.months) {
                    if (yearPack.months.hasOwnProperty(monthKey)) {
                        events = events.concat(this.getMonthPack(yearPack.months[monthKey]));
                    }
                }

                this.performEmit(events, action);
            },

            performMonth(monthPack, action){
                this.performEmit(this.getMonthPack(monthPack), action);
            },

            performWeek(weekPack, action){
                this.performEmit(this.getWeekPack(weekPack), action);
            },

            performDay(dayPack, action){
                this.performEmit(this.getDayPack(dayPack), action);
            },

            performEmit( events, action ){
                if( action == 'validatesci' ){
                    this.$emit('validateevent', events, 'sci');
                }
                else if( action == 'validateadm' ){
                    this.$emit('validateevent', events, 'adm');
                }
                else if( action == 'rejectsci' ){
                    this.$emit('rejectevent', events, 'sci');
                }
                else if( action == 'rejectadm' ){
                    this.$emit('rejectevent', events, 'adm');
                }
                else if( action == 'submit' ){
                    this.$emit('submitevent', events);
                }
            }
        },

        computed: {
            listEvents(){

                if (!store.listEvents) {
                    return null
                }

                var structure = {};
                var owners = [];
                var events = store.listEvents;



                for (let i = 0; i < events.length; i++) {
                    let event = events[i];
                    if (!(store.filterActivity == '' || store.filterActivity == event.activityId) ) continue;
                    if (!(store.filterOwner == '' || store.filterOwner == event.owner_id)) continue;
                    if (!(store.filterType == '' || store.filterType == event.status )) continue;

                    let currentYear, currentMonth, currentWeek, currentDay;
                    let duration = event.duration;
                    let labelYear = event.mmStart.format('YYYY');
                    let labelMonth = event.mmStart.format('MMMM');
                    let labelWeek = event.mmStart.format('W');
                    let labelDay = event.mmStart.format('ddd D');

                    if( owners.indexOf(event.owner_id) < 0 ){
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
                    if( currentDay.persons.indexOf(event.owner_id) < 0 ){
                        currentDay.persons.push(event.owner_id);
                    }

                    currentDay.events.push(event);

                    event.decaleY = currentDay.persons.indexOf(event.owner_id);

                    if( event.validableSci == true ){
                        currentYear.credentials.sci = currentMonth.credentials.sci = currentWeek.credentials.sci = currentDay.credentials.sci =
                            currentYear.credentials.actions = currentMonth.credentials.actions = currentWeek.credentials.actions = currentDay.credentials.actions = true;
                    }
                    if( event.validableAdm == true ){
                        currentYear.credentials.adm = currentMonth.credentials.adm = currentWeek.credentials.adm = currentDay.credentials.adm =
                            currentYear.credentials.actions = currentMonth.credentials.actions = currentWeek.credentials.actions = currentDay.credentials.actions = true;
                    }
                    if( event.sendable == true ){
                        currentYear.credentials.send = currentMonth.credentials.send = currentWeek.credentials.send = currentDay.credentials.send =
                            currentYear.credentials.actions = currentMonth.credentials.actions = currentWeek.credentials.actions = currentDay.credentials.actions = true;;
                    }

                }

                return structure;
            }
        }
    }
</script>