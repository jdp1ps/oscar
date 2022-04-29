<template>
  <div class="highdelay">
    <article class="card" v-for="entry in entries">
      <h2 class="card-title">{{ entry.fullname }}</h2>
      <div>
        <i class="icon-calendar"></i> Nombre de période : <strong>{{ entry.total_periods }}</strong><br>
        <i class="icon-docs"></i> Déclaration(s) : <strong>{{ entry.total_declarations }}</strong>
      </div>
      <div class="periods">
        <span v-for="(p, key) in entry.periods" class="cartouche xs" :class="{'success': p.valid == true, 'danger': p.conflict == true}">
          <span v-if="p.valid == true"><i class="icon-ok-circled"title="Validée"></i></span>
          <span v-else-if="p.conflict == true"><i class="icon-hammer"title="Conflit à régler (demandeur)"></i></span>
          <span v-else-if="p.send == false" ><i class="icon-paper-plane" title="En attente d'envoi"></i></span>
          <span v-else><i class="icon-hourglass-3" title="En attente de validation"></i></span>
          {{ key }}
        </span>
      </div>
    </article>
  </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :
cd front
Pour compiler en temps réél :
node node_modules/.bin/vue-cli-service build --name TimesheetHighDelay --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/TimesheetHigDelay.vue --watch
 */


export default {
  props: {
    url: {required: true, default: ''}
  },

  data() {
    return {
      /** Liste des données (Personne,periode) **/
      entries: []
    }
  },

  methods: {

    fetch() {
      console.log("fetch()");
      this.$http.get(this.url).then(
          ok => {
            this.entries = ok.data.highdelays;
          },
          ko => {
            console.log(ko);
          }
      )
    }
  },

  mounted() {
    console.log("MOUNTED");
    this.fetch();
  }

}
</script>