<template>
  <div class="highdelay">
    <div class="overlay" v-if="debug">
      <div class="overlay-content">
        <div class="overlay-closer" @click="debug = null">x</div>
        <pre>
          {{ debug }}
        </pre>
      </div>
    </div>
    <article class="card" v-for="entry in entries">
      <h2>
        {{ entry.fullname }}
        <span class="cartouche xs warning">
          {{ entry.total_declarations }} / {{ entry.total_periods }}
        </span>
      </h2>
<!--      <div>-->
<!--        <i class="icon-calendar"></i> Nombre de période : <strong>{{ entry.total_periods }}</strong><br>-->
<!--        <i class="icon-docs"></i> Déclaration(s) : <strong>{{ entry.total_declarations }}</strong><br>-->


<!--      </div>-->
      <div class="validators">
        <h3>
          <i class="icon-user"></i>
          Validateur(s) impliqué(s) :
        </h3>
        <div class="alert alert-danger" v-if="entry.np1.length == 0"><i class="icon-attention-1"></i>
          Aucun <em>N+1</em> (validateur hors-lot) n'est assigné à ce déclarant, vous pouvez lui en assigner un (ou plusieurs) depuis la fiche personne.
        </div>

        <div v-if="entry.send == true && entry.validators.length == 0" class="alert alert-danger">
          <i class="icon-attention-1"></i>
          Aucun validateur n'est désigné pour valider ces déclarations, rendez-vous dans la fiche activité afin de vérifier que des validateurs sont bien désignés, ainsi que dans la fiche personne pour les créneaux Hors-lot
        </div>
        <ul v-else>
          <li v-for="v in entry.validators"><i class="icon-cube"></i> {{ v }}</li>
          <li v-for="v in entry.np1"><i class="icon-tag"></i> {{ v }}</li>
        </ul>
      </div>
      <div class="periods">
        <h3>
          <i class="icon-calendar"></i>
          Période(s)</h3>
        <span v-for="(p, key) in entry.periods" class="cartouche xs" :class="{
          'error ': p.conflict,
          'success': p.valid == true,
          'danger': p.send == false && p.conflict != true,
          'secondary2': p.send == true && p.valid_prj == true && p.valid_sci == false,
          'warning': p.conflict != true && (p.send == true && p.valid_prj == true && p.valid_sci == true),
        }" @click.shift="debug = p">
          <span v-if="p.valid == true"><i class="icon-ok-circled"title="Validée"></i></span>
          <span v-else-if="p.conflict == true"><i class="icon-hammer"title="Conflit à régler (demandeur)"></i></span>
          <span v-else-if="p.send == false" ><i class="icon-paper-plane" title="En attente d'envoi"></i></span>
          <span v-else-if="p.valid_prj == true && p.valid_sci == false"><i class="icon-beaker" title="En attente de validation scientifique"></i></span>
          <span v-else-if="p.valid_sci == true && p.valid_adm == false"><i class="icon-book" title="En attente de validation administrative"></i></span>
          {{ key | period }}
          <small style="font-weight: bold">({{ p.step }} / 3)</small>
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

const months = [
  '',
  'Janvier',
  'Février',
  'Mars',
  'Avril',
  'Mai',
  'Juin',
  'Juillet',
  'Aout',
  'Septembre',
  'Octobre',
  'Novembre',
  'Décembre',
];

export default {
  props: {
    url: {required: true, default: ''}
  },

  data() {
    return {
      /** Liste des données (Personne,periode) **/
      entries: [],
      debug: null
    }
  },

  filters: {
    period(key){
      let s = key.split('-');
      let m = months[parseInt(s[1])];
      return m + ' ' + s[0];
    }
  },

  methods: {

    fetch() {
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