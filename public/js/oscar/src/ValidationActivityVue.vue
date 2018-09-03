<template>
    <section>
        <section class="period" v-for="declarations,p in periods">
            <h2>Période : <strong>{{ p | monthyear}}</strong></h2>

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

                    <section class="days">
                        <div class="label">&nbsp;</div>
                        <div class="day" v-for="i in nbrDays">
                            {{ i }}
                        </div>
                    </section>
                    <section v-for="lot, wpCode in activity.details">
                        <section class="days">
                            <div class="label"> {{ lot.label }}</div>
                            <div class="day" v-for="i in nbrDays" :class="{'empty': !lot.days[i]}">
                                {{ lot.days[i] ? lot.days[i] : '0.0' }}
                            </div>
                            <div>{{ lot.total }}</div>
                        </section>
                    </section>
                </section>

                <section class="otherProjects">
                    <h3>Autres projets</h3>
                    <section v-for="otherProject in datas.projects">
                        <section class="days">
                            <div class="label">{{ otherProject.code }}</div>
                            <div class="day" v-for="i in nbrDays" :class="{'empty': !otherProject.days[i]}">
                                {{ otherProject.days[i] ? otherProject.days[i] : '0.0' }}
                            </div>
                            <div>{{ otherProject.total }}</div>
                        </section>
                    </section>
                </section>

                <section class="other">
                    <h3>Autres</h3>

                    <section class="days" v-for="other in datas.others">
                        <div class="label">{{ other.label }}</div>
                        <div class="day" v-for="i in nbrDays" :class="{'empty': !other.days[i]}">
                            {{ other.days[i] ? other.days[i] : '0.0' }}
                        </div>
                        <div>{{ other.total }}</div>
                    </section>

                </section>

                <section class="total">
                    <h3>Total pour cette période</h3>
                    <section class="days">
                        <div class="label">Total</div>
                        <div class="day" v-for="i in nbrDays" :class="{'empty': !datas.total[i]}">
                            {{ datas.total[i] ? datas.total[i] : '0.0' }}
                        </div>
                        <div>
                            {{ datas.totalFull }}
                        </div>
                    </section>

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
    .days:nth-child(odd){
        background-color: rgba(255,255,255,.2);
    }
    .days {
        border-bottom: thin rgba(255,255,255,.25) solid;
        background-color: rgba(255,255,255,.5);
        display: flex;}
        .days .day {
            color: black;
            font-weight: 600;
            flex: 1}
        .days .day {
            padding: .5em}
        .days .day.empty {
            font-weight: 100;
            color: rgba(0,0,0,.5);}
        .days .label {
            padding: .5em;
            font-size: 100%;
            color: black;
            flex: 0 0 150px;}
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
