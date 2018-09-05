<template>
    <section>
        <section class="period" v-for="declarations,p in periods">
            <h2>Période : <strong>{{ p | monthyear}}</strong></h2>
            <pre style="display: none">
                {{ declarations }}
            </pre>

            <section v-for="datas in declarations" class="card">
                <section class="main" v-for="activity in datas.main">
                    <h3 class="card-title">
                        <span>
                            <i class="icon-user"></i>
                            Déclaration de <strong>{{ datas.declarant }}</strong>
                        </span>
                    </h3>

                    <div class="validation" :class="{ 'valid' : activity.validationperiod.validationactivity_by, 'invalid' : activity.validationperiod.rejectactivity_by, }">
                        <span class="icon-cube"></span> Validation projet :
                        <span v-if="activity.validationperiod.validationactivity_by">
                            <i class="icon-ok-circled"></i>
                            par <strong>{{ activity.validationperiod.validationactivity_by }}</strong>
                        </span>

                        <span v-else-if="activity.validationperiod.rejectactivity_by">
                           <i class="icon-minus-circled"></i>
                           par <strong>{{ activity.validationperiod.rejectactivity_by }}</strong>
                        </span>

                        <span v-else>
                           <nav v-if="datas.validable_prj">
                               <button class="btn btn-xs btn-success"  @click="sendValidationPrj(activity.validationperiod_id)">
                                   <i class="icon-ok-circled"></i>Valider</button>
                               <button class="btn btn-xs btn-danger" @click="sendRejectPrj(activity.validationperiod_id)">
                                   <i class="icon-minus-circled"></i>Refuser</button>
                           </nav>
                        </span>
                        <span v-else>
                           <i class="icon-hourglass-3"></i>
                           <em>en attente &hellip;</em>
                        </span>
                    </div>

                    <div class="validation" :class="{ 'valid' : activity.validationperiod.validationsci_by, 'invalid' : activity.validationperiod.rejectsci_by, }">
                        <span class="icon-beaker"></span> Validation scientifique :
                        <span v-if="activity.validationperiod.validationsci_by">
                            <i class="icon-ok-circled"></i>
                            par <strong>{{ activity.validationperiod.validationsci_by }}</strong>
                        </span>

                        <span v-else-if="activity.validationperiod.rejectsci_by">
                           <i class="icon-minus-circled"></i>
                           par <strong>{{ activity.validationperiod.rejectsci_by }}</strong>
                        </span>

                        <span v-else>
                           <nav v-if="datas.validable_sci">
                               <button class="btn btn-xs btn-success"  @click="sendValidationSci(activity.validationperiod_id)">
                                   <i class="icon-ok-circled"></i>Valider</button>
                               <button class="btn btn-xs btn-danger" @click="sendRejectSci(activity.validationperiod_id)">
                                   <i class="icon-minus-circled"></i>Refuser</button>
                           </nav>
                        </span>
                        <span v-else>
                           <i class="icon-hourglass-3"></i>
                           <em>en attente &hellip;</em>
                        </span>
                    </div>

                    <div class="validation" :class="{ 'valid' : activity.validationperiod.validationadm_by, 'invalid' : activity.validationperiod.rejectadm_by, }">
                        <span class="icon-beaker"></span> Validation administrative :
                        <span v-if="activity.validationperiod.validationadm_by">
                            <i class="icon-ok-circled"></i>
                            par <strong>{{ activity.validationperiod.validationadm_by }}</strong>
                        </span>

                        <span v-else-if="activity.validationperiod.rejectadm_by">
                           <i class="icon-minus-circled"></i>
                           par <strong>{{ activity.validationperiod.rejectadm_by }}</strong>
                        </span>

                        <span v-else>
                           <nav v-if="datas.validable_adm">
                               <button class="btn btn-xs btn-success"  @click="sendValidationAdm(activity.validationperiod_id)">
                                   <i class="icon-ok-circled"></i>Valider</button>
                               <button class="btn btn-xs btn-danger" @click="sendRejectAdm(activity.validationperiod_id)">
                                   <i class="icon-minus-circled"></i>Refuser</button>
                           </nav>
                        </span>
                        <span v-else>
                           <i class="icon-hourglass-3"></i>
                           <em>en attente &hellip;</em>
                        </span>
                    </div>

                    <table class="table table-condensed">
                        <thead>
                            <tr class="header-day" style="background-color: #5c9ccc">
                                <th colspan="2">
                                    {{ p | monthyear}}
                                </th>
                                <th class="day" v-for="i in nbrDays" :title="datas.daysInfos[i]" :class="{ 'closed': datas.daysClosed[i]  }">
                                    <small>{{ datas.daysLabels[i] }}</small>
                                    {{ i }}<br>
                                </th>
                                <th>
                                    Total
                                </th>
                                <th>
                                    <i class="icon-cog"></i>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr style="background-color: #8f97a0">
                                <th :colspan="nbrDays.length + 4" class="subhead">
                                    <i class="icon-cube"></i>
                                    <strong>{{ activity.acronym }}</strong>
                                    <small>{{ activity.label }}</small>
                                </th>
                            </tr>
                            <tr v-for="lot, wpCode in activity.details" class="subgroup">
                                <th>&nbsp;</th>
                                <th><i class="icon-archive"></i>{{ lot.label }}</th>
                                <td v-for="i in nbrDays" class="day" :class="{'empty': !lot.days[i]}" :title="(lot.days[i] ? lot.days[i] : '0.0') + ' heure(s)'">
                                    {{ (lot.days[i] ? lot.days[i] : '0.0') | displayTime(datas.displayHours, datas.daysLength[i]) }}
                                </td>
                                <td class="soustotal" :title="lot.total + ' heure(s)'">
                                    {{ lot.total | displayTime(datas.displayHours, datas.monthLength) }}
                                </td>
                                <th>
                                   ~
                                </th>
                            </tr>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Total</th>
                                <td v-for="i in nbrDays" class="day soustotal" :class="{'empty': !activity.totalDays[i]}">
                                    {{ (activity.totalDays[i] ? activity.totalDays[i] : '0.0') | displayTime(datas.displayHours, datas.dayLength) }}
                                </td>
                                <td class="total">
                                    {{ activity.totalPeriod | displayTime(datas.displayHours, datas.monthLength) }}
                                </td>
                                <th>
                                    actions
                                </th>
                            </tr>


                            <tr class="interligne">
                                <th :colspan="nbrDays.length + 4">
                                    Autres projets de recherche
                                </th>
                            </tr>

                            <tr v-for="otherProject in datas.projects" class="subgroup">
                                <th>&nbsp;</th>
                                <th>
                                    <i :class=" otherProject.validation_icon"></i>
                                    {{ otherProject.code }}</th>
                                <td class="day" v-for="i in nbrDays" :class="{'empty': !otherProject.days[i]}">
                                    {{ (otherProject.days[i] ? otherProject.days[i] : '0.0')|displayTime(datas.displayHours, datas.dayLength) }}
                                </td>
                                <td class="soustotal">{{ otherProject.total|displayTime(datas.displayHours, datas.monthLength) }}</td>
                                <td>~</td>
                            </tr>

                            <tr class="interligne">
                                <th :colspan="nbrDays.length + 4">
                                    Hors-lot
                                </th>
                            </tr>

                            <tr class="subgroup" v-for="other in datas.others">
                                <th>&nbsp;</th>
                                <th>
                                    <i :class=" other.validation_icon"></i>
                                    {{ other.label }}
                                </th>
                                <td class="day" v-for="i in nbrDays" :class="{'empty': !other.days[i]}">
                                    {{ (other.days[i] ? other.days[i] : '0.0')|displayTime(datas.displayHours, datas.daylength) }}
                                </td>
                                <td class="soustotal">{{ other.total|displayTime(datas.displayHours, datas.monthLength) }}</td>
                                <td>~</td>
                            </tr>

                            <tr class="interligne">
                                <th :colspan="nbrDays.length + 4">
                                    Total période
                                </th>
                            </tr>

                            <tr class="">
                                <th>&nbsp;</th>
                                <th>{{ p | monthyear}}</th>
                                <td class="day soustotal" v-for="i in nbrDays" :class="{'empty': !datas.total[i]}">
                                    {{ (datas.total[i] ? datas.total[i] : '0.0')|displayTime(datas.displayHours, datas.dayLength) }}
                                </td>
                                <td class="total">
                                    {{ datas.totalFull|displayTime(datas.displayHours, datas.monthLength) }}
                                </td>
                                <td>~</td>
                            </tr>

                        </tbody>

                        <tfoot>

                        </tfoot>
                    </table>

                </section>


            </section>
            <hr>
        </section>
    </section>
