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
      <h2 class="card-title">
        <span class="profile">
          <img :src="'//www.gravatar.com/avatar/' + entry.emailmd5 +'?s=80'" alt="" width="40" style="width: 40px"/>
        </span>
        <span>
          <strong>{{ entry.fullname }}</strong>
          <small style="display: block">
            <i class="icon-mail"></i>
            {{ entry.email }}
          </small>
        </span>
        <span v-if="entry.url_show">
          <a :href="entry.url_show">
            Fiche
          </a>
        </span>
      </h2>
      <div class="row">
        <div class="col-md-3">
          <div class="validators">
            <h4 style="margin-top: 1em">
              <i class="icon-user"></i>
              Validateur(s) impliqué(s) :
            </h4>
            <div class="alert alert-danger" v-if="entry.np1.length == 0"><i class="icon-attention-1"></i>
              Aucun <em>N+1</em> (validateur hors-lot) n'est assigné à ce déclarant, vous pouvez lui en assigner un (ou
              plusieurs) depuis la fiche personne.
            </div>

            <div v-if="entry.send == true && entry.validators.length == 0" class="alert alert-danger">
              <i class="icon-attention-1"></i>
              Aucun validateur n'est désigné pour valider ces déclarations, rendez-vous dans la fiche activité afin de
              vérifier que des validateurs sont bien désignés.
            </div>
            <ul v-else>
              <li v-for="v in entry.validators"><i class="icon-cube"></i> {{ v }}</li>
              <li v-for="v in entry.np1">
                <i class="icon-tag"></i>
                <strong>{{ v }}</strong>
                <small> (Validateur Hors-Lot)</small>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-md-9">
          <div class="periods">
            <h3>
              <i class="icon-calendar"></i>
              {{ entry.total_periods }} Période(s) en retard
              <small v-if="entry.total_declarations">
                (dont <strong>{{ entry.total_declarations }}</strong> en cours de traitement)
              </small>
            </h3>
            <div v-for="(p, key) in entry.periods" class="xs highdelay-line" :class="{
          'error ': p.conflict,
          'success': p.valid == true,
          'danger': p.send == false && p.conflict != true,
          'secondary2': p.send == true && p.valid_prj == true && p.valid_sci == false,
          'warning': p.conflict != true && (p.send == true && p.valid_prj == true && p.valid_sci == true),
        }" @click.shift="debug = p">

          <span class="state-info">
            <span v-if="p.valid == true">
              <span class="compact-state"><i class="icon-ok-circled" title="Validée"></i>
                <span class="info-text">Période validée</span>
              </span>
            </span>
            <span v-else-if="p.conflict == true">
              <span class="compact-state"><i class="icon-hammer" title="Conflit à régler (demandeur)"></i>
                <span class="info-text">La période a un conflit</span>
              </span>
            </span>
            <span v-else-if="p.send == false">
              <span class="compact-state"><i class="icon-paper-plane" title="En attente d'envoi"></i>
                <span class="info-text">Pas encore envoyée</span>
              </span>
            </span>
            <span v-else-if="p.valid_prj == true && p.valid_sci == false">
              <span class="compact-state"><i class="icon-beaker" title="En attente de validation scientifique"></i>
                <span class="info-text">En attente de validation scientifique</span>
              </span>
            </span>
            <span v-else-if="p.valid_sci == true && p.valid_adm == false">
              <span class="compact-state"><i class="icon-book" title="En attente de validation administrative"></i>
                <span class="info-text">En attente de validation administrative</span>
              </span>
            </span>
          </span>

              <span class="period-label">{{ key | period }}</span>

              <span>
                Validation : <small style="font-weight: bold">({{ p.step }} / 3)</small>
                <span class="danger" v-if="p.send == true && p.validators.length == 0">
                  <i class="icon-attention-1"></i> Pas de validateur
                </span>
              </span>
            </div>
          </div>
        </div>
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
    period(key) {
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