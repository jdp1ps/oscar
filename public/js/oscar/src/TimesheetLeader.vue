<template>
    <div class="timesheetleaderarea">
        <div class="pending" v-if="pending">pending</div>
        <h1>Déclarations en attente de validation</h1>

        <div class="alert-danger alert" v-show="error">
            <i class="icon-warning-empty"></i>
            {{ error }}
        </div>

        <section v-for="group in structuredTimesheets" class="organization-timesheets" v-if="group.timesheets.length">
            <h2><i class="icon-building-filled"></i>{{ group.label }} ({{ group.role }})</h2>
            <timesheet v-for="timesheet in group.timesheets" :timesheet="timesheet" :key="timesheet.id"
                       @validsci="handlerValidSci"
                       @validadm="handlerValidAdm"
                       @rejectsci="handlerRejectSci"
                       @rejectadm="handlerRejectSci"
            />
        </section>
    </div>
</template>

<script>
    import Timesheet from './Timesheet.vue'

    /**
     *
     */
    export default {
        name: "timesheetleader",
        props: {
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
              timesheets: [],
              groups: {
                'organization': "Par organization",
                'owner': 'Par déclarant'
              }
          }
        },

        components: {
            'timesheet': Timesheet
        },

        computed: {
            structuredTimesheets(){
                /*let datas = {};
                this.timesheets.forEach( timesheet => {

                    let grouper = timesheet[this.group],
                        subgrouper = timesheet[this.subgroup];

                    if( !datas.hasOwnProperty(grouper) ){
                        datas[grouper] = {
                            label: grouper,
                            subgroup: {}
                        }
                    }

                    if( !datas[grouper].subgroup.hasOwnProperty(subgrouper) ){
                        datas[grouper].subgroup[subgrouper] = {
                            label: subgrouper,
                            timesheets: []
                        }
                    }

                    datas[grouper].subgroup[subgrouper].timesheets.push(timesheet);
                });
                return datas;
                */
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

            send(action, timesheet){
                this.pending = true;
                this.$http.post('', { 'action': action, 'timesheet_id': timesheet.id}).then(
                    success => {
                       this.fetch();
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
                this.send('rejectsci', timesheet);
            },

            handlerRejectSci(timesheet){
                this.send('rejectadm', timesheet);
            }
        },

        mounted(){
            this.fetch()
        }
    }
</script>