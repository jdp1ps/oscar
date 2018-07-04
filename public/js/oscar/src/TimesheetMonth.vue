<template>
    <section @click="handlerClick">

        <div class="overlay" v-if="error"  style="z-index: 2002">
            <div class="content container overlay-content">
                <h2><i class="icon-attention-1"></i> Oups !</h2>
                <p class="text-danger">
                    Cette opération a provoqué une erreur. Après avoir refermé toutes les fenêtres Oscar, reconnectez
                    vous et retentez l'opération. Si l'erreur persiste, veuillez transmettre le message ci dessous à
                    l'administrateur de l'application :
                </p>
                <pre class="alert alert-danger">{{ error }}</pre>
                <nav class="buttons">
                    <button class="btn btn-primary" @click="error = ''">Fermer</button>
                </nav>
            </div>
        </div>

        <div class="overlay" v-if="selectedDay && selectionWP && selectionWP.code " style="z-index: 2001">
            <div class="content container overlay-content">
                <section>
                    <h3 v-if="selectionWP.id">
                        <small>Déclaration pour le lot</small>
                        <strong>
                            <i  class="icon-archive"></i>
                            <abbr>{{selectionWP.code}}</abbr> {{selectionWP.label}}
                        </strong>
                    </h3>
                    <h3 v-else>
                        <small>Déclaration hors-lot pour</small>
                        <strong>
                            <i :class="'icon-' + selectionWP.code"></i>
                            {{ selectionWP.label }}
                        </strong>
                    </h3>
                </section>

                <div class="col">
                    <div class="col-md-2">
                        <h4>Infos</h4>
                        <p>
                            Journée : <strong>{{ selectedDay.date | datefull }}</strong><br/>
                            Heures restantes : <strong>{{ (selectedDay.dayLength - selectedDay.duration) | duration}}</strong>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h4>Temps</h4>
                        <timechooser @timeupdate="handlerDayUpdated" :baseTime="ts.daylength" :fill="fillDayValue"></timechooser>
                    </div>
                    <div class="col-md-4">
                        <h4>Commentaire</h4>
                        <textarea class="form-control textarea" v-model="commentaire"></textarea>
                    </div>

                </div>

                <nav class="buttons">
                    <button class="btn btn-default" @click="selectionWP = null">Annuler</button>
                    <button class="btn btn-primary" @click="handlerSaveMenuTime">Valider</button>
                </nav>
            </div>
        </div>

        <div :style="cssDayMenu" class="daymenu">
            <div class="selector">
                <div class="choose-wp">
                    <ul class="menu-wps" v-if="ts">
                        <li v-for="wp in ts.workPackages" class="menu-wps-item"
                            :class="{ 'selected': wp == selectedWP }"
                            @click.prevent.stop="handlerSelectWP(wp)">
                            <i class="icon-cubes"></i>
                            <span class="acronym">{{ wp.acronym }}</span>
                            <span>{{ wp.code }}</span>
                            <i class="icon-angle-right"></i>
                        </li>
                        <li><i class="icon-leaf"></i>Congès</li>
                        <li><i class="icon-book-1"></i>Formation</li>
                        <li><i class="icon-graduation-cap"></i>Enseignement</li>
                        <li><i class="icon-beaker"></i>Autre recherche</li>
                        <li><i class="icon-beaker"></i>Autre activités...</li>
                    </ul>
                </div>
            </div>
        </div>


        <section v-if="ts" >
            <!-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% VUE CALENDRIER %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% -->
            <div class="month col-lg-8">

                <h2>Déclarations de temps pour <strong>{{ ts.person }}</strong></h2>
                <h3 class="periode">Période :
                    <a href="#" @click.prevent="prevMonth"><i class="icon-angle-left"/></a>
                    <strong>{{ mois }}</strong>
                    <a href="#" @click.prevent="nextMonth"><i class="icon-angle-right"/></a>
                </h3>

                <div class="month">
                    <header class="month-header">
                        <strong>Lundi</strong>
                        <strong>Mardi</strong>
                        <strong>Mercredi</strong>
                        <strong>Jeudi</strong>
                        <strong>Vendredi</strong>
                        <strong>Samedi</strong>
                        <strong>Dimanche</strong>
                    </header>
                    <div class="weeks">
                        <section v-for="week in weeks" v-if="ts" class="week" :class="selectedWeek == week ? 'selected' : ''">
                            <header class="week-header" @click="selectWeek(week)">
                                <span>Semaine {{ week.label }}</span>
                                <small>
                                    <em>Cumul des heures : </em>
                                    <strong :class="(week.total > week.weekLength)?'has-titled-error':''"
                                            :title="(week.total > week.weekLength)?
                                                'Les heures excédentaires risques d\'être ignorées lors d\'une justification financière dans le cadre des projets soumis aux feuilles de temps'
                                                :''">
                                        <i class="icon-attention-1" v-if="week.total > week.weekLength"></i>{{ week.total | duration }}</strong>

                                    / {{ week.weekLength | duration }}
                                </small>
                            </header>
                            <div class="days">
                                <timesheetmonthday v-for="day in week.days"
                                       :class="selectedDay == day ? 'selected':''"
                                       @selectDay="handlerSelectData(day)"
                                       @daymenu="handlerDayMenu"
                                       :day="day"
                                       :key="day.date"/>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <section class="col-lg-4">

                    <!-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% VUE DETAILS SEMAINE %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% -->
                    <div v-if="selectedWeek">
                        <a class="link" @click="selectedWeek = null">
                            <i class="icon-angle-left"></i> Revenir au mois
                        </a>
                        <h3>
                            <i class="icon-calendar"></i> Détails de la
                            <strong>semaine {{ selectedWeek.label }}</strong>
                        </h3>

                        <h4>Détails : </h4>
                        <article class="card xs total" :class="{ 'locked': d.locked, 'closed': d.closed }"
                                 v-for="d in selectedWeek.days"
                                 @click="handlerSelectData(d)"
                        >
                            <div class="week-header" :class="{ 'text-thin' : d.closed || d.locked }">
                                <span class="" >
                                    <i class="icon-minus-circled" v-if="d.closed"></i>
                                    <i class="icon-lock" v-else-if="d.locked"></i>
                                    <i class="icon-calendar" v-else></i>
                                    {{ d.data | datefull }}
                                </span>
                                <small>
                                    <strong class="text-large">{{ d.duration | duration }}</strong> /
                                    <span class="heure-total">{{ d.dayLength | duration }}</span>
                                </small>
                            </div>
                        </article>
                        <article class="card xs total">
                            <div class="week-header">
                                <span class="">
                                    <i class="icon-clock"></i>
                                    Heures déclarées <br>
                                    <small class="text-thin" v-if="selectedWeek.totalOpen < selectedWeek.weekLength">
                                        <i class="icon-attention-1"></i>
                                        Cette semaine n'est pas encore terminée
                                    </small>
                                </span>
                                <small class="text-big">
                                    <strong>{{ selectedWeek.total | duration }}</strong>
                                </small>
                            </div>
                        </article>

                        <nav class="buttons-bar">
                            <button class="btn btn-danger btn-xs" @click="deleteWeek(selectedWeek)" v-if="selectedWeek.drafts > 0">
                                <i class="icon-trash"></i>
                                Supprimer les déclarations non-envoyées
                            </button>
                        </nav>

                        <section v-if="selectedWeek.total < selectedWeek.totalOpen">
                            <p>
                                <i class="icon-help-circled"></i>
                                Vous pouvez compléter automatiquement cette semaine en affectant les
                                <strong>{{ (selectedWeek.totalOpen) | duration }} heure(s)</strong>
                                avec une des activités ci-dessous :
                            </p>
                            <wpselector :workpackages="ts.workPackages" :selection="fillSelectedWP" @select="fillSelectedWP = $event"></wpselector>
                            <button class="btn btn-default" @click="fillWeek(selectedWeek, fillSelectedWP)" :class="fillSelectedWP ? 'btn-primary' : 'disabled'">
                                <i class="icon-floppy"></i>
                                Valider
                            </button>
                        </section>


                        <pre>{{ selectedWeek }}</pre>

                    </div>
                    <!-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% VUE DETAILS JOUR %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% -->
                    <timesheetmonthdaydetails  v-else-if="selectedDay"
                            :day="selectedDay"
                            :workPackages="ts.workPackages"
                            :selection="selectionWP"
                            :label="dayLabel"
                            @cancel="selectedDay = null"
                            @removetimesheet="deleteTimesheet"
                            @addtowp="handlerWpFromDetails($event)"
                    />
                    <div v-else>
                        <h3>
                            <i class="icon-calendar"></i>
                            Mois de <strong>{{ mois }}</strong></h3>

                        <section v-for="week in weeks" v-if="ts" :class="selectedWeek == week ? 'selected' : ''" class="card xs">
                            <header class="week-header" @click="selectWeek(week)">
                                <span>Semaine {{ week.label }}</span>
                                <small>
                                    <em>Cumul des heures : </em>
                                    <strong :class="(week.total > week.weekLength)?'has-titled-error':''"
                                            :title="(week.total > week.weekLength)?
                                                'Les heures excédentaires risques d\'être ignorées lors d\'une justification financière dans le cadre des projets soumis aux feuilles de temps'
                                                :''">
                                        <i class="icon-attention-1" v-if="week.total > week.weekLength"></i>{{ week.total | duration }}</strong>

                                    / <span class="heure-total">{{ week.weekLength | duration }}</span>
                                </small>
                            </header>
                        </section>

                        <section class="card xs total">
                            <div class="week-header">
                                <span class="text-big text-xxl">Total</span>
                                <small>
                                    <strong class="text-large">{{ ts.total | duration }}</strong> /
                                    <span class="heure-total text-large">{{ ts.periodLength | duration }}</span>
                                </small>
                            </div>
                        </section>

                        <hr>

                        <h4><i class="icon-cubes"></i> Par projet</h4>
                        <section class="card xs" v-for="a in ts.activities">
                            <div class="week-header">
                                <span>
                                    <strong>{{ a.acronym }}</strong><br>
                                    <em class="text-thin">{{ a.label }}</em>
                                </span>
                                <small>
                                    <strong class="text-large">{{ a.total | duration }}</strong>
                                </small>
                            </div>
                        </section>

                        <nav class="buttons-bar">
                            <button class="btn btn-primary" style="margin-left: auto"
                                    :class="{ 'disabled': !ts.submitable, 'enabled': ts.submitable }"
                                    @click="sendMonth()">
                                <i class="icon-upload"></i>
                                <i class="icon-spinner animate-spin" v-show="loading"></i>
                                Soumettre mes déclarations
                            </button>
                        </nav>

                        <p v-if="ts.periodCurrent">
                            Mois en cours, vous ne pouvez soumettre vos heures qu'à la fin du mois.
                        </p>
                        <p v-else-if="ts.periodFutur">
                            FUTUR
                        </p>
                        <p v-else-if="ts.periodFinished">
                            PAST
                        </p>
                    </div>

            </section>
        </section>
    </section>
