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

                <h3 @click="period.open = !period.open" :class="{ 'has-validation': period.validableStep }">
                    <strong class="person"><i class="icon-user"></i> {{ period.person }}</strong>
                    <strong class="period"><i class="icon-calendar"></i> {{ period.period | period}}</strong>
                    <small v-if="period.validableStep">
                        <i class="icon-clock"></i>
                        Validation à faire
                    </small>
                </h3>

                <transition name="slide">
                <section class="activity card" v-show="period.open">

                    <table class="table table-condensed">

                        <thead class="heading-days">
                            <th>~</th>
                            <th v-for="dayInfos, day in period.details" :class="{'locked': dayInfos.locked}">
                                <small>{{ dayInfos.label }}</small>
                                <strong>{{ day }}</strong>
                            </th>
                            <th>Total</th>
                            <th style="width: 150px"><i class="icon-cog"></i> Validation</th>
                        </thead>

                        <tbody>
                            <template v-for="activity in period.declarations_activities">
                            <tr class="heading-activity heading">
                                <th :colspan="period.totalDays+3">
                                    <i class="icon-cube"></i>{{ activity.label }}

                                    <span class="state" :class="'state-' + activity.status">
                                        <i :class="'icon-'+activity.status"></i>
                                        {{ activity.status | status }}
                                    </span>

                                    <span v-if="activity.validators.length">
                                        Validateurs :
                                        <span v-for="v in activity.validators" class="cartouche cartouche-xs xs">
                                            {{ v }}
                                        </span>
                                    </span>
                                </th>

                            </tr>
                                <tr class="heading-activity heading">
                                    <th :colspan="period.totalDays+3">
                                        <strong>Commentaire</strong>
                                        <pre style="white-space: pre-wrap; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif">{{ activity.comment }}</pre>
                                    </th>

                                </tr>
                            <tr v-for="lot in activity.workpackages"  class="datas">
                                <th :title="lot.label"><i class="icon-archive"></i>{{ lot.code }}</th>
                                <td v-for="d in period.totalDays">
                                   <strong v-if="lot.timesheets[d]">{{ lot.timesheets[d] | duration2 }}</strong>
                                    <em v-else>-</em>
                                </td>
                                <th class="total">
                                    {{ lot.total | duration2 }}
                                </th>
                                <th>-</th>
                            </tr>
                                <tr>
                                    <th :colspan="period.totalDays-1" style="padding-left: 1em">
                                        Total
                                    </th>
                                    <th colspan="2">&nbsp;</th>
                                    <th class="total">
                                        {{ activity.total | duration2 }}
                                    </th>
                                    <th>
                                        <small><i :class="'icon-'+activity.status"></i> {{ activity.statusMessage }}</small>
                                        <span v-if="activity.validableStep">
                                            <button class="btn btn-success btn-xs" @click="validate(activity.validationperiod_id)" v-if="activity.validableStep">Valider</button>
                                            <button class="btn btn-danger btn-xs" @click="reject(activity.validationperiod_id)" v-if="activity.validableStep">Rejeter</button>
                                        </span>
                                        <span v-else>
                                            <span v-if="activity.validable">
                                                <span v-if="activity.validators.length == 0" class="text-danger">
                                                    <i class="icon-attention-1"></i> Aucun validateur
                                                </span>
                                                <div v-else>
                                                    <span class="cartouche xs" v-for="v in activity.validators">{{ v }}</span>
                                                </div>
                                            </span>
                                        </span>
                                    </th>
                                </tr>
                            </template>

                            <template v-if="period.declarations_others && period.declarations_others.length != 0">
                                <tr class="heading-activity heading">
                                    <th :colspan="period.totalDays+3"><i class="icon-tags"></i>Hors-lot</th>
                                </tr>
                                <tr v-for="hl, hlcode in period.declarations_others" class="datas">
                                    <th>
                                        <i class="hors-lot" :class="'icon-' + hlcode"></i>{{ hl.label }}
                                        <span class="state" :class="'state-' + hl.status">
                                            <i :class="'icon-'+hl.status"></i>
                                            {{ hl.status | status }}
                                        </span>
                                        <pre class="commentaire-hl"><strong>Commentaire : </strong>{{ hl.comment }}</pre>

                                    </th>
                                    <td v-for="d in period.totalDays">
                                        <strong v-if="hl.timesheets[d]">{{ hl.timesheets[d] | duration2 }}</strong>
                                        <em v-else>-</em>
                                    </td>
                                    <th class="total">
                                        {{ hl.total | duration2 }}
                                    </th>
                                    <th>
                                        <small><i :class="'icon-'+hl.status"></i> {{ hl.statusMessage }}</small>
                                        <span v-if="hl.validableStep">
                                            <button class="btn btn-success btn-xs" @click="validate(hl.validationperiod_id)">Valider</button>
                                            <button class="btn btn-danger btn-xs" @click="reject(hl.validationperiod_id)">Rejeter</button>
                                        </span>
                                        <span v-else-if="hl.validabe">
                                            <span v-if="hl.validators.length == 0">
                                                <i class="icon-attention-1"></i> Aucun validateur
                                            </span>
                                            <div v-else>
                                                <span class="cartouche xs" v-for="v in hl.validators">{{ v }}</span>
                                            </div>
                                        </span>
                                    </th>
                                </tr>
                            </template>

                            <template v-if="period.declarations_off && period.declarations_off.total > 0">
                                <tr class="heading-activity heading">
                                    <th :colspan="period.totalDays+2"><i class="icon-lock"></i>Autres déclarations
                                    </th>
                                </tr>
                                <tr class="datas">
                                    <th><small>Vous ne pouvez pas voir le détails pour ces créneaux</small></th>
                                    <td v-for="d in period.totalDays">
                                        <strong v-if="period.declarations_off.timesheets[d]">{{ period.declarations_off.timesheets[d] | duration2 }}</strong>
                                        <em v-else>-</em>
                                    </td>
                                    <th class="total">
                                        {{ period.declarations_off.total | duration2 }}
                                    </th>
                                    <td>~</td>
                                </tr>
                            </template>
                        </tbody>

                        <tfoot>
                        <tr class="total-row">
                            <th>Total période</th>
                            <td class="day" v-for="dayInfos, day in period.details" :class="{'locked': dayInfos.locked,
                                'less': dayInfos && dayInfos.duration < dayInfos.dayLength,
                                'many': dayInfos && dayInfos.duration > dayInfos.dayLength,
                                'warnmin': dayInfos && dayInfos.duration < dayInfos.amplitudemin,
                                'littlemin': dayInfos && dayInfos.duration > dayInfos.amplitudemin && dayInfos.duration < dayInfos.dayLength,
                                'warnmax': dayInfos && dayInfos.duration > dayInfos.amplitudemax,
                                'littlemax': dayInfos && dayInfos.duration < dayInfos.amplitudemax && dayInfos.duration > dayInfos.dayLength,
                                'exact': dayInfos && dayInfos.duration == dayInfos.dayLength}">

                                <i class="icon-up-outline icon-many"></i>
                                <i class="icon-down-outline icon-less"></i>
                                <i class="icon-clock icon-exact"></i>

                                <strong v-if="dayInfos.duration">{{ dayInfos.duration | duration2 }}</strong>
                                <small v-else>0.0</small>
                            </td>
                            <th class="total">{{ period.total | duration2 }}</th>
                            <td>-</td>
                            </tr>
                            <tr class="total-row">
                                <th>Temps normal prévu</th>
                                <td class="day" v-for="dayInfos, day in period.details">
                                    <small>{{ dayInfos.dayLength | duration2 }}</small>
                                </td>
                                <th class="total"><small>{{ period.periodLength | duration2}}</small></th>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                </section>
                </transition>

            </section>
        </section>
        <section v-else class="alert alert-info">
            Aucune déclaration en attente
        </section>
    </div>
