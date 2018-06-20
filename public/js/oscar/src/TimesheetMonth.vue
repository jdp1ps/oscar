<template>
    <section>
        <h1>Feuille de temps mensuelle</h1>
        <section v-if="ts">
            <h2>Déclarations de temps pour <strong>{{ ts.person }}</strong></h2>
            <h3>Période :
                <a href="#" @click.prevent="prevMonth"><i class="icon-angle-left"/></a>
                <strong>{{ mois }}</strong>
                <a href="#" @click.prevent="nextMonth"><i class="icon-angle-right"/></a>
            </h3>

            {{ dayBeforeMonth }}


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
                            <article class="day" v-for="day in week" :class="{ 'locked': day.locked }">
                                {{ day.date }} ({{ day.day }})

                                <nav v-if="!day.locked">
                                    <i class="icon-doc-add"></i>
                                    Déclarer
                                </nav>
                            </article>
                        </div>
                    </section>
                </div>
            </div>

        </section>


    </section>
</template>

<style lang="scss">
    .month-header {
        display: flex;
        strong {
            display: block;
            text-align: center;
            flex: 0 0  14.285714286%;
        }
    }
    .days {
        display: flex;
        height: 75px;
        .day {
            background: rgba(#ff6600, .5);
            flex: 0 0 14.285714286%;
            cursor: pointer;

            &:hover {
                background: white;
            }


            &.locked {
                cursor: not-allowed;
                background: #0d3349;
            }
        }
    }

    .week:first-child .days {
        background: green;
        justify-content: flex-end;
    }
    .week:last-child .days {
        background: orange;
        align-items: flex-start;
        justify-content: flex-start;
    }
</style>

<script>
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

        data(){
            return {
                ts: null,
                month: null,
                year: null
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
            console.log(this.month, this.year);
            this.fetch()
        }
    }
</script>