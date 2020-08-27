<template>
    <section>

        <transition name="fade">
            <div class="overlay" v-if="infosPerson">
                <div class="overlay-content" style="flex-basis: 90%; max-height: 90%">
                    <h3>
                        Détails pour
                        <strong>{{ infosPerson.person }}</strong>
                        pour <em>{{ infosPerson.period || formatPeriod }}</em>

                        <a href="#" @click.prevent="infosPerson = null"><i class="icon-cancel-outline"></i></a>
                    </h3>

                    <table class="table bordered table table-condensed">
                        <thead>
                        <tr>
                            <th> ~</th>
                            <th v-for="d, i in infosPerson.days">
                                <small style="font-weight: 100">{{ d.label }}</small>
                                {{ d.i }}
                            </th>
                            <th> Total</th>
                        </tr>
                        </thead>
                        <tbody v-for="activity in organize(infosPerson).activities">
                        <tr>
                            <th :colspan="infosPerson.dayNbr + 2"><h4><i class="icon-cube"></i>{{ activity.acronym }}</h4></th>
                        </tr>
                        <tr v-for="workpackage in activity.workpackages">
                            <th><i class="icon-archive"></i>{{ workpackage.code }}</th>
                            <td v-for="d, i in infosPerson.days" class="day" :class="{ 'off': d.locked }">
                                <strong v-if="workpackage.days[i]">
                                    {{ workpackage.days[i] | formatDuration }}
                                </strong>
                                <small v-else>0.0</small>
                            </td>
                            <th class="total">{{ workpackage.total | formatDuration }}</th>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td :colspan="infosPerson.dayNbr">&nbsp;</td>
                            <th>{{ activity.total | formatDuration }}</th>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr>
                            <th :colspan="infosPerson.dayNbr + 2"><h4><i class="icon-tags"></i>Hors-lot</h4></th>
                        </tr>
                        <tr v-for="other in organize(infosPerson).others">
                            <th>{{ other.label }}</th>
                            <td v-for="d, i in infosPerson.days" class="day" :class="{ 'off': d.locked }">
                                <strong v-if="other.days[i]">
                                    {{ other.days[i] | formatDuration }}
                                </strong>
                                <small v-else>0.0</small>
                            </td>
                            <th class="total">{{ other.total | formatDuration }}</th>
                        </tr>

                        </tbody>
                        <tfoot>
                        <tr>
                            <th>Total</th>
                            <th v-for="d, i in infosPerson.days" class="day" :class="{'off': d.locked }">
                                <small>{{ d.total | formatDuration }}</small>
                            </th>
                            <th>{{ infosPerson.total | formatDuration }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-if="error">
                <div class="alert alert-danger">
                    <h3>Erreur
                        <a href="#" @click.prevent="error =null" class="float-right">
                            <i class="icon-cancel-outline"></i>
                        </a>
                    </h3>
                    <p>{{ error }}</p>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-if="loading">
                <div class="overlay-content">
                    <p>Chargement de la période</p>
                </div>
            </div>
        </transition>

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
            <article class="card" v-for="d in declarers" style="display: flex">

                    <span style="width: 48px; flex: 0">
                        <img class="thumb32" :src="'//www.gravatar.com/avatar/'+d.mailMd5+'?s=48'" alt="">
                    </span>
                    <span style="flex: 1; padding-left: .25em">
                        <strong class="lastname">{{ d.lastName }}</strong>
                        <em class="firstname">{{ d.firstName }}</em>
                        <a href="#" class="link" @click.prevent="details(d)"><i class="icon-calendar"></i>Détails</a>

                        <br>
                        <small>{{ d.affectation }}</small>
                    </span>
                    <span class="tags" style="flex: 1">
                        <span class="tag cartouche xs" v-for="p in d.projects">
                            <i class="icon-cubes"></i>{{ p }}</span>
                    </span>
                    <span style="flex: 1">
                        <strong class="cartouche secondary1">
                            <i class="icon-calendar"></i>
                            <small>Saisie : </small><strong>{{ (100 / d.details.waitingTotal * d.details.total) | percent }} %</strong>
                            <span class="addon">
                                {{ d.details.total | round1 }} / {{ d.details.waitingTotal | round1 }}
                            </span>
                        </strong><br>
                        <strong class="cartouche xs secondary1" :class="d.details.state">
                            <i class="icon-paper-plane"></i>
                            <small>État : <strong>{{ d.details.stateText }}</strong></small>
                        </strong>
                    </span>
                    <nav class="actions" style="width: 175px;">
                        <button class="btn btn-primary btn-xs" v-if="d.details.state == 'PERIOD_NODECLARATION'" @click="recall(d.id, period)">
                            <i class="icon-mail">Relancer le déclarant</i>
                        </button><br>
                        <a class="btn btn-default btn-xs" v-if="d.url_person" :href="d.url_person">
                            <i class="icon-user">Fiche personne</i>
                        </a>
                    </nav>


            </article>
        </div>
    </section>
</template>
<script>
    //  nodejs node_modules/.bin/poi watch --format umd --moduleName  Declarers --filename.js Declarers.js --dist public/js/oscar/dist public/js/oscar/src/Declarers.vue

    export default {
        props: {
            urlRecallDeclarer: { required: true }
        },
        data(){
            return {
                declarers: null,
                period: null,
                error: null,
                loading: false,
                infosPerson: null,
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

        filters: {
            percent(value){
                return ''+(Math.round(value*10)/10).toFixed(1);
            },
            round1(value){
                return value.toFixed(1);
            },
            formatDuration(heure) {
                var h = Math.floor(heure);
                var m = Math.round((heure - h) * 60);
                return h + ':' + (m < 10 ? '0' + m : m);
            }
        },

        methods: {
            recall(personId, period){
              console.log(personId, period.periodCode, this.urlurlRecallDeclarer);
            },

            details(declarer){
                this.$http.get(declarer.url_details).then(
                    ok => {
                        this.infosPerson = ok.data;
                    }
                );
            },


            organize(personDatas) {
                let datas = {
                    activities: {},
                    others: {}
                };

                Object.keys(personDatas.activities).forEach(activityId => {
                    var activity = personDatas.activities[activityId];
                    datas.activities[activityId] = {
                        acronym: activity.acronym,
                        id: activity.id,
                        label: activity.label,
                        total: activity.total,
                        workpackages: {},
                        days: {}
                    };
                });

                Object.keys(personDatas.workpackages).forEach(wpId => {
                    var wp = personDatas.workpackages[wpId];
                    datas.activities[wp.activity_id].workpackages[wpId] = {
                        code: wp.code,
                        id: wp.id,
                        label: wp.label,
                        total: wp.total,
                        days: {}
                    };
                });

                Object.keys(personDatas.otherWP).forEach(otherKey => {
                    var other = personDatas.otherWP[otherKey];
                    datas.others[otherKey] = {
                        label: other.label,
                        code: other.code,
                        total: other.total,
                        days: {}
                    };
                });

                Object.keys(personDatas.days).forEach(date => {
                    var day = personDatas.days[date];

                    if (day.declarations) {
                        day.declarations.forEach(declaration => {
                            var wpId = declaration.wp_id;
                            var activityId = declaration.activity_id;
                            var duration = declaration.duration;

                            if (!datas.activities[activityId].days.hasOwnProperty(date)) {
                                datas.activities[activityId].days[date] = 0.0;
                            }
                            datas.activities[activityId].days[date] += duration;

                            if (!datas.activities[activityId].workpackages[wpId].days.hasOwnProperty(date)) {
                                datas.activities[activityId].workpackages[wpId].days[date] = 0.0;
                            }
                            datas.activities[activityId].workpackages[wpId].days[date] += duration;
                        });
                    }
                    if (day.othersWP) {
                        day.othersWP.forEach(otherInfos => {
                            var otherKey = otherInfos.code;
                            var duration = otherInfos.duration;
                            if (!datas.others[otherKey].days.hasOwnProperty(date)) {
                                datas.others[otherKey].days[date] = 0.0;
                            }
                            datas.others[otherKey].days[date] += duration;
                        })

                    }
                });
                return datas;
            },
            fetch( p = null ){
                this.loading = "Chargement de la période";

                this.$http.get( p ? ('?period=' +p) : '').then(
                     ok => {
                        this.declarers = ok.body.declarers.sort((a,b)=> a.lastName.localeCompare(b.lastName));
                        this.period = ok.body.period;
                     },
                     ko => {
                        this.error = ko.body;
                     }
                ).then( foo => {
                    this.loading = false;
                });
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