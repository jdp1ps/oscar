<template>
    <div>

        <h1>Résumé</h1>
        <div class="overlay" v-if="debug">
            <div class="overlay-content">
                <button @click="debug = null">close</button>
                <pre>{{ debug }}</pre>
            </div>
        </div>
        <pre>{{ debug }}</pre>
        <table class="table">
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
                    <th colspan="6">{{ year }}</th>
                </tr>
            <tbody class="yearrow">
                <tr v-for="p in yeardatas.periods">
                    <th class="period"><strong @click="debug = p">{{ p.period | period}}</strong></th>
                    <td class="required">
                        <em v-if="p.activities_id.length">
                            <span v-if="p.validations_id.length">Oui</span>
                            <span v-else="p.validations_id.length">Non</span>
                        </em>
                        <em v-else>
                            Facultatif
                        </em>
                    </td>
                    <td>{{ p.activities_id.length }}</td>
                    <td class="soustotal">{{ p.total_activities }}</td>
                    <td class="soustotal">{{ p.total_horslots }}</td>
                    <td class="total">{{ p.total }} / {{ p.validations_id.length }}</td>
                    <td class="total">
                        <em class="text-danger">{{p.error}}</em>
                        <span v-if="p.total > 0">
                            <template v-if="p.validations_id.length > 0">Envoyé</template>
                            <template v-else>Pas encore envoyée</template>
                        </span>
                        <em v-else>
                           Aucune déclaration
                        </em>
                        <a class="xs btn btn-primary btn-xs" :href="'/feuille-de-temps/declarant?month=' +p.month +'&year=' +p.year">
                            <i class="icon-calendar"></i>
                            <template v-if="p.validations_id.length > 0">Visualiser</template>
                            <template v-else>Déclarer</template>

                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Total {{ year }}</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>{{ yeardatas.total_activities }}</th>
                    <th>{{ yeardatas.total_horslots }}</th>
                    <th>{{ yeardatas.total }}</th>
                    <th>&nbsp;</th>
                </tr>
            </tbody>

            </template>
        </table>

    </div>
</template>
<script>
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
                                total_activities: 0.0,
                                total_horslots: 0.0
                            }
                        }

                        out[year].periods[periodKey] = this.datas.periods[periodKey];
                        out[year].total += this.datas.periods[periodKey].total;
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