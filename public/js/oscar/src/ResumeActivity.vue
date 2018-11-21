<template>
    <div>
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
                        <div v-for="person in month.persons" class="line line-datas">
                            <strong class="line-label">
                                <i class="icon-user"></i>
                                {{ person.displayname }}
                                <small>
                                    <i :class="'icon-' +person.state"></i>
                                    {{ person.state | state }}
                                </small>
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

                    <div class="line line-total" >
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
        font-size: 1.2em;
        border-bottom: solid #999 1px;
        .line-label {
            font-size: 1.2em;
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
    .line {
        display: flex;
        >* {
            flex: 1 0 0;
            padding: 2px 4px;
        }

        .duration {
            color: #0b58a2;
            &.nothing {
                color: #8f97a0;
            }
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
    .line-label {
        padding-left: 1em;
    }
    .line-datas {
        &:nth-child(even){
            background: rgba(0,0,0,.05);
        }
        &:hover {
            background: rgba(#0b58a2, .25);
        }
        >* {
            border-right: solid #CCC 1px;
        }
    }
</style>
<script>
    // poi watch --format umd --moduleName  ResumeActivity --filename.css ResumeActivity.css --filename.js ResumeActivity.js --dist public/js/oscar/dist public/js/oscar/src/ResumeActivity.vue

    const states = {
        'none' : 'Non-envoyé',
        'send-prj' : 'Validation projet',
        'send-sci' : 'Validation scientifique',
        'send-adm' : 'Validation administrative',
        'conflict' : 'Conflit',
        'valid' : 'Validée'
    }

    export default {

        data(){
            return {
                datas: null
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
                        totalWps: {},
                        persons: {},
                        total: 0.0
                    };

                    if( period.total > 0 ){
                        Object.keys(this.datas.workspackages).forEach( wp => {
                            month.totalWps[wp] = 0.0;
                        });
                        Object.keys(period.persons).forEach( personId => {
                            let personDt = period.persons[personId];
                            if( personDt.total > 0 ) {
                                console.log(JSON.stringify(personDt));
                                let person = {
                                    displayname: personDt.displayname,
                                    state: personDt.validation_state,
                                    total: 0.0,
                                    totalWps: {}
                                };
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