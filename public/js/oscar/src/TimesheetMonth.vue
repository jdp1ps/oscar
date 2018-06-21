<template>
    <section>

        <h1>
            <span v-if="selectedDay">
                <i class="icon-angle-left interactive-icon-big" @click="selectedDay = null"></i>
                {{ selectedDay.date | date }}
            </span>
            <span v-else>Feuille de temps mensuelle</span>
        </h1>

        <div v-show="!selectedDay">
            <section v-if="ts" >

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
                            <section v-for="week in weeks" v-if="ts" class="week">
                                <div class="days">
                                    <timesheetmonthday v-for="day in week"
                                                       @selectDay="handlerSelectData"
                                                       :day="day"
                                                       :key="day.date"/>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
                <section class="col-lg-4">
                    <h3>
                        <i class="icon-archive"></i>
                        Lot de travail</h3>
                    <p class="help">Ne sont proposés que les lots de travail <strong>disponibles sur la période</strong>.</p>
                    <article class="card xs wp" v-for="wp in ts.workPackages">
                        <h3 class="">
                            <small>
                                <i class="icon-cubes"></i>[{{wp.acronym}}]
                                <i class="icon-cube"></i>{{ wp.activity }}
                            </small><br/>
                            <abbr title="">{{ wp.code}}</abbr> {{ wp.label }}
                        </h3>
                        <div class="card-content">
                            <i class="icon-calendar"></i> Du <strong>{{ wp.from | date}}</strong> au <strong>{{ wp.to|date }}</strong><br />
                            <i class="icon-clock"></i> Heures prévues : {{ wp.hours }}
                        </div>
                    </article>
                </section>
            </section>

        </div>

        <timesheetmonthdaydetails v-if="selectedDay" :day="selectedDay" :workPackages="ts.workPackages"/>

    </section>
</template>

<style lang="scss">
    .interactive-icon-big {
        font-size: 32px;
        cursor: pointer;
    }

    article.wp {
        font-size: 1.2em;
        h3 {
            font-size: 1.2em;
            margin: 0;
            padding: 0;
        }
    }

    .month-header {
        display: flex;
        height: 50px;
        line-height: 45px;
        justify-content: center;
        justify-items: center;
        strong {
            display: block;
            text-align: center;
            flex: 0 0  14.285714286%;
            background: #efefef;
            color: #5c9ccc;
            border-left: solid thin #fff;
        }
    }
    .periode strong {
        display: inline-block;
        width: 10em;
        text-align: center;
    }
    .days {
        display: flex;
        height: 75px;
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
                white-space: nowrap;
            }

            position: relative;
            background: rgba(#ffffff, .25);
            border: thin solid white;
            flex: 0 0 14.285714286%;
            cursor: pointer;

            &:hover {
                background: white;
            }


            &.locked {
                cursor: not-allowed;
                background: #eee;
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

    import TimesheetMonthDay from './TimesheetMonthDay.vue';
    import TimesheetMonthDayDetails from './TimesheetMonthDayDetails.vue';

    let defaultDate = new Date();
    let moment = function(){};

    export default {
        props: {
            moment: {
                required: true
            },
            defaultMonth: { default: defaultDate.getMonth()+1},
            defaultYear: { default: defaultDate.getFullYear()}
        },

        components: {
            timesheetmonthday: TimesheetMonthDay,
            timesheetmonthdaydetails: TimesheetMonthDayDetails
        },

        data(){
            return {
                ts: null,
                month: null,
                year: null,
                selectedDay: null
            }
        },

        filters: {
            date(value, format="ddd DD MMMM  YYYY"){
                var m = moment(value);
                return m.format(format);
            },
            day(value, format="ddd DD"){
                var m = moment(value);
                return m.format(format);
            }
        },

        computed: {
            mois(){
                return moment(this.ts.from).format('MMMM YYYY');
            },

            dayBeforeMonth(){
                let d = 0;
                let firstDay = this.ts.days[1].day;
                return firstDay-1;
            },

            weeks(){
                let weeks = [];
                if( this.ts && this.ts.days ){

                    let firstDay = this.ts.days[1];
                    let currentWeekNum = firstDay.week;
                    let currentWWeek = [];

                    for( var d in this.ts.days ){
                        let currentDay = this.ts.days[d];
                        if( currentWeekNum != currentDay.week ){
                            weeks.push(currentWWeek);
                            currentWWeek = [];
                        }
                        currentWeekNum = currentDay.week;
                        currentWWeek.push(currentDay);
                    }
                    if( currentWWeek.length )
                        weeks.push(currentWWeek);
                }
                return weeks;
            }
        },

        methods: {
            handlerSelectData(day){
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
                console.log("Chanrgement des données");
                this.$http.get('?month=' +this.month +'&year=' + this.year).then(
                    ok => {
                        console.log(ok);
                        this.ts = ok.body
                    },
                    ko => {

                    }
                )
            }
        },
        mounted(){
            moment = this.moment;
            this.month = this.defaultMonth;
            this.year = this.defaultYear;
            this.fetch()
        }
    }
</script>