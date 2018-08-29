<template>
    <section @click="handlerClick">

        <transition name="fade">
            <div class="loading-message" v-show="loading">
                <i class="icon-spinner animate-spin"></i>
                {{ loading }}
            </div>
        </transition>

        <div class="overlay" v-if="error"  style="z-index: 2002">
            <div class="content container overlay-content">
                <h2><i class="icon-attention-1"></i> Oups !</h2>
                <pre class="alert alert-danger">{{ error }}</pre>
                <p class="text-danger">
                    Si ce message ne vous aide pas, transmettez le à l'administrateur Oscar.
                </p>
                <nav class="buttons">
                    <button class="btn btn-primary" @click="error = ''">Fermer</button>
                </nav>
            </div>
        </div>

        <div class="overlay" v-if="rejectPeriod"  style="z-index: 2002">
            <div class="content container overlay-content">
                <h2><i class="icon-attention-1"></i> Déclaration rejetée !</h2>

                <div v-if="rejectPeriod.rejectadmin_at">
                    <p>Déclaration rejetée administrativement par <strong>{{ rejectPeriod.rejectadmin_by }}</strong>
                        le <time>{{ rejectPeriod.rejectadmin_at }}</time>
                    </p>
                    <pre><strong>Motif : </strong>{{ rejectPeriod.rejectadmin_message }}</pre>
                </div>
                <div v-else-if="rejectPeriod.rejectsci_at">
                    <p>Déclaration rejetée scientifiquement par <strong>{{ rejectPeriod.rejectsci_by }}</strong>
                        le <time>{{ rejectPeriod.rejectsci_at }}</time>
                    </p>
                    <pre><strong>Motif : </strong>{{ rejectPeriod.rejectsci_message }}</pre>
                </div>
                <div v-else-if="rejectPeriod.rejectactivity_at">
                    <p>Déclaration rejetée par <strong>{{ rejectPeriod.rejectactivity_by }}</strong>
                        le <time>{{ rejectPeriod.rejectactivity_at }}</time>
                    </p>
                    <pre><strong>Motif : </strong>{{ rejectPeriod.rejectactivity_message }}</pre>
                </div>

                <nav class="buttons">
                    <button class="btn btn-primary" @click="rejectPeriod = null">Fermer</button>
                </nav>
            </div>
        </div>



        <div class="overlay" v-if="popup"  style="z-index: 2001">
            <div class="content container overlay-content">
                <h2>Historique</h2>
                <pre class="alert alert-info">{{ popup }}</pre>
                <nav class="buttons">
                    <button class="btn btn-primary" @click="popup = ''">Fermer</button>
                </nav>
            </div>
        </div>

        <div class="overlay" v-if="help"  style="z-index: 2002">
            <div class="content container overlay-content">
                <h2><i class="icon-help-circled"></i> Informations légales</h2>
                <p>
                    Dans le cadre des projets soumis aux feuilles de temps, l'organisme financeur impose la justification des heures,
                    <strong>incluant les activités hors-projets</strong>.
                    Le culum des heures déclarées doit respecter le cadre légale : <br>
                </p>
                <ul v-if="ts">
                    <li>Durée <em>normal</em> d'une journée : <strong>{{ ts.daylength | duration }}</strong></li>
                    <li>Durée <strong>maximum légale</strong> d'une journée : <strong>{{ ts.dayExcess | duration }}</strong></li>
                    <li>Durée <strong>maximum légale</strong> d'une semaine : <strong>{{ ts.weekExcess | duration }}</strong></li>
                    <li>Durée <strong>maximum légale</strong> d'un mois : <strong>{{ ts.monthExcess | duration }}</strong></li>
                </ul>
                <p>
                    Selon les modalités de financement, les dépacements (même en éxcédent) peuvent être considérés comme des <em>irrégularité</em> pouvant déclencher la suspension ou le remboursement des financements engagés ou à venir.
                </p>
                <nav class="buttons">
                    <button class="btn btn-primary" @click="help = ''">Fermer</button>
                </nav>
            </div>
        </div>

        <div class="overlay" v-if="debug"  style="z-index: 2002">
            <div class="content container overlay-content">
                <h2><i class="icon-bug"></i> Debug</h2>
                <pre class="alert alert-info" style="white-space: pre; font-size: 12px">{{ debug }}</pre>
                <nav class="buttons">
                    <button class="btn btn-primary" @click="debug = ''">Fermer</button>
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

                <div v-if="selectionWP.validation_up != true" class="alert alert-danger">
                    Vous ne pouvez plus ajouter de créneaux pour ce lot sur cette période
                </div>
                <div v-else>
                    <p>
                        <i class="icon-calendar"></i>
                        Journée : <strong>{{ selectedDay.date | datefull }}</strong><br/>
                    </p>

                    <div class="row">

                        <div class="col-md-6">
                            <h4>Temps</h4>
                            <timechooser @timeupdate="handlerDayUpdated" :baseTime="ts.daylength" :fill="fillDayValue" :duration="editedTimesheet ? editedTimesheet.duration : 0"></timechooser>
                        </div>
                        <div class="col-md-6">
                            <h4>Commentaire</h4>
                            <textarea class="form-control textarea" v-model="commentaire"></textarea>
                        </div>

                    </div>
                </div>



                <nav class="buttons">
                    <button class="btn btn-default" @click="selectionWP = null">
                        <i class="icon-block"></i>
                        Annuler</button>
                    <button class="btn btn-primary" @click="handlerSaveMenuTime" v-if="selectionWP.validation_up == true">
                        <i class="icon-floppy"></i>
                        <i class="icon-floppy"></i>
                        Valider</button>
                </nav>
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

                <!--
                <div class="help cursor-pointer" @click="help=true">
                    <i class="icon-help-circled"></i> Informations légales sur les déclarations soumises aux feuilles de temps
                </div>
                -->

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
                                    <strong :class="(week.total > week.weekExcess)?'has-titled-error':''"
                                            :title="(week.total > week.weekExcess)?
                                                'Les heures excédentaires risques d\'être ignorées lors d\'une justification financière dans le cadre des projets soumis aux feuilles de temps'
                                                :''">
                                        <i class="icon-attention-1" v-if="week.total > week.weekExcess"></i>{{ week.total | duration2(week.weekLength) }}</strong>

                                </small>
                            </header>
                            <div class="days">
                                <timesheetmonthday v-for="day in week.days"
                                       :class="selectedDay == day ? 'selected':''"
                                       :others="ts.otherWP"
                                       @selectDay="handlerSelectData(day)"
                                       @daymenu="handlerDayMenu"
                                       @debug="debug = $event"
                                       :day="day"
                                       :key="day.date"/>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <section class="col-lg-4">

                <!-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% VUE DETAILS JOUR %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% -->
                <timesheetmonthdaydetails  v-if="selectedDay"
                                           :day="selectedDay"
                                           :workPackages="ts.workPackages"
                                           :others="ts.otherWP"
                                           :selection="selectionWP"
                                           :label="dayLabel"
                                           :day-excess="ts.dayExcess"
                                           @debug="debug = $event"
                                           @cancel="selectedDay = null"
                                           @removetimesheet="deleteTimesheet"
                                           @edittimesheet="editTimesheet"
                                           @addtowp="handlerWpFromDetails($event)"
                />

                    <!-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% VUE DETAILS SEMAINE %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% -->
                    <div v-else-if="selectedWeek">
                        <h3 @click.shift="debug = selectedWeek">
                            <i class="icon-calendar"></i>
                            Détails de la
                            <strong>semaine {{ selectedWeek.label }}</strong>
                        </h3>

                        <a class="link" @click="selectedWeek = null">
                            <i class="icon-angle-left"></i> Revenir au mois
                        </a>

                        <h4>Détails : </h4>
                        <article class="card xs total repport-item" :class="{ 'locked': d.locked, 'closed': d.closed, 'excess': d.duration > ts.dayExcess }"
                                 v-for="d in selectedWeek.days"
                                 @click="handlerSelectData(d)">

                            <div class="week-header" :class="{ 'text-thin' : d.closed || d.locked }">
                                <span class="" >
                                    <i class="icon-minus-circled" v-if="d.closed"></i>
                                    <i class="icon-lock" v-else-if="d.locked"></i>
                                    <i class="icon-calendar" v-else></i>
                                    {{ d.data | datefull }}

                                    <i class="icon-attention-circled" style="color: red" title="Les heures déclarées dépassent la limite légales"></i>
                                </span>
                                <small>
                                    <strong class="text-large">{{ d.duration | duration2(d.dayLength) }}</strong>
                                    <!-- <span class="heure-total">{{ d.dayLength | duration }}</span>-->
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
                                    <strong>{{ selectedWeek.total | duration2(selectedWeek.weekLength) }}</strong>
                                </small>
                            </div>
                        </article>

                        <div class="alert alert-danger" v-if="selectedWeek.total > ts.weekExcess">
                            <i class="icon-attention-1"></i>
                            Vos déclarations pour cette semaine dépasse la limite légale fixée à <strong>{{ ts.weekExcess | duration }}</strong> heures.
                        </div>

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
                                <strong>{{ (selectedWeek.totalOpen - selectedWeek.total) | duration }} heure(s)</strong>
                                avec une des activités ci-dessous :
                            </p>
                            <wpselector :others="ts.otherWP" :workpackages="ts.workPackages" :selection="fillSelectedWP" @select="fillSelectedWP = $event"></wpselector>
                            <button class="btn btn-default" @click="fillWeek(selectedWeek, fillSelectedWP)" :class="fillSelectedWP ? 'btn-primary' : 'disabled'">
                                <i class="icon-floppy"></i>
                                Valider
                            </button>
                        </section>
                    </div>

                    <!-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% VUE DETAILS MOIS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% -->
                    <div v-else>
                        <h3 @click.prevent.shift.stop="debug = ts">
                            <i class="icon-calendar"></i>
                            Mois de <strong>{{ mois }}</strong>
                        </h3>

                        <section v-for="week in weeks" v-if="ts" class="card xs" >
                            <header class="week-header" @click="selectWeek(week)">
                                <span>
                                    Semaine {{ week.label }}
                                    <i class="icon-ok-circled" style="color: #999" v-if="week.total < week.weekLength"></i>
                                    <i class="icon-attention-circled" style="color: #993d00" v-else-if="week.total > week.weekExcess" title="La déclaration est incomplète pour cette période"></i>
                                    <i class="icon-ok-circled" style="color: #5c9ccc" v-else></i>
                                </span>
                                <small>
                                    <strong :class="(week.total > week.weekExcess)?'has-titled-error':''"
                                            :title="(week.total > week.weekExcess)?
                                                'Les décalarations dépassent la limite légales et risques d\'être ignorées lors d\'une justification financière dans le cadre des projets soumis aux feuilles de temps'
                                                :''">
                                        <i class="icon-attention-1" v-if="week.total > week.weekExcess"></i>{{ week.total | duration2(week.weekLength) }}</strong>
                                </small>
                            </header>
                        </section>

                        <section class="card xs total interaction-off">
                            <div class="week-header">
                                <span class="text-big text-xxl">Total</span>
                                <small>
                                    <strong class="text-large">{{ ts.total | duration2(ts.periodLength) }}</strong>
                                </small>
                            </div>
                        </section>

                        <div class="alert alert-danger" v-if="ts.total > ts.monthExcess">
                            <i class="icon-attention-circled"></i>
                            Les heures mensuelles dépassent le cadre légale fixé à <strong>{{ ts.monthExcess | duration }}</strong> heures.
                        </div>

                        <hr>

                        <h4><i class="icon-cubes"></i> Activités pour cette période</h4>
                        <p class="alert alert-info" v-if="ts.activities.length == 0">
                            Vous n'être identifié comme déclarant sur aucune activité pour cette période. Si cette situation vous semble anormale, prenez contact avec votre responsable scientifique.
                        </p>
                        <section class="card xs" v-for="a in ts.activities" @click="debug = a" v-else>
                            <div class="week-header interaction-off">

                                <span>
                                    <strong>{{ a.acronym }}</strong>
                                    <i v-if="a.validation_state == null"></i>
                                    <i class="icon-cube" v-else-if="a.validation_state.status == 'send-prj'" title="Validation projet en attente"></i>
                                    <i class="icon-beaker" v-else-if="a.validation_state.status == 'send-sci'" title="Validation scientifique en attente"></i>
                                    <i class="icon-hammer" v-else-if="a.validation_state.status == 'send-adm'" title="Validation administrative en attente"></i>
                                    <i class="icon-minus-circled" v-else-if="a.validation_state.status == 'conflict'" title="Il y'a un problème dans la déclaration"></i>
                                    <i class="icon-ok-circled" v-else-if="a.validation_state.status == 'valid'" title="Cette déclaration est valide"></i>
                                    <br>
                                    <em class="text-thin">{{ a.label }}</em>

                                </span>
                                <small>
                                    <strong class="text-large">{{ a.total | duration2(ts.periodLength) }}</strong>
                                </small>
                            </div>
                        </section>
                        <section class="card xs total interaction-off">
                            <div class="week-header">
                                <span>
                                    <strong class="text-big text-xxl">Total</strong><br>
                                    <small>Pour les activités soumisses aux déclarations</small>
                                </span>
                                <small>
                                    <strong class="text-large">{{ ts.periodDeclarations | duration2(ts.periodLength) }}</strong>
                                </small>
                            </div>
                        </section>


                        <div v-if="ts.periodsValidations.length">
                            <h3>Procédures de validation pour cette période</h3>
                            <section v-for="periodValidation in ts.periodsValidations" class="card card-xs">
                                <i v-if="periodValidation.status == 'valid'" class="icon-ok-circled"></i>
                                <i v-else-if="periodValidation.status == 'conflict'" class="icon-minus-circled"></i>
                                <i v-else class="icon-history"></i>
                                {{ periodValidation.label }}

                                <a href="#" @click="popup = periodValidation.log">Historique</a>
                                <a href="#" @click="rejectPeriod = periodValidation" v-if="periodValidation.status == 'conflict'">Détails sur le rejet</a>
                                <a href="#" @click="reSendPeriod(periodValidation)" v-if="periodValidation.status == 'conflict'">Réenvoyer</a>
                            </section>
                        </div>

                        <nav class="buttons-bar">
                            <button class="btn btn-primary" style="margin-left: auto" v-if="ts.submitable"
                                    :class="{ 'disabled': !ts.submitable, 'enabled': ts.submitable }"
                                    @click="sendMonth()">
                                <i class="icon-upload"></i>
                                <i class="icon-spinner animate-spin" v-show="loading"></i>
                                <span>
                                    Soumettre mes déclarations
                                </span>
                            </button>
                            <span v-else>
                            Vous ne pouvez pas soumettre cette période<br>
                                <small>{{ ts.submitableInfos }}</small>
                            </span>
                        </nav>
                    </div>
            </section>
        </section>
    </section>
