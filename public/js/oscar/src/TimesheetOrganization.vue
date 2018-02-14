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
        <div class="timesheet">

            <div class="activity">
                Activité
                <input v-model="filtreActivite" class="form-control" placeholder="Filtrer sur les activités" />
            </div>

            <div class="wp">
                Lot de travail
            </div>

            <div class="date">
                Créneau
            </div>


            <div class="declarant">
                Déclarant
                <input v-model="filtreDeclarant" class="form-control" placeholder="Filtrer sur les déclarants" />
            </div>

            <div>
                Validation scientifique
            </div>

            <div>
                Validation administrative
            </div>
        </div>
        <table class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th rowspan="2">
                    <i class="icon-cube"></i>
                    Activité

                </th>

                <th rowspan="2">
                    <i class="icon-archive"></i>
                    Lot de travail</th>

                <th rowspan="2">
                    <i class="icon-calendar"></i>
                    Jour</th>

                <th rowspan="2">
                    <i class="icon-clock"></i>
                    Horaire</th>

                <th rowspan="2">
                    <i class="icon-stopwatch"></i>
                    Durée</th>

                <th rowspan="2">
                    <i class="icon-user"></i>
                    Déclarants

                </th>

                <th colspan="2">Validation</th>
            </tr>
            <tr>
                <th>Scientfique</th>
                <th>Administrative</th>
            </tr>
            </thead>
        </table>
            <section class="timesheets">
            <transition-group name="list" tag="article">
                <timesheet v-for="timesheet in filteredTimesheets" :timesheet="timesheet" :key="timesheet.id"
                           @validsci="$emit('validsci', $event)"
                           @validadm="$emit('validadm', $event)"
                           @rejectsci="$emit('rejectsci', $event)"
                           @rejectadm="$emit('rejectadm', $event)"
                />
            </transition-group>
            </section>
    </section>
</template>

<script>
    import Timesheet from './Timesheet.vue';

    export default {
        props: ['timesheets', 'label', 'role'],
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

