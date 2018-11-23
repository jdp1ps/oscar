<template>
    <div>
        <transition name="fade">
        <div class="overlay" v-if="details">
            <div class="overlay-content">

                <a href="#" @click.prevent="details = null" class="overlay-closer">
                    <i class="icon-cancel-outline"></i>
                </a>

                <h1>
                    <i class="icon-cube"></i>
                    <strong>{{ datas.acronym }} <br><small>{{ datas.label }}</small></strong></h1>

                <h2>
                    Déclaration pour
                    {{ details.month.period | period }} - <strong>{{ details.person.displayname }}</strong>
                </h2>

                <div class="alert alert-info">
                    État de la déclaration : <strong>
                        <i :class="'icon-' +details.person.state"></i>
                        {{ details.person.state | state }}
                    </strong>
                </div>

                <table class="table-bordered table details">
                    <thead style="font-size: .7em">
                        <tr>
                            <th>&nbsp;</th>
                            <th v-for="day, date in details.month.days">
                                <small>{{ day }}</small>
                                <strong>{{ date }}</strong>
                            </th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="wp in datas.workspackages">
                            <th>{{ wp.code }}</th>
                            <td v-for="day, date in details.month.days" class="duration">
                                <strong v-if="details.person.details[wp.id] && details.person.details[wp.id][date]">
                                    {{ details.person.details[wp.id][date] | duration2 }}
                                </strong>
                                <small v-else>
                                    -
                                </small>
                            </td>
                            <td>
                                <strong v-if="details.person.totalWps[wp.id]" class="duration total">{{ details.person.totalWps[wp.id] | duration2}}</strong>
                                <small v-else>0.0</small>
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>&nbsp;</th>
                            <td v-for="day, date in details.month.days" class="duration total">
                                <strong v-if="details.person.days[date]">
                                    {{ details.person.days[date] | duration2}}
                                </strong>
                                <small v-else class="nothing">
                                    0.0
                                </small>
                            </td>
                            <td class="total duration">
                                <strong>{{ details.person.total | duration2}}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        </transition>
        <template v-if="datas">

            <div class="year" v-for="year, yearLabel in grouped">
                <h1>{{ yearLabel }}</h1>
                <section v-for="month, monthLabel in year.months" class="month card">
                    <div class="line line-heading">
                        <strong class="line-label">{{ monthLabel | period}}</strong>
                        <span v-for="wpmonth, wpid in month.totalWps">{{ datas.workspackages[wpid].code }}</span>
                        <span class="total duration">&nbsp;</span>
                    </div>

                    <template v-if="month.total > 0">
                        <div v-for="person in month.persons" class="line line-datas" @click.prevent="displayDetails(month, person)" :class="person.state">
                            <strong class="line-label">
                                <i class="icon-user"></i>
                                {{ person.displayname }}
                                <small class="state" :class="'state-' + person.state">
                                    <i :class="'icon-' +person.state"></i>
                                    {{ person.state | state }}
                                </small>

                                <a @click.stop.prevent="handlerTimesheetDL(person)" v-if="person.state == 'valid'">
                                    <i class="icon-file-excel"></i>
                                    Télécharger la feuille de temps
                                </a>

                            </strong>
                            <span v-for="wp in person.totalWps" class="duration" :class="{ 'nothing': wp == 0 }">
                                {{ wp | duration}}
                            </span>
                            <span class="duration total">
                                <strong v-if="person.total > 0">{{ person.total | duration}}</strong>
                                <small v-else>0.0</small>
                            </span>
                        </div>
                    </template>

                    <div class="line line-total" v-if="month.total > 0">
                        <strong class="line-label">Total</strong>
                        <span v-for="wpmonth in month.totalWps" class="duration">
                            <strong>{{ wpmonth | duration}}</strong>
                        </span>
                        <span class="total duration">{{ month.total | duration}}</span>
                    </div>
                </section>
            </div>
        </template>
    </div>
