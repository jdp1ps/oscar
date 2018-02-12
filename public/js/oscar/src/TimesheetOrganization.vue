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
        <table class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th rowspan="2">
                    <i class="icon-cube"></i>
                    Activité
                    <input v-model="filtreActivite" class="form-control" placeholder="Filtrer sur les activités" />
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
                    <input v-model="filtreDeclarant" class="form-control" placeholder="Filtrer sur les déclarants" />
                </th>

                <th colspan="2">Validation</th>
            </tr>
            <tr>
                <th>Scientfique</th>
                <th>Administrative</th>
            </tr>
            </thead>
            <tbody>
            <timesheet v-for="timesheet in filteredTimesheets" :timesheet="timesheet" :key="timesheet.id"
                       @validsci="$emit('validsci', $event)"
                       @validadm="$emit('validadm', $event)"
                       @rejectsci="$emit('rejectsci', $event)"
                       @rejectadm="$emit('rejectadm', $event)"
            />
            </tbody>
        </table>
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

<style scoped>
    table {
        background: rgba(255,255,255,0.9);
    }
    thead {
        background: #0a3783;
        text-align: center;
        vertical-align: middle;
        color: white;
        text-shadow: -1px 1px 0 rgba(0,0,0,.3);

    }
    tbody tr:nth-child(odd){
        background: rgba(0,0,0,.05);
    }
    th {
        text-align: center;
        vertical-align: middle;
        border: thin solid rgba(255,255,255,.25);
        font-weight: 100;
    }
    td nav {
        white-space: nowrap;
    }
</style>