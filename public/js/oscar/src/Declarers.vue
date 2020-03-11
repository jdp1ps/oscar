<template>
    <section>
        <nav class="navbar navbar-default">
            <div class="button-group" v-if="period" >
                <button class="btn btn-default" @click="previousPeriod()"> < </button>
                <span>Période : </span>
                <select name="mois" v-model="period.month" @change="changePeriod()">
                    <option :value="m" v-for="mm,m in months">{{ mm }}</option>
                </select>
                <select name="mois" v-model="period.year" @change="changePeriod()">
                    <option :value="y" v-for="y in years">{{ y }}</option>
                </select>
                <strong>{{ period.periodLabel }}</strong>
                <button class="btn btn-default" @click="nextPeriod()"> > </button>
            </div>
        </nav>
        <div class="list" v-if="declarers">
            <article class="card" v-for="d in declarers">
                <h3 class="card-title">
                    <span style="flex: 3">
                    <strong><i class="icon-user"></i>{{ d.displayname }}</strong>
                    <em v-if="d.affectation">{{ d.affectation }}</em>
                    </span>
                    <span class="tags">
                        <span class="tag cartouche xs" v-for="p in d.projects">
                            <i class="icon-cubes"></i>{{ p }}</span>
                    </span>

                </h3>
                <div class="card-content">
                    <strong>{{ d.displayname }}</strong> a saisi <strong>{{ d.details.total }}</strong> sur <em>{{ d.details.waitingTotal }}</em> prévu. <br>
                    Saisi à <strong class="label label-primary">{{ 100 / d.details.waitingTotal * d.details.total }} %</strong>
                    État de la déclaration : <strong>{{ d.details.stateText }}</strong>
                    <code>{{ d.details.validations }}</code>
                </div>
            </article>
        </div>
    </section>
</template>
<script>
    //  nodejs node_modules/.bin/poi watch --format umd --moduleName  Declarers --filename.js Declarers.js --dist public/js/oscar/dist public/js/oscar/src/Declarers.vue


    export default {
        data(){
            return {
                declarers: null,
                period: null,
                error: null,
                loading: false,
                months: {
                    1: "Janvier",
                    2: "Février",
                    3: "Mars",
                    4: "Avril",
                    5: "Mai",
                    6: "Juin",
                    7: "Juillet",
                    8: "Aout",
                    9: "Septembre",
                    10: "Octobre",
                    11: "Novembre",
                    12: "Décembre"
                }
            }
        },
        computed: {
            years(){
                let out = [];
                for( let i=this.period.year-5; i<=this.period.year+5; i++ ){
                    out.push(i);
                }
                return out;
            }
        },

        methods: {
            fetch( p = null ){
                this.$http.get( p ? ('?period=' +p) : '').then(
                     ok => {
                        this.declarers = ok.body.declarers;
                        this.period = ok.body.period;
                     },
                     ko => {
                        this.error = ko.body;
                     }
                );
            },

            nextPeriod(){
                let month,year;
                if( this.period.month == 12 ){
                    month = 1;
                    year = this.period.year + 1;
                } else {
                    month = this.period.month + 1;
                    year = this.period.year;
                }
                this.fetch(year +'-' +month);
            },

            previousPeriod(){
                let month,year;
                if( this.period.month == 1 ){
                    month = 12;
                    year = this.period.year - 1;
                } else {
                    month = this.period.month - 1;
                    year = this.period.year;
                }
                this.fetch(year +'-' +month);
            },

            changePeriod(){
                let period = this.period.year+'-'+this.period.month;
                this.fetch(period);
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>