</template>

<style lang="scss">
    .interactive-icon-big {
        font-size: 32px;
        cursor: pointer;
    }

    .card.locked, .card.closed {
        opacity: .7;
        .week-header { cursor: default }
    }
    .card.closed {
        opacity: .4;
        .week-header { cursor: default }
    }

    article.wp {
        font-size: 1.2em;
        h3 {
            font-size: 1.2em;
            margin: 0;
            padding: 0;
        }
    }

    .has-titled-error {
        color: darkred;
        cursor: help;
    }

    .menu-wps {
        padding: 2px 4px;
        box-shadow: 0 0 1em rgba(0,0,0,.3);
        font-size: 12px;
        margin: 0;
        padding: 0;
        >li {
            cursor: pointer;
            display: flex;
            transition: background-color .5s ease-out;
            border-bottom: thin solid rgba(0,0,0,.4);
            .icon-angle-right {
                color: white;
                position: relative;
                left: -25px;
                opacity: 0;
                transition: left .3s ease-out, opacity .5s ease-out;
                margin-left: auto;
            }
            .acronym {
                font-weight: 700;
                &:after {
                    content: ':';
                }
            }


            padding: 2px 4px;
            text-shadow: -1px 1px 0 rgba(0,0,0,.1);
            &:hover, &.selected {
                background: #0b58a2;
                color:white;
                .icon-angle-right { left: 0px; opacity: 1; }
            }
        }
    }

    .daymenu {
        position: fixed;
        background: white;
        z-index: 100;
        .selector {
            display: flex;
        }
    }

    .month-header {
        display: flex;

        line-height: 30px;
        justify-content: center;
        justify-items: center;
        strong {
        font-weight: 100;
            display: block;
            text-align: center;
            flex: 0 0  14.285714286%;
            background: #efefef;
            color: #5c9ccc;
            border-left: solid thin #fff;
        }
    }
    .periode {
        display: flex;
        justify-content: flex-start;
    }
    .periode strong {
        display: inline-block;
        width: 10em;
        text-align: center;
    }

    ///////////////////////////////////////////////////

    $weekHightlightColor: #80b7ec;

    .week {
        border: solid 2px #fff;
        margin: 2px 0;
        .days {
            background-image: url('/images/bg-lock.gif');

            .day {
                background: #efefef;
            }
        }

        &.selected{
            background: rgba($weekHightlightColor, .25);
            border-color: $weekHightlightColor;
            box-shadow: 0 -4px 4px rgba(0,0,0,.2);
            .week-header {
                background-color: $weekHightlightColor;
                color: white;
                span {
                    text-shadow: -1px 1px 0 rgba(0,0,0,.2);
                }
            }
            .day {
                border-color: $weekHightlightColor;
            }
        }
    }

    .heure-total {
        display: inline-block;
        width: 5em;
    }

    /** EN TÊTE des SEMAINES **/
    .week-header, .month-header .week-header {
        background-color: rgba(255,255,255,.5);
        align-items: center;
        cursor: pointer;
        display: flex;
        text-align: left;
        font-size: 1.0em;
        padding: 0 .8em;
        justify-content: space-between;
        span {
            font-weight: 700;
            flex: 1;
        }
        small {
            em {
                color: #5c646c;
            }
            justify-self: flex-end;
            flex: 1;
            text-align: right;
            margin-left: auto;
        }
    }
    ///////////////////////////////////////////////////

    .days {
        display: flex;
//        height: 100px;

        .day {
            .label {
                position: absolute;
                top: 0;
                right: 0;
                display: block;
                font-size: 20px;
                text-align: right;
                text-shadow: -1px 1px 1px rgba(0,0,0,.2);
            }

            .cartouche em {
                max-width: 3em;
                overflow: hidden;
                display: inline-block;
                display: inline-block;
                white-space: nowrap;
            }

            position: relative;
            background: rgba(#ffffff, .25);
            transition: background-color linear .3s;
            border: thin solid white;
            flex: 0 0  14.285714286%;
            overflow: hidden;
            cursor: pointer;
            min-height: 50px;

            &:hover {
                background: white;
            }

            &.selected {
                background: #5c9ccc;
            }

            &.locked {
                cursor: not-allowed;
                background: rgba(#ccc, .8);
            }
        }
    }

    .week:first-child .days {
        justify-content: flex-end;
    }

    .week:last-child .days {
        justify-content: flex-start;
    }
</style>

<script>

   // import TimesheetMonthDay from './TimesheetMonthDay.vue';
    import TimesheetMonthDayDetails from './TimesheetMonthDayDetails.vue';
    import UITimeChooser from './UITimeChooser.vue';
    import TimesheetMonthWorkPackageSelector from './TimesheetMonthWorkPackageSelector.vue';

    let defaultDate = new Date();
    let moment = function(){};

    export default {
        props: {
            moment: {
                required: true
            },
            defaultMonth: { default: defaultDate.getMonth()+1},
            defaultYear: { default: defaultDate.getFullYear()},
            defaultDayLength: { default: 8.0 }
        },

        components: {
            timesheetmonthday: require('./TimesheetMonthDay.vue').default,
            timesheetmonthdaydetails: require('./TimesheetMonthDayDetails.vue').default,
            timechooser: require('./UITimeChooser.vue').default,
            wpselector: require('./TimesheetMonthWorkPackageSelector.vue').default
        },

        data(){
            return {
                // Gestion de l'affichage de la fenêtre
                // d'édition/ajout de créneaux
                editWindow: {
                    display: false,
                    wp: null,
                    type: 'infos',
                },

                loading: false,

                //
                error: '',
                commentaire: '',

                fillSelectedWP: null,

                // Données reçues
                ts: null,
                month: null,
                year: null,
                dayLength: null,
                selectedWeek: null,

                selectedDay: null,
                dayMenuLeft: 50,
                dayMenuTop: 50,
                dayMenu: 'none',
                selectedWP: null,
                selectionWP: null,
                selectedTime: null,
                dayMenuSelected: null,
                dayMenuTime: 0.0
            }
        },

        filters: {
            date(value, format="ddd DD MMMM  YYYY"){
                var m = moment(value);
                return m.format(format);
            },
            datefull(value, format="ddd DD MMMM  YYYY"){
                var m = moment(value);
                return m.format(format);
            },
            day(value, format="ddd DD"){
                var m = moment(value);
                return m.format(format);
            },
            duration(v){
                let h = Math.floor(v);
                let m = Math.round((v - h)*60);
                if( m < 10 ) m = '0'+m;
                return h +':' +m;
            }
        },

        computed: {
            dayLabel(){
                if( this.selectedDay )
                    return moment(this.selectedDay.data).format('dddd DD MMMM YYYY');
                else
                    return "";
            },

            /**
             * Retourne la durée de remplissage d'une journée.
             */
            fillDayValue(){
                let reste = this.selectedDay.dayLength - this.selectedDay.duration;
                if( reste < 0 ){
                    reste = 0;
                }
                return reste;
            },

            mois(){
                return moment(this.ts.from).format('MMMM YYYY');
            },

            cssDayMenu(){
              return {
                  display: this.dayMenu,
                  top: this.dayMenuTop +'px',
                  left: this.dayMenuLeft +'px'
              }
            },

            /**
             * Retourne les informations par semaine.
             *
             * @returns {Array}
             */
            weeks(){
                let weeks = [];
                if( this.ts && this.ts.days ){

                    let firstDay = this.ts.days[1];
                    let currentWeekNum = firstDay.week;

                    let currentWWeek = {
                        label: currentWeekNum,
                        days: [],
                        total: 0.0,
                        totalOpen: 0.0,
                        weekLength: 0.0,
                        drafts: 0
                    };

                    for( var d in this.ts.days ){
                        let currentDay = this.ts.days[d];

                        if( currentWeekNum != currentDay.week ){
                            weeks.push(currentWWeek);
                            currentWWeek = {
                                label: currentDay.week,
                                days: [],
                                total: 0.0,
                                totalOpen: 0.0,
                                weekLength: 0.0,
                                drafts: 0
                            };
                        }

                        currentWeekNum = currentDay.week;
                        currentWWeek.total += currentDay.duration;

                        if( !(currentDay.locked || currentDay.closed) ){
                            currentWWeek.totalOpen += currentDay.dayLength;
                        }

                        currentDay.declarations.forEach( d => {
                            if( d.status_id == 2 ){
                                currentWWeek.drafts++;
                            }
                        });

                        if( !currentDay.closed )
                            currentWWeek.weekLength += currentDay.dayLength;

                        currentWWeek.days.push(currentDay);
                    }
                    if( currentWWeek.days.length )
                        weeks.push(currentWWeek);
                }
                return weeks;
            }
        },

        methods: {

            sendMonth(){

                if( this.ts.submitable == undefined || this.ts.submitable != true ){
                    this.error = 'Vous ne pouvez pas soumettre vos déclarations pour cette période : ' + this.ts.submitableInfos;
                    return;
                }

                // Données à envoyer
                var datas = new FormData();
                datas.append('action', 'sendmonth');
                datas.append('datas', JSON.stringify({
                  from: this.ts.from,
                  to: this.ts.to
                }));

                this.loading = true;

                this.$http.post('', datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                      this.error = 'Erreur lors de l\'envoi de la période : ' + ko.body;
                    }
                ).then(foo => {
                  this.selectedWeek = null;
                    this.loading = false;
                });
            },

            fillMonth(){
                // TODO Remplissage automatique du mois
            },

            fillWeek(week, wp){
                let data = [];

                week.days.forEach( d => {
                   console.log("JOUR",JSON.parse(JSON.stringify(d)));
                   console.log("LOT", JSON.parse(JSON.stringify(wp)));
                   if( !(d.closed || d.locked || d.duration >= d.dayLength) ){
                       data.push({
                           'day': d.date,
                           'wpId': wp.id,
                           'code': wp.code,
                           'commentaire': this.commentaire,
                           'duration':(d.dayLength - d.duration)*60
                       });
                   }
                });
                this.performAddDays(data);
            },

            fillDay(){

            },

            selectWeek(week){
                this.selectedDay = null;
                this.selectedWeek = week;
            },

            deleteWeek(week){
                let ids = [];
                week.days.forEach(d => {
                    d.declarations.forEach(t => {
                        ids.push(t.id);
                    })
                })
                this.performDelete(ids);
            },

            deleteTimesheet(timesheet){
                this.performDelete([timesheet.id]);
            },

            performDelete( ids ){
                this.$http.delete('?id=' +ids.join(',')).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = 'Impossible de supprimer le créneau : ' + ko.body;
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                });
            },

            handlerSaveMenuTime(){


                let data = [{
                    'day': this.selectedDay.date,
                    'wpId': this.selectionWP.id,
                    'duration': this.dayMenuTime,
                    'comment' : this.commentaire,
                    'code': this.selectionWP.code
                }];

                this.performAddDays(data);
            },

            performAddDays(datas){
                let formData = new FormData();
                formData.append('timesheets', JSON.stringify(datas));

                this.$http.post('/feuille-de-temps/declarant-api', formData).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = "Impossible d'enregistrer les créneaux : " + ko.body;
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                    this.selectionWP = null;
                });;
            },

            handlerDayUpdated(){
              let t = arguments[0];
              this.dayMenuTime = (t.h + t.m) * 60;
            },

            handlerSelectWP(w){
                this.selectedWP = w;
                this.selectionWP = w;
                console.log(this.selectedDay);
                console.log(this.selectedWP);
                this.dayMenu = 'none';
            },

            hideWpSelector(){
                this.selectedWP = null;
                this.selectedTime = null;
                this.dayMenu = 'none';
            },

            handlerClick(){
               this.hideWpSelector();
            },


            handlerWpFromDetails(wp){
              console.log('TimesheetMonth', wp);
               this.handlerSelectWP(wp);
            },

            handlerDayMenu(event, day){

                this.dayMenuLeft = event.clientX;
                this.dayMenuTop = event.clientY;
                this.dayMenu = 'block';
//                this.dayMenuSelected = day;
                this.selectedDay = day;
            },

            handlerSelectData(day){
                this.selectedWeek = null;
                this.selectedDay = day;
            },

            nextYear(){
                this.year +=1;
                this.fetch();
            },
            nextMonth(){
                this.month +=1;
                if( this.month > 12 ){
                    this.month = 1;
                    this.nextYear();
                } else {
                    this.fetch();
                }
            },
            prevYear(){
                this.year -=1;
                this.fetch();
            },
            prevMonth(){
                this.month -=1;
                if( this.month < 1 ){
                    this.month = 12;
                    this.prevYear();
                } else {
                    this.fetch();
                }
            },
            fetch(){
                let daySelected;

                if( this.selectedDay )
                    daySelected = this.selectedDay.i;

                console.log('daySelected', daySelected);
                this.$http.get('?month=' +this.month +'&year=' + this.year).then(
                    ok => {
                        this.dayLength = ok.body.dayLength;
                        this.ts = ok.body
                        if( daySelected ){
                           this.selectedDay = this.ts.days[daySelected];
                        }
                    },
                    ko => {
                        this.error = 'Impossible de charger cette période (msg server : ' + ko.body +')';
                    }
                )
            }
        },
        mounted(){
            moment = this.moment;
            this.month = this.defaultMonth;
            this.year = this.defaultYear;
            this.dayLength = this.defaultDayLength;

            this.fetch()
        }
    }
</script>