</template>
<style>
    .validation.valid {
        border-color: green;
        color: green;
    }
    .validation.invalid {
        border-color: #990000;
        color: #990000;
    }
    .validation {
        display: inline-flex;
        color: #999;
        border: thin solid #999;
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 1em;
        }
    .validation .btn {
        border-radius: 4px;
    }

    .header-day th {
        text-align: center;
        text-shadow: -1px 1px 0 rgba(255,255,255,.5);
        border-left: thin solid rgba(255,255,255,.3);
    }
    .header-day th small {
        font-weight: 100;
        display: block;
    }

    .table tbody tr:hover {
        background-color: #deebef;
    }

    .day.empty {
        font-weight: 100;
        color: rgba(0,0,0,.333);
    }

    .closed {
        background-color: #8f97a0 !important;
    }

    .interligne {

    }
    .interligne th {
        font-weight: 100;
        border-top: dotted thin #777;
        padding-top: 1em;
    }

    .subgroup {
        font-size: .8em;
    }

    .table tbody tr td {
        text-align: right;
        border-left: solid thin #ddd;
    }

    .soustotal { font-weight: 700;}
    .total { font-weight: 900;}

</style>
<script>
    // poi watch --format umd --moduleName  ValidationActivityVue --filename.css ValidationActivityVue.css --filename.js ValidationActivityVue.js --dist public/js/oscar/dist public/js/oscar/src/ValidationActivityVue.vue

    export default {
        props: {
            days: {
                default: 31
            },
            bootbox: {
              required: true
            },
            periods: {
                default: {}
            }
        },

        filters: {
            displayTime(v, hours=true, total=8.0){
                if( hours ){
                    if( v == 0 ){
                        return 0;
                    }
                    let h = Math.floor(v);
                    let m = Math.round((v - h)*60);
                    if( m < 10 ) m = '0'+m;
                    return h +':' +m;
                } else {
                    if( v == 0 ) return 0.0;
                    return Math.round( 100 / total * v) + "%";
                }
            }
        },

        computed: {
            nbrDays(){
                let days = [];
                for( let i=1; i<= this.days; i++ ){
                    if( i < 10 ){
                        days.push('0'+i);
                    } else {
                        days.push(''+i);
                    }
                }
                return days;
            }
        },

        methods: {
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // VALIDATION des HEURES
            sendValidationPrj(validationperiod_id){
                console.log("Validation de la période", validationperiod_id);
                this.bootbox.confirm("Confirmer la validation de la décaration pour cette période ?", (res) => {
                    if( !res ) return;
                    this.send('valid-prj', validationperiod_id);

                });
            },

            sendValidationSci(validationperiod_id){
                console.log('VALID ', validationperiod_id);
                this.bootbox.confirm("Confirmer la validation scientifique de la décaration pour cette période ?", (res) => {
                    if( !res ) return;
                    this.send('valid-sci', validationperiod_id);
                });
            },

            sendValidationAdm(validationperiod_id){
                this.bootbox.confirm("Confirmer la validation administrative de la décaration pour cette période ?", (res) => {
                    if( !res ) return;
                    this.send('valid-adm', validationperiod_id);
                });
            },

            sendRejectPrj(validationperiod_id){
                this.bootbox.prompt("Indiquez les raisons du rejet de la déclaration", (message) => {
                    if( !message ) return;
                    this.send('reject-prj', validationperiod_id, message);
                });
            },

            sendRejectSci(validationperiod_id){
                this.bootbox.prompt("Indiquez les raisons du rejet scientifique de la déclaration", (message) => {
                    if( !message ) return;
                    this.send('reject-sci', validationperiod_id, message);
                });
            },

            sendRejectAdm(validationperiod_id){
                this.bootbox.prompt("Indiquez les raisons du rejet administratif de la déclaration", (message) => {
                    if( !message ) return;
                    this.send('reject-adm', validationperiod_id, message);
                });
            },


            send( action, validationperiod_id, message=''){
                let data = new FormData();
                data.append('action', action);
                data.append('validationperiod_id', validationperiod_id);
                data.append('message', message);

                this.$http.post('', data).then(
                    (ok) => {
                        document.location.reload();
                    },
                    (ko) => {
                        this.bootbox.alert("ERREUR : " + ko.body);
                    }
                ).then( foo => {

                })
            }
        },

        data(){
            return {
                foo: "default"
            }
        }
    }
</script>