</template>
<style lang="scss">
    .validation {

        .day {
            white-space: nowrap;
            .icon-less, .icon-exact, .icon-many { display: none }


            &.littlemax {

            }

            &.exact {
                color: darkgreen;
            }

            &.littlemin {

            }


            &.less {
                color: darkgreen;
                .icon-less {
                    display: inline-block;
                    color: green;
                }

                &.warnmin {
                    background: red;
                    strong { color: white };
                    .icon-less {
                        color: white;
                    }
                }
            }

            &.many {
                color: darkgreen;
                .icon-many {
                    display: inline-block;
                    color: green;
                }
                &.warnmax {
                    background: red;
                    strong { color: white };
                    .icon-many {
                        color: white;
                    }
                }
            }

        }

        table.table {
            td, th {
                padding: 2px;
                font-size: .9em;
            }

            .datas > th {
                padding-left: 1em;
            }

            .heading-activity > th {
                padding-top: 1em;
                border-top: solid 1px #ddd;
                font-weight: 100;
            }
        }



        .has-validation {
            cursor: pointer;
            border-left: 4px #0b93d5 solid;
        }

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
        .datas td, .total-row td {
            text-align: right;
        }

        .total {
            text-align: right;
            font-size: 1.2em;
        }
    }

</style>
<script>
    // Compilation :
    // poi watch --format umd --moduleName  ValidationPeriod --filename.css ValidationPeriod.css --filename.js ValidationPeriod.js --dist public/js/oscar/dist public/js/oscar/src/ValidationPeriod.vue


    const status = {
        'valid': 'Validée',
        'send-prj': 'Validation projet',
        'send-sci': 'Validation scientifique',
        'send-adm': 'Validation administrative',
        'conflict': 'Refusée'
    };

    export default {
        filters: {
            status(s){
                return status[s];
            }
        },

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
                        let datas = [];
                        Object.keys(ok.body).forEach(key => {
                           ok.body[key].open = ok.body[key].validableStep;
                            datas.push(ok.body[key]);
                        });
                        this.declarations = datas;
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