</template>

<style lang="scss">
    .repport-item {
        .icon-attention-circled {
            display: none;
        }
        &.excess {
            color: #990000;
            border-left: 4px solid red;
            .icon-attention-circled {
                display: inline-block;
            }
        }
    }




    .loading-message {
        background: white;
        font-size: 1em;
        position: fixed;
        z-index: 10000;
        bottom: 0;
        right: 0;
        padding: .3em 1em;
        border-radius: 8px 0 0 0 ;
    }

    .interaction-off {
        cursor: default;
        pointer-events: none;
    }

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
    import AjaxResolve from "./AjaxResolve";


    let defaultDate = new Date();
    let moment = function(){};

    export default {
        name: 'TimesheetMonth',

        props: {
            moment: {
                required: true
            },
            bootbox: {
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
                debug: null,
                help: false,
                popup: "",

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

                rejectPeriod: null,

                selectedDay: null,
                dayMenuLeft: 50,
                dayMenuTop: 50,
                dayMenu: 'none',
                selectedWP: null,
                selectionWP: null,
                selectedTime: null,
                dayMenuSelected: null,
                dayMenuTime: 0.0,
                editedTimesheet: null
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
                        editable: this.ts.editable,
                        drafts: 0,
                        weekExcess: this.ts.weekExcess
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
                                drafts: 0,
                                weekExcess: this.ts.weekExcess
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

            editTimesheet( timesheet, day ){

              this.editedTimesheet = timesheet;
              this.commentaire = timesheet.comment;
              this.selectedDay = day;
              this.dayMenuTime = timesheet.duration;

              if( timesheet.wp_id ){
                  this.selectionWP = this.getWorkpackageById(timesheet.wp_id);
              }
            },

            getWorkpackageById( id ){
                return this.ts.workPackages[id];
            },

            reSendPeriod(periodValidation){
                console.log(periodValidation.status);
                if( periodValidation.status != 'conflict' ){
                    this.error = 'Vous ne pouvez pas soumettre cette déclaration, status incorrect';
                    return;
                }

                this.bootbox.confirm('Réenvoyer la déclaration ?', ok => {
                    if( ok ){
                        // Données à envoyer
                        var datas = new FormData();
                        datas.append('action', 'resend');
                        datas.append('period_id', periodValidation.id);

                        this.loading = true;

                        this.$http.post('', datas).then(
                            ok => {
                                this.fetch();
                            },
                            ko => {
                                this.error = AjaxResolve.resolve('Impossible d\'envoyer la période',  ko);
                            }
                        ).then(foo => {
                            this.selectedWeek = null;
                            this.loading = false;
                        });
                    }
                })
            },

            sendMonth(){

                if( this.ts.submitable == undefined || this.ts.submitable != true ){
                    this.error = 'Vous ne pouvez pas soumettre vos déclarations pour cette période : ' + this.ts.submitableInfos;
                    return;
                }

                this.bootbox.confirm('Soumettre vos déclarations pour cette période ?', ok => {
                   if( ok ){
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
                               this.error = AjaxResolve.resolve('Impossible d\'envoyer la période',  ko);
                           }
                       ).then(foo => {
                           this.selectedWeek = null;
                           this.loading = false;
                       });
                   }
                });

            },

            fillMonth(){
                // TODO Remplissage automatique du mois
            },

            fillWeek(week, wp){
                let data = [];

                week.days.forEach( d => {
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

            handlerSaveMenuTime(){
                let data = [{
                    'id': this.editedTimesheet ? this.editedTimesheet.id : null,
                    'day': this.selectedDay.date,
                    'wpId': this.selectionWP.id,
                    'duration': this.dayMenuTime * 60,
                    'comment' : this.commentaire,
                    'code': this.selectionWP.code
                }];

                this.performAddDays(data);
            },

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // TRAITEMENT DES CRENEAUX

            /**
             * Déclenchement de l'envoi des créneaux à l'API.
             */
            performAddDays(datas){
                let formData = new FormData();
                formData.append('timesheets', JSON.stringify(datas));
                this.loading = "Enregistrement des créneaux";

                this.$http.post('/feuille-de-temps/declarant-api', formData).then(
                    ok => {
                        this.fetch(false);
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible d\'enregistrer les créneaux',  ko);
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                    this.selectionWP = null;
                    this.loading = false;
                    this.editedTimesheet = null;
                });;
            },

            performDelete( ids ){
                this.loading = "Suppression des créneaux";
                this.$http.delete('?id=' +ids.join(',')).then(
                    ok => {
                        this.fetch(false);
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de supprimer le créneau',  ko);
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                    this.loading = false;
                });
            },

            handlerDayUpdated(){
              let t = arguments[0];
              this.dayMenuTime = (t.h + t.m);
            },

            handlerSelectWP(w){
                this.selectedWP = w;
                this.selectionWP = w;
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
               this.handlerSelectWP(wp);
            },

            handlerDayMenu(event, day){
                this.dayMenuLeft = event.clientX;
                this.dayMenuTop = event.clientY;
                this.dayMenu = 'block';
                this.selectedDay = day;
            },

            handlerSelectData(day){
                this.selectedDay = day;
            },

            /**
             * Chargement du mois suivant
             */
            nextYear(){
                this.year +=1;
                this.fetch(true);
            },

            /**
             * Chargement du mois suivant
             */
            nextMonth(){
                this.month +=1;
                if( this.month > 12 ){
                    this.month = 1;
                    this.nextYear();
                } else {
                    this.fetch(true);
                }
            },

            /**
             * Charement de l'année précédente.
             */
            prevYear(){
                this.year -=1;
                this.fetch(true);
            },

            prevMonth(){
                this.month -=1;
                if( this.month < 1 ){
                    this.month = 12;
                    this.prevYear();
                } else {
                    this.fetch(true);
                }
            },
            fetch(clear = true){

                this.loading = "Chargement de la période";

                if( clear ){
                    this.selectedDay = null;
                    this.selectedWeek = null;
                }

                let daySelected;

                if( this.selectedDay )
                    daySelected = this.selectedDay.i;


                this.$http.get('?month=' +this.month +'&year=' + this.year).then(
                    ok => {
                        this.dayLength = ok.body.dayLength;
                        this.ts = ok.body
                        if( daySelected ){
                           this.selectedDay = this.ts.days[daySelected];
                        }
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de charger cette période', ko);
                    }
                ).then( foo => {
                    this.loading = false
                });
            }
        },
        mounted(){
            moment = this.moment;
            this.month = this.defaultMonth;
            this.year = this.defaultYear;
            this.dayLength = this.defaultDayLength;

            this.fetch(true)
        }
    }
</script>