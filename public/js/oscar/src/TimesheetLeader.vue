<template>
    <div class="timesheetleaderarea">

        <transition name="fade">
            <div class="pending overlay" v-if="pending">
                <div class="overlay-content">
                    <i class="icon-spinner animate-spin"></i>
                    {{ pendingMsg }}
                </div>
            </div>
        </transition>

        <h1>Déclarations en attente de validation</h1>

        <div class="alert-danger alert" v-show="error">
            <i class="icon-warning-empty"></i>
            {{ error }}
        </div>

        <div class="overlay" v-if="rejectModal">
            <div class="overlay-content">
                <p>Indiquez la raison du rejet</p>

                <textarea class="form-control" v-model="rejectMsg"></textarea>

                <nav>
                    <button @click="handlerCancelReject">
                        Annuler
                    </button>
                    <button @click="handlerSubmitReject">
                        Envoyer
                    </button>
                </nav>
            </div>
        </div>

        <div v-if="timesheets.length == 0">
            <div class="alert alert-warning">
                <i class="icon-attention-1"></i>
                Vous n'êtes pas associé à une organisation
            </div>
        </div>
        <div v-else>
            <section v-for="group in structuredTimesheets" class="organization-timesheets" v-if="group.timesheets.length">

                <timesheetorganization :timesheets="group.timesheets" :label="group.label" :role="group.role"
                       :moment="moment"
                       @validsci="handlerValidSci"
                       @validadm="handlerValidAdm"
                       @rejectsci="handlerRejectSci"
                       @rejectadm="handlerRejectAdm"
                />

            </section>
        </div>
    </div>
</template>

<script>
    import TimesheetOrganization from './TimesheetOrganization.vue'

    /**
     *
     */
    export default {
        name: "timesheetleader",
        props: {
            moment: {
                required: true
            },
            validsci: {
                default: false
            },
            validadm: {
                default: false
            }
        },
        data(){
          return {
              error: null,
              group: 'organization',
              subgroup: 'activity_label',
              pending: false,
              pendingMsg: "Chargement des données",
              timesheets: [],

              rejectModal: false,
              rejectType: null,
              rejectTimesheet: null,
              rejectMsg: "",

              filtreDeclarant: "",

              groups: {
                'organization': "Par organization",
                'owner': 'Par déclarant'
              }
          }
        },

        components: {
            'timesheetorganization': TimesheetOrganization
        },

        computed: {
            structuredTimesheets(){
                if( this.filtreDeclarant )
                    return this.timesheets.filter( (value) => {
                        console.log(JSON.parse(JSON.stringify(value)));
                        return value.owner.indexOf(this.filtreDeclarant) > -1
                    })
                return this.timesheets;
            }
        },

        methods: {
            fetch(){
                this.pending = true;
                this.$http.get().then(
                  response => {
                      this.timesheets = response.data;
                  },
                  fail => {
                    console.error('error', fail);
                  }
                ).then(
                    foo => {
                        this.pending = false;
                    }
                );
            },

            refreshTimesheet(timesheet){
                console.log('Refresh timesheet', timesheet);
                for( let i=0; i<this.timesheets.length; i++ ){
                    for( let j=0; j<this.timesheets[i].timesheets.length; j++ ){
                        console.log('Refresh timesheet', this.timesheets[i].timesheets[j].id , ' < ', timesheet.id);

                        if( this.timesheets[i].timesheets[j].id == timesheet.id ){
                            if( timesheet.status == 'reject' ){
                                this.timesheets[i].timesheets.splice(j, 1);
                            } else {
                                this.timesheets[i].timesheets.splice(j, 1, timesheet);
                            }
                            return;
                        }
                    }
                }
            },


            send(action, timesheet){
                this.pending = true;
                this.$http.post('', { 'action': action, 'timesheet_id': timesheet.id}).then(
                    success => {
                        let updateTimesheet = success.data[0];
                        this.refreshTimesheet(updateTimesheet);
                    },
                    error => {
                        this.error = error.body ? error.body : "Erreur : " + error.statusText;
                    }
                ).then( () => this.pending = false )
            },

            handlerValidSci(timesheet){
                this.send('validatesci', timesheet);
            },

            handlerValidAdm(timesheet){
                this.send('validateadm', timesheet);
            },

            handlerRejectSci(timesheet){
                this.rejectModal = true;
                this.rejectType = 'rejectsci';
                this.rejectTimesheet = timesheet;
            },

            handlerRejectAdm(timesheet){
                this.rejectModal = true;
                this.rejectType = 'rejectadm';
                this.rejectTimesheet = timesheet;
            },

            handlerCancelReject(){
                this.rejectType = '';
                this.rejectTimesheet = null;
                this.rejectModal = false;
            },

            /**
             * Envoi du rejet
             */
            handlerSubmitReject(){
                this.pending = true;
                this.$http.post('', {
                    'action': this.rejectType,
                    'timesheet_id': this.rejectTimesheet.id,
                    'rejectComment' : this.rejectMsg
                }).then(
                    success => {
                        this.fetch();
                    },
                    error => {
                        this.error = error.body ? error.body : "Erreur : " + error.statusText;
                    }
                ).then( () => {
                    this.pending = false;
                    this.rejectModal = false;
                }  )
            }
        },

        mounted(){
            this.fetch()
        }
    }
</script>