<template>
    <div>
        <div class="overlay" v-if="error"  style="z-index: 2002">
            <div class="content container overlay-content">
                <h2><i class="icon-attention-1"></i> Oups !</h2>
                <pre class="alert alert-danger">{{ error }}</pre>
                <p class="text-danger">
                    Si ce message ne vous aide pas, transmettez le à l'administrateur Oscar.
                </p>
                <nav class="buttons">
                    <button class="btn btn-primary" @click="error = ''">Fermer</button>
                </nav>
            </div>
        </div>

        <section v-if="declarations && declarations.length" class="validation">
            <section v-for="period in periodsPerson">
                <h2>
                    <strong class="person"><i class="icon-user"></i> {{ period.person }}</strong>
                    <strong class="period"><i class="icon-calendar"></i> {{ period.period | period}}</strong>
                </h2>

                <section class="activity card">
                    <table class="table table-condensed">

                        <thead class="heading-days">
                            <th>~</th>
                            <th v-for="dayInfos, day in period.details" :class="{'locked': dayInfos.locked}">
                                <small>{{ dayInfos.label }}</small>
                                <strong>{{ day }}</strong>
                            </th>
                            <th>Total</th>
                            <th style="max-width: 150px"><i class="icon-cog"></i> Validation</th>
                        </thead>

                        <tbody>
                            <template v-for="activity in period.declarations_activities">
                            <tr class="heading-activity heading">
                                <th :colspan="period.totalDays+3">
                                    <i class="icon-cube"></i>{{ activity.label }}
                                </th>

                            </tr>
                            <tr v-for="lot in activity.workpackages"  class="datas">
                                <th :title="lot.label"><i class="icon-archive"></i>{{ lot.code }}</th>
                                <td v-for="d in period.totalDays">
                                   <strong v-if="lot.timesheets[d]">{{ lot.timesheets[d] | duration2 }}</strong>
                                    <em v-else>-</em>
                                </td>
                                <th>
                                    {{ lot.total }}
                                </th>
                                <th>-</th>
                            </tr>
                                <tr>
                                    <th :colspan="period.totalDays-1">
                                        Total pour <i class="icon-cube"></i>{{ activity.label }}
                                    </th>
                                    <th colspan="2">Actions</th>
                                    <th>
                                        {{ activity.total | duration2 }}
                                    </th>
                                    <th>
                                        <small><i :class="'icon-'+activity.status"></i> {{ activity.statusMessage }}</small>
                                        <span v-if="activity.validableStep">
                                            <button class="btn btn-success btn-xs" @click="validate(activity.validationperiod_id)" v-if="activity.validableStep">Valider</button>
                                            <button class="btn btn-danger btn-xs" @click="reject(activity.validationperiod_id)" v-if="activity.validableStep">Rejeter</button>
                                        </span>
                                        <span v-else>
                                            <span v-if="activity.validabe">
                                                <span v-if="activity.validators.length == 0" class="text-danger">
                                                    <i class="icon-attention-1"></i> Aucun validateur pour cette étape, contacter l'administrateur Oscar pour corriger le problème
                                                </span>
                                                <div v-else>
                                                    <span class="cartouche xs" v-for="v in activity.validators">{{ v }}</span>
                                                </div>
                                            </span>
                                        </span>
                                    </th>
                                </tr>
                            </template>

                            <template v-if="period.declarations_others">
                                <tr class="heading-activity heading">
                                    <th :colspan="period.totalDays+3"><i class="icon-tags"></i>Hors-lot</th>
                                </tr>
                                <tr v-for="hl, hlcode in period.declarations_others" class="datas">
                                    <th><i class="hors-lot" :class="'icon-' + hlcode"></i>{{ hl.label }}</th>
                                    <td v-for="d in period.totalDays">
                                        <strong v-if="hl.timesheets[d]">{{ hl.timesheets[d] | duration2 }}</strong>
                                        <em v-else>-</em>
                                    </td>
                                    <th>
                                        {{ hl.total }}
                                    </th>
                                    <th>
                                        <small><i :class="'icon-'+hl.status"></i> {{ hl.statusMessage }}</small>
                                        <span v-if="hl.validableStep">
                                            <button class="btn btn-success btn-xs" @click="validate(hl.validationperiod_id)">Valider</button>
                                            <button class="btn btn-danger btn-xs" @click="reject(hl.validationperiod_id)">Rejeter</button>
                                        </span>
                                        <span v-else-if="hl.validabe">
                                            <span v-if="hl.validators.length == 0">
                                                <i class="icon-attention-1"></i> Aucun validateur pour cette étape, contacter l'administrateur Oscar pour corriger le problème
                                            </span>
                                            <div v-else>
                                                <span class="cartouche xs" v-for="v in hl.validators">{{ v }}</span>
                                            </div>
                                        </span>
                                    </th>
                                </tr>
                            </template>

                            <template v-if="period.declarations_off">
                                <tr class="heading-activity heading">
                                    <th :colspan="period.totalDays+2"><i class="icon-lock"></i>Autres déclarations </th>
                                </tr>
                                <tr class="datas">
                                    <th><small>~</small></th>
                                    <td v-for="d in period.totalDays">
                                        <strong v-if="period.declarations_off.timesheets[d]">{{ period.declarations_off.timesheets[d] | duration2 }}</strong>
                                        <em v-else>-</em>
                                    </td>
                                    <th>
                                        {{ period.declarations_off.total }}
                                    </th>
                                    <th><small>Vous ne pouvez pas voir le détails pour ces créneaux</small></th>
                                </tr>
                            </template>
                        </tbody>

                        <tfoot>
                        <tr class="total-row">
                            <th>Total période</th>
                            <th v-for="dayInfos, day in period.details" :class="{'locked': dayInfos.locked}">
                                <strong>{{ dayInfos.duration | duration2 }}<small>/ {{ dayInfos.dayLength | duration2 }}</small></strong>
                            </th>
                            <th>{{ period.total | duration2 }}</th>
                            <th>-</th>
                            </tr>
                        </tfoot>
                    </table>
                </section>

            </section>
        </section>
        <section v-else class="alert alert-info">
            Aucune déclaration en attente
        </section>
    </div>
