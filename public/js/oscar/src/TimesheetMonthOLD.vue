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

            <article>
                <section class="projets">

                    <article class="projet" v-for="p, acronym in ts.projects">

                        <article class="activity" v-for="a, activityCode in p.activities">
                            <header class="activity-header">
                                <h3 class="activity-title">
                                    <strong class="project-acronym">
                                        <i class="icon-cubes"></i>
                                        {{ acronym }}</strong>
                                    <span class="activity-label">
                                        <i class="icon-cube"></i>
                                        <i>{{ activityCode }}</i> {{ a.label }}
                                    </span>
                                </h3>
                                <small>
                                    <i class="icon-calendar"></i>
                                    du
                                    <time>{{ a.dateStart | date }}</time>
                                    au
                                    <time>{{ a.dateEnd | date }}</time>
                                </small>
                            </header>
                            <section class="wps">
                                <div class="days wp">
                                    <span>Lots</span>
                                    <span v-for="d in ts.days" class="day day-header" :class="{ 'weekend': d.weekend, 'closed': d.closed, 'samedi': d.samedi }">{{ d.data | day }}</span>
                                    <span>Total</span>
                                </div>
                                <article class="wp" v-for="wp, wpCode in a.wps">
                                    <h4>{{ wpCode }}</h4>
                                    <span v-for="heures, day in wp.times" class="day day-data">
                                        <strong v-if="heures">{{ heures }}</strong>
                                        <em v-else>0.0</em>
                                    </span>
                                </article>
                            </section>
                        </article>
                    </article>
                </section>

                <!--
                <div class="line">

                </div>
                -->
            </article>
        </section>


    </section>
</template>

<style lang="scss">
    .wp {
        border-top: thin solid #ddd;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
    }
    .wp > * {
        font-size: 1em;
        background: #efefef;
        flex-grow: 0;     /* do not grow   - initial value: 0 */
        flex-shrink: 0;   /* do not shrink - initial value: 1 */
        flex-basis: 40px; /* width/height  - initial value: auto */
        overflow: hidden;
        padding: 2px;
        text-align: right;
    }

    .wp > *:nth-child(2n) {
        background: white;
    }

    .day {
        border-right: thin solid #ccc;
    }

    .day-header {
        font-size: .75em;
    }

    .activity {
        background: white;
        text-shadow: 0 0 .5em rgba(0,0,0,.25);
        margin: 0 0 .5em;
        padding: 0;
        border: thin solid #dedede;
        font-size: 12px;
    }

    .activity-header {
        border-bottom: solid thin #dedede;
        margin-bottom: .5em;
        small {
            padding-left: .2em;
            color: #999999;
            font-weight: 100;
            font-size: .8em;
        }
    }

    .wp > .weekend {
        background: #aaa !important;
        text-shadow: -1px 1px 0 white;
    }
    .wp > .samedi {
        background: #ccc !important;
    }


    .activity-title {
        margin: 0;
        padding: 0;
        font-weight: 300;
        color: #000000;
        font-size: 18px;
        line-height: 1.25em;

        .project-acronym {
            font-weight: 400;
            background: #999;
            color: #fff;
            padding-right: 1em;
            position: relative;
            &:after {
                content: '';
                display: block;
                background: #999;
                transform: rotate(45deg);
                width: 1em;
                height: 1em;
                position: absolute;
                right: -.2em;
                top: .2em;

            }
        }
        .activity-label {
            padding-left: .25em;
        }
    }
</style>

<script>
    let defaultDate = new Date();
    let moment = function(){};

    export default {
        props: {
            moment: { required: true }
        },

        data(){
            return {
                ts: null,
                month: defaultDate.getMonth()+2,
                year: defaultDate.getFullYear()
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
            this.fetch()
        }
    }
</script>