</template>
<style lang="scss">

    .line-heading {
        border-bottom: solid #999 1px;
        .line-label {
            >strong { padding-left: .25em };
            text-transform: capitalize;
            font-weight: 100;
            text-align: left;
        }
        text-align: right;
    }

    .line-total {
        border-top: solid #CCC 1px;
        // font-size: 1.4em;
        >* {
            border-right: solid #CCC 1px;
        }
    }

    .details {
        max-width: 100%;
//        font-size: .9em;
        td, th {
            padding: 2px !important;
        }
        thead th {
            text-align: center;
            font-weight: 100;
            small { display: block }
            strong { font-size: 1.25em }
        }
        tbody {
            text-align: right;
            tr {
                //font-size: .9em;
            }
            tr:nth-child(even){
                background: rgba(0,0,0,.05);
            }
        }
    }

    .duration {
        color: #0b58a2;
        text-align: right;
    }
    .nothing {
        color: #8f97a0;
    }

    .line {
        display: flex;
        >* {
            flex: 1 0 0;
            padding: 2px;
        }

        small {
            text-align: center;
            color: rgba(0,0,0,.25)
        }

        .line-label {
            flex: 3 0 0;
        }
        .empty-period {
            flex: 10 1 0;
            text-align: center;
        }
        .duration {
            text-align: right;
        }
    }

    .line-datas {
        &:nth-child(even){ background: rgba(0,0,0,.05); }
        &:hover { background: rgba(#0b58a2, .25); }
        border-left: 8px #5c646c solid;

        &.send-prj { border-left-color: #5c9ccc; }
        &.send-sci { border-left-color: #5c9ccc; }
        &.send-adm { border-left-color: #5c9ccc; }
        &.valid { border-left-color: #3a8104; }
        &.conflic { border-left-color: #dd1144; }

        >* { border-right: solid #CCC 1px; }
    }
</style>
<script>
    // poi watch --format umd --moduleName  ResumeActivity --filename.css ResumeActivity.css --filename.js ResumeActivity.js --dist public/js/oscar/dist public/js/oscar/src/ResumeActivity.vue

    const states = {
        'none' : 'Non-envoyée',
        'send-prj' : 'Validation projet',
        'send-sci' : 'Validation scientifique',
        'send-adm' : 'Validation administrative',
        'conflict' : 'Conflit',
        'valid' : 'Validée'
    }

    export default {

        data(){
            return {
                datas: null,
                details: null
            }
        },

        filters: {
            state(s){
                return states[s];
            }
        },

        computed: {
            grouped(){
                let output = {};
                Object.keys(this.datas.periods).forEach(key => {
                    let split = key.split('-'),
                        year = split[0],
                        period = this.datas.periods[key];

                    // ANNEE
                    if( !output.hasOwnProperty(year) ){
                        output[year] = {
                            'year': year,
                            'total' : 0.0,
                            'totalWps': {},
                            'months' : {}
                        };

                        Object.keys(this.datas.workspackages).forEach( wp => {
                            output[year].totalWps[wp] = 0.0;
                        });
                    }

                    // MOIS
                    let month = {
                        period: key,
                        total: 0.0,
                        totalWps: {},
                        days: period.days,
                        persons: {},
                    };

                    if( period.total > 0 ){
                        Object.keys(this.datas.workspackages).forEach( wp => {
                            month.totalWps[wp] = 0.0;
                        });
                        Object.keys(period.persons).forEach( personId => {
                            let personDt = period.persons[personId];
                            if( personDt.total > 0 ) {
                                let person = {
                                    id: personId,
                                    displayname: personDt.displayname,
                                    period: key,
                                    state: personDt.validation_state,
                                    total: 0.0,
                                    totalWps: {},
                                    details: personDt.details
                                };

                                let detailsByDays = {};
                                Object.keys(personDt.details).forEach( wp => {
                                    Object.keys(personDt.details[wp]).forEach( date => {
                                        if( !detailsByDays.hasOwnProperty(date) ){
                                            detailsByDays[date] = 0.0;
                                        }
                                        detailsByDays[date] += personDt.details[wp][date];
                                    });
                                });

                                person.days = detailsByDays;

                                Object.keys(personDt.workpackages).forEach(wpId => {
                                    let wpTotal = personDt.workpackages[wpId];
                                    output[year].total += wpTotal;
                                    output[year].totalWps[wpId] += wpTotal;

                                    person.totalWps[wpId] = wpTotal;
                                    month.totalWps[wpId] += wpTotal;
                                    person.total += wpTotal;
                                    month.total += wpTotal;

                                })
                                month.persons[personId] = person;
                            }
                        })
                    }
                    output[year].months[key] = month;
                });
                return output;
            }
        },

        methods: {
            handlerTimesheetDL(person){
              document.location = '/feuille-de-temps/excel?action=export&period=' + person.period +'&personid=' +person.id;
            },
            displayDetails( month, person){
              this.details = {
                  month, person
              }
            },
            fetch(){
                this.$http.get('').then(
                    ok => {
                        this.datas = ok.body
                    },
                    fail => {
                        console.log(fail)
                    }
                )
            }
        },

        mounted(){
            this.fetch();
        }
    }
</script>