</template>
<style lang="scss">
    .validation {

        background: transparent;

        font-size: 14px;

        .heading {
            font-size: 1.2em;
        }
        th small { display: block; }

        tbody tr.datas:hover {
            background: rgba(#5c9ccc, .25);
        }

        .heading-days {
            th {
                text-align: center;
                &.locked {
                    background: #CCC;
                }
            }
        }
        td, th {
            border-right: thin #ddd solid;
        }

        th small {
            font-weight: 100; font-size: .8em;
        }
        td:nth-child(odd) {
            background: rgba(#ccc, .3);
        }

        .datas th {
            padding-left: 1em;
        }
        .datas td {
            text-align: right;
        }
    }

</style>
<script>
    // Compilation :
    // poi watch --format umd --moduleName  ValidationPeriod --filename.css ValidationPeriod.css --filename.js ValidationPeriod.js --dist public/js/oscar/dist public/js/oscar/src/ValidationPeriod.vue
    export default {
        data() {
            return {
                error: null,
                declarations: null,
                group: 'monthLabel' // ou label
            }
        },

        props: {
            bootbox: { required: true }
        },

        computed: {
            periodsPerson(){
                return this.declarations;
            }
        },

        methods: {
            fetch(){
                this.$http.get().then(
                    ok => {
                        this.declarations = ok.body;
                    },
                    ko => {
                        this.error = "Impossible de charger les données : " + ko.body;
                    }
                )
            },

            validate( period_id ){
                this.bootbox.confirm("Valider cette déclaration ?", ok => {
                    if( ok ){
                        this.send('valid', period_id, '');
                    }
                });
            },

            reject( period_id ){
                this.bootbox.prompt("Refuser cette déclaration ?", ok => {
                    if( ok )
                        this.send('reject', period_id, ok);
                });
            },

            send(action, period_id, message){
                let dataSend = new FormData();
                dataSend.append('period_id', period_id);
                dataSend.append('action', action);
                dataSend.append('message', message);
                this.$http.post('', dataSend).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = "Erreur : " + ko.body;
                    }
                );
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>