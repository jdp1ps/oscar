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
                        <h2>{{ acronym }}</h2>
                        <article class="activity" v-for="a, activityCode in p.activities">
                            <h3><i>{{ activityCode }}</i> {{ a.label }}</h3>
                            <section class="wps">
                                <article class="wp" v-for="wp, wpCode in a.wps">
                                    <h4>{{ wpCode }}</h4>
                                    <span v-for="heures, day in wp">
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

<script>
    let defaultDate = new Date();
    let moment = function(){};

    export default {
        props: {
            month: { default: defaultDate.getMonth() },
            year: { default: defaultDate.getFullYear() },
            moment: { required: true }
        },

        data(){
            return {
                ts: null
            }
        },

        filters: {
            dayText(value){
                var m = moment();
                return "Day " + m.format('YYYYY MMM dddd');
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