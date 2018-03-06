<template>
    <section class="timesheet-organization">
        <h2><i class="icon-building-filled"></i>{{ label }} ({{ role }})</h2>
        <p v-show="filtreDeclarant || filtreActivite">
            Déclaration pour les déclarants correspondant à la recherche
            <span class="cartouche" v-show="filtreDeclarant"><i class="icon-user"></i>{{ filtreDeclarant }}
                <i class="icon-cancel-circled-outline" @click="filtreDeclarant = ''"></i>
                </span>
            <span class="cartouche" v-show="filtreActivite"><i class="icon-cube"></i>{{ filtreActivite }}
                <i class="icon-cancel-circled-outline" @click="filtreActivite = ''"></i>
                </span>
        </p>

        <div class="alert alert-info" v-if="!timesheets.length">
            Il n'y a pas de déclaration en attente pour <strong>{{ label }}</strong>
        </div>
        <div v-else>
            <div class="timesheet timesheet-header">
                <div class="activity">
                    <i class="icon-cube"></i>
                    Activité
                    <input v-model="filtreActivite" class="form-control input-sm" placeholder="Filtrer sur les activités" />
                </div>

                <div class="wp">
                    <i class="icon-archive"></i>
                    Lot de travail
                </div>

                <div class="date">
                    <i class="icon-calendar"></i>
                    Créneau
                </div>


                <div class="declarant">
                    <i class="icon-user"></i>
                    Déclarant
                    <input v-model="filtreDeclarant" class="form-control input-sm" placeholder="Filtrer sur les déclarants" />
                </div>

                <div>
                    Validation scientifique
                </div>

                <div>
                    Validation administrative
                </div>
            </div>

            <section class="timesheets">
                <transition-group name="list" tag="article">
                    <timesheet v-for="timesheet in filteredTimesheets" :timesheet="timesheet" :key="timesheet.id"
                               :moment="moment"
                               @validsci="$emit('validsci', $event)"
                               @validadm="$emit('validadm', $event)"
                               @rejectsci="$emit('rejectsci', $event)"
                               @rejectadm="$emit('rejectadm', $event)"
                    />
                </transition-group>
            </section>
        </div>
    </section>
</template>

<script>
    import Timesheet from './Timesheet.vue';

    export default {
        props: ['timesheets', 'label', 'role', 'moment'],
        components: {
            'timesheet': Timesheet
        },
        computed: {
          filteredTimesheets(){
              if( this.filtreDeclarant || this.filtreActivite){
                  let fitered = [];

                  this.timesheets.forEach( item => {
                      if( this.filtreDeclarant && item.owner.toLowerCase().indexOf(this.filtreDeclarant.toLowerCase()) < 0 )
                          return;

                      if( this.filtreActivite && !(
                          item.activity_label.toLowerCase().indexOf(this.filtreActivite.toLowerCase()) > -1 ||
                          item.project_acronym.toLowerCase().indexOf(this.filtreActivite.toLowerCase()) > -1 ) )
                          return;

                      fitered.push(item)
                  })

                  return fitered;
              }
              return this.timesheets;
          }
    },
        data(){
            return {
                filtreDeclarant: '',
                filtreActivite: ''
            }
        }
    }
</script>

