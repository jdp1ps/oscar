<template>
    <div>

        <h1>Vos déclarations</h1>
        <div class="overlay" v-if="debug">
            <div class="overlay-content">
                <button @click="debug = null">close</button>
                <pre>{{ debug }}</pre>
            </div>
        </div>

        <table class="table declarations-resume">
            <thead>
            <tr>
                <th>Période</th>
                <th>Déclarations</th>
                <th>Activité</th>
                <th>Total activité</th>
                <th>Total hors-activité</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
            </thead>

            <template v-for="yeardatas, year in years">
                <tr class="heading-year">
                    <th colspan="7">{{ year }}</th>
                </tr>
            <tbody class="yearrow">
                <tr v-for="p in yeardatas.periods" :class="{
                'valid-100' : p.total == p.periodDuration,
                'valid-95': p.total >= p.periodDuration*.95 && p.total < p.periodDuration,
                'valid-105': p.total <= p.periodDuration*1.05 && p.total > p.periodDuration,
                'error-105': p.total > p.periodDuration*1.05,
                'error-95': p.total < p.periodDuration*.95,
                'optional': p.activities_id.length == 0,
                'conflict' : p.validation_state == 'conflict',
                'validating' : p.validation_state && p.validation_state.indexOf('send-') == 0,
                'validated' : p.validation_state == 'valid'
                }">
                    <th class="period"><strong @click="debug = p">
                        <i class="icon-ellipsis" v-if="p.activities_id.length == 0" title="Aucune déclaration requise pour cette période"></i>
                        <i class="icon-attention-1" v-if="p.activities_id.length && !p.validations_id.length && p.past" title="Il faut déclarer pour cette période"></i>
                        <i class="icon-calendar" v-if="p.activities_id.length && p.validations_id.length" title="Procédure de déclaration en cours"></i>

                        {{ p.period | period}}</strong>
                    </th>
                    <td class="required">
                        <em v-if="p.activities_id.length">
                            <span v-if="p.validations_id.length">
                                <i :class="'icon-' +p.validation_state"></i>
                                <small v-if="p.validators.length">
                                    Validateur(s) :
                                    <strong class="cartouche" v-for="v in p.validators">
                                        <i class="icon-user"></i>
                                        {{ v }}</strong>
                                </small>
                            </span>
                            <a :href="'/feuille-de-temps/excel?action=export&period=' +p.period +'&personid=' + p.person_id" v-if="p.validation_state == 'valid'">
                                <i class="icon-download-outline"></i>
                                Télécharger la feuille de temps (Excel)
                            </a>
                            <em v-if="p.validations_id.length == 0">
                               Pas de déclaration envoyée
                            </em>
                        </em>
                        <em v-else>
                            Facultatif
                        </em>
                    </td>

                    <td>
                        <div v-for="activityId in p.activities_id">
                            <i class="icon-cube"></i>
                            <strong :title="datas.activities[activityId].acronym +' : ' +datas.activities[activityId].label">{{ datas.activities[activityId].acronym }}</strong>
                            <span v-if="p.total_activities_details">
                                {{  p.total_activities_details[activityId] | heures }}
                            </span>
                            <em v-else>Rien</em>
                        </div>
                    </td>

                    <td class="soustotal text-right">{{ p.total_activities | heures }}</td>
                    <td class="soustotal text-right">{{ p.total_horslots | heures }}</td>
                    <td class="total text-right">
                        <i class="icon-time icon-clock"></i>
                        <strong>{{ p.total | heures }}</strong> <small>/ {{ p.periodDuration | heures }}</small></td>
                    <td class="total text-right">
                        <em class="text-danger">{{p.error}}</em>
                        <span v-if="datas.owner">
                            <a class="xs btn btn-primary btn-xs" :href="'/feuille-de-temps/declarant?month=' +p.month +'&year=' +p.year" v-if="p.validation_state == 'conflict'">
                                <i class="icon-edit"></i>
                                Corriger
                            </a>
                            <a class="xs btn btn-default btn-xs" :href="'/feuille-de-temps/declarant?month=' +p.month +'&year=' +p.year" v-else-if="p.validations_id.length > 0">
                                <i class="icon-zoom-in-outline"></i>
                                Visualiser
                            </a>
                            <a class="xs btn btn-primary btn-xs" :href="'/feuille-de-temps/declarant?month=' +p.month +'&year=' +p.year" v-else>
                                <i class="icon-calendar"></i>
                                Déclarer
                            </a>
                        </span>

                        <a :href="'/feuille-de-temps/excel?action=export&period=' +p.period +'&personid=' + p.person_id">
                            <i class="icon-download-outline"></i>
                            Prévisualiser (Excel)
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Total {{ year }}</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th class="text-right">{{ yeardatas.total_activities | heures }}</th>
                    <th class="text-right">{{ yeardatas.total_horslots | heures }}</th>
                    <th class="text-right"><strong>{{ yeardatas.total | heures }}</strong><small>/ {{ yeardatas.periodDuration | heures }}</small></th>
                    <th>&nbsp;</th>
                </tr>
            </tbody>

            </template>
        </table>

    </div>
</template>
<script>
    // poi watch --format umd --moduleName  TimesheetPersonResume --filename.css TimesheetPersonResume.css --filename.js TimesheetPersonResume.js --dist public/js/oscar/dist public/js/oscar/src/TimesheetPersonResume.vue
    export default {
        props: {
            datas: {
                required: true
            }
        },

        data(){
            return {
                debug: null
            }
        },

        computed: {
            years(){
                let out = {};
                Object.keys(this.datas.periods).forEach( periodKey => {
                    if( !this.datas.periods[periodKey].futur ) {
                        let split = periodKey.split('-');
                        let year = split[0];
                        let month = split[1];
                        if (!out.hasOwnProperty(year)) {
                            out[year] = {
                                periods: {},
                                total: 0.0,
                                periodDuration: 0.0,
                                total_activities: 0.0,
                                total_horslots: 0.0
                            }
                        }

                        out[year].periods[periodKey] = this.datas.periods[periodKey];
                        out[year].total += this.datas.periods[periodKey].total;
                        out[year].periodDuration += this.datas.periods[periodKey].periodDuration;
                        out[year].total_activities += this.datas.periods[periodKey].total_activities;
                        out[year].total_horslots += this.datas.periods[periodKey].total_horslots;
                    }
                });
                return out;
            },

            periods(){
                let periods = [];
                Object.keys(this.datas.periods).forEach( periodKey => {
                    periods.push(this.datas.periods[periodKey]);
                });
                return periods;
            }
        },

        methods: {
            getActivity(activityId){

            }
        }
    }
</script>