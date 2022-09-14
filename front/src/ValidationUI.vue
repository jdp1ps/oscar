<template>
  <section>

    <div class="overlay" v-if="reject_dt" style="z-index: 2000">
      <div class="overlay-content">
        <h3>
          Rejet de la période <strong>{{ reject_dt.period_label }}</strong>
        </h3>

        <a href="" class="overlay-closer" @click.prevent="reject_dt = null;">X</a>

        <p class="alert alert-info">
          <i class="icon-info-circled"></i>
          Merci de renseigner la raison du rejet pour que le déclarant (<strong>{{ reject_dt.declarer }}</strong>)
          puisse corriger sa déclaration.
        </p>
        <form action="" method="post" class="form-inline">
          <input type="hidden" name="period" :value="reject_dt.period"/>
          <input type="hidden" name="action" value="reject"/>
          <input type="hidden" name="declarer" :value="reject_dt.declarer_id"/>

          <textarea name="message" id="" cols="30" rows="10" v-model="reject_dt.message"
                    class="form-control"></textarea>

          <nav class="buttons-bar">
            <button class="btn btn-default" @click.prevent="reject_dt=null">
              Annuler
            </button>
            <button class="btn btn-danger" type="submit">
              <i class="icon-cancel-alt"></i>
              Rejeter
            </button>
          </nav>
        </form>

      </div>
    </div>

    <div class="overlay" v-if="debug_dt" style="z-index: 2000">
      <div class="overlay-content">
        <a href="" class="overlay-closer" @click.prevent="debug_dt = null;">X</a>
        <pre>{{ debug_dt }}</pre>
      </div>
    </div>

    <div class="overlay" v-if="loadingDetails || details">
      <div class="overlay-content">
        <a href="" class="overlay-closer" @click.prevent="loadingDetails = null; details = null">X</a>
        <div v-if="loadingDetails">
          Chargement des données
        </div>
        <div v-else>
          <h3>{{ details.validation.infos.period.periodLabel }} /
            <strong>{{ details.validation.infos.declarer.displayname }}</strong>
          </h3>
          <header class="validation-area">
            <div class="line heading">
              <span class="line-head">
                Lots
              </span>
              <span class="header-days day" v-for="dinf, d in headerDays"
                    style="display: inline-block; text-align: center ">
                <small>{{ dinf.label }}</small><br>
                <strong>{{ d }}</strong><br>
              </span>
              <span>
                <i class="icon-valid"></i>
              </span>
              <span class="total">
                Total
              </span>
            </div>
          </header>
          <section class="validation-area research">
            <!-- Activités -->
            <section class="activity" v-for="a in details.validation.datas.activities">

              <!-- HEAD -->
              <section class="line" style="border-bottom: solid thin rgba(255,255,255,.3)">
                <strong class="line-head">{{ a.activity_acronym }}</strong>
                <span v-for="dinf, d in headerDays" @click.ctrl="debug(a.by_days[d])">
                  <span class="none">&nbsp;</span>
                </span>
                <span class="none">
                  &nbsp;
                </span>
                <span class="total" :class="a.total > 0 ? 'valued' : 'none'">
                &nbsp;
              </span>
              </section>

              <!-- Infos -->
              <section class="line" v-for="w in a.details_workspackages">
                <strong class="line-head">{{ w.workpackage_code }}</strong>
                <span v-for="dinf, d in headerDays" @click.ctrl="debug(dinf)">
                  <strong v-if="w.by_days[d]" class="valued">{{ w.by_days[d] | duration }}</strong>
                  <span v-else class="none">0</span>
                </span>
                <span class="none">
                  &nbsp;
                </span>
                <span class="total" :class="w.workpackage_total > 0 ? 'valued' : 'none'">
                {{ w.workpackage_total | duration }}
              </span>
              </section>

              <!-- FOOT -->
              <section class="line" style="border-bottom: solid thin rgba(255,255,255,.3)">
                <strong class="line-head">&nbsp;</strong>
                <span v-for="dinf, d in headerDays" @click.ctrl="debug(a.by_days[d])">
                  <span class="none">&nbsp;</span>
                </span>
                <span>
                  <i class="icon" :class="'icon-' + a.status"></i>
                </span>
                <span class="total" :class="a.total > 0 ? 'valued' : 'none'">
                {{ a.total | duration }}
              </span>
              </section>

            </section>
          </section>
          <section class="validation-area research" v-if="details.validation.datas.horslots.research">
            <section v-for="hl, hl_code in details.validation.datas.horslots.research.subs" class="line">
              <strong class="line-head">{{ hl.label }}</strong>
              <span v-for="dinf, d in headerDays" @click.ctrl="debug(hl.by_days[d])">
                <strong v-if="hl.by_days[d]" class="valued">{{ hl.by_days[d] | duration }}</strong>
                <span v-else class="none">0</span>
              </span>
              <span>
                  <i class="icon" :class="'icon-' + hl.status"></i>
              </span>
              <span class="total" :class="hl.total > 0 ? 'valued' : 'none'">
                {{ hl.total | duration }}
              </span>
            </section>
          </section>

          <section class="validation-area" :class="g" v-if="g != 'research'"
                   v-for="g_info, g in details.validation.datas.horslots">
            <section v-for="hl, hl_code in g_info.subs" class="line">
              <strong class="line-head">{{ hl.label }}</strong>
              <span v-for="dinf, d in headerDays" @click.ctrl="debug(hl)">
                <strong v-if="hl.by_days[d]" class="valued">{{ hl.by_days[d] | duration }}</strong>
                <span v-else class="none">0</span>
              </span>
              <span>
                  <i class="icon" :class="'icon-' + hl.status"></i>
              </span>
              <span class="total" :class="hl.total > 0 ? 'valued' : 'none'">
                {{ hl.total | duration }}
              </span>
            </section>
          </section>

          <footer class="validation-area">
            <div class="line heading">
              <span class="line-head">
                Total
              </span>
              <span class="footer-days day" v-for="dinf, d in headerDays" @click.ctrl="debug(dinf)">
                <strong v-if="dinf.duration" class="valued">{{ dinf.duration | duration }}</strong>
                <span v-else class="none">0</span>
              </span>
              <span>
                &nbsp;
              </span>
              <span class="total">
                {{ details.validation.datas.total | duration }}
              </span>
            </div>
          </footer>

          <hr>
          <nav class="text-center">
            <button class="btn btn-danger" @click="handlerReject(details)">
              <i class="icon-cancel-alt"></i>
              Refuser
            </button>
            <form action="" method="post" class="form-inline">
              <input type="hidden" name="period" :value="details.period"/>
              <input type="hidden" name="action" value="validate"/>
              <input type="hidden" name="declarer" :value="details.declarer"/>
              <button class="btn btn-success" type="submit">
                <i class="icon-valid"></i>
                Valider la déclaration pour cette période
              </button>
            </form>
          </nav>
        </div>
      </div>
    </div>


    <div class="row" v-if="synthesis">

      <!------------------------------------------------------------------------------------------------------------ -->
      <div class="col-md-3">
        FILTRES :
        <strong class="cartouche" v-if="selectedPerson" @click="handlerRemoveSelectedPerson()">
          <i class="icon-user"></i>
          {{ selectedPerson.fullname }}
          <i class="icon-trash"></i>
        </strong>

        <strong class="cartouche" v-if="selectedActivity" @click="handlerRemoveSelectedActivity()">
          <i class="icon-cubes"></i>
          {{ selectedActivity.name }}
          <i class="icon-trash"></i>
        </strong>

        <hr>

        <!-- Filtres DECLARANTS -->
        <h4>
          <i class="icon-user"></i>
          Déclarants</h4>
        <article v-for="p in categories.persons"
                 class="card xs card-synthesis clickable"
                 :class="{'light' : p.light }"
                 @click="handlerAddSelectedPerson(p)">
            <span>
              <i class="icon-hourglass-3" v-if="p.completed == false"></i>
              <i class="icon-valid text-success" v-else></i>
              <strong>{{ p.declarer_fullname }}</strong>
              <small> ({{ p.declarer_affectation }})</small>
            </span>

          <span class="cartouche card-synthesis-left" :class="{'green': p.unvalidated == 0}">
              {{ p.total - p.unvalidated }} / {{ p.total }}
            </span>
        </article>

        <!-- Filtres ACTIVITES -->
        <h4>
          <i class="icon-cubes"></i>
          Projets</h4>
        <article v-for="p in categories.activities"
                 @click="handlerAddSelectedActivity(p)"
                 class="card xs">
          <strong>{{ p.activity_acronym }}</strong>
          <span class="cartouche" :class="{'green': p.unvalidated == 0}">
              {{ p.total - p.unvalidated }} / {{ p.total }}
            </span>
        </article>
      </div>
      <!------------------------------------------------------------------------------------------------------------ -->
      <div class="col-md-9">
        <section v-for="year, year_label in stackedDatas">
          <h4>
            Année {{ year_label }}
          </h4>

          <article v-for="vp in year.validations" class="card card-synthesis line"
                   v-if="selectedActivity == null || vp.activity_in.indexOf(selectedActivity.id) >= 0"
                   :class="'validation-step-' + vp.statusKey"
                   @click.ctrl="debug(vp)">
              <i :class="'icon-' + vp.statusKey" :title="vp.statusText"></i>
              <span class="declarer">
                <strong class="period">{{ vp.label | period }}</strong>
                <span class="person">
                  <i class="icon-user"></i>
                  <em>{{ vp.declarer_fullname }}</em>
                </span>
              </span>
              <span v-if="vp.projects">
                <span v-for="p in vp.projects" class="cartouche">
                  <i class="icon-cubes"></i>
                  {{ p }}
                </span>
              </span>
              <span v-if="vp.statusKey != 'conflict' && vp.statusKey != 'valid' ">
                Validateurs : <span class="cartouche" v-for="v in vp.validators">{{ v }}</span>
              </span>
              <span v-if="vp.validable" class="buttons">
                <button class="btn btn-default btn-info" @click="handlerDetail(vp)">
                  <i class="icon-zoom-in-outline"></i>
                  Vérifier et valider
                </button>
              </span>
          </article>
        </section>
        <hr>

      </div>
    </div>
  </section>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

node node_modules/.bin/vue-cli-service build --name ValidationUI --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/ValidationUI.vue

 */

export default {

  props: {
    url: {required: true}
  },

  data() {
    return {
      synthesis: null,
      filter: null,
      group: 'person',
      tabopen: 'person',
      selectedPerson: null,
      selectedActivity: null,
      details: null,
      loadingDetails: null,
      selectedPeriod: null,
      debug_dt: null,
      reject_dt: null
    }
  },

  computed: {
    headerDays() {
      let headers = [];
      return this.details.validation.infos.days;
    },

    stackedDatas() {

      let statutOrder = {
        'error': 0,
        'conflict': 1,
        'send-prj': 2,
        'send-sci': 3,
        'send-adm': 4,
        'valid': 5
      };

      let statutTextOrder = [
        "Erreur technique",
        "En conflit",
        "Validation projet",
        "Validation scientifique",
        "Validation administrative",
        "Validé"
      ];

      let statutKeys = [
        "error",
        "conflict",
        "send-prj",
        "send-sci",
        "send-adm",
        "valid"
      ];

      let out = {};
      this.synthesis.forEach(e => {

        let declarer_id = e.declarer_id;
        let activity_id = e.activity_id;
        let project = e.activity_acronym;
        let spt = e.period.split('-');
        let year = spt[0];
        let month = spt[1];
        let period = e.period;
        let validable = e.validable;
        let declarer = e.declarer_fullname;
        let period_person_agg = period + '-' + declarer_id;
        let statutInt = statutOrder[e.statut];
        let statutText = statutTextOrder[statutInt];
        let statutKey = statutKeys[statutInt];

        if (this.selectedPerson && this.selectedPerson.id != declarer_id) {
          return;
        }

        if (!out[year]) {
          out[year] = {
            'label': year,
            'validations': {}
          };
        }
        if (!out[year].validations[period_person_agg]) {
          out[year].validations[period_person_agg] = {
            'label': period,
            'declarer_id': declarer_id,
            'activity_in': [],
            'projects': [],
            'period': period,
            'declarer_fullname': declarer,
            'validable': false,
            'validators': [],
            'status' : '',
            'statusText' : '',
            'statusKey' : '',
            'statusInt' : 5,
            'validations': []
          };
        }

        if( activity_id ){
          out[year].validations[period_person_agg].activity_in.push(activity_id);
        }

        if (validable == true) {
          out[year].validations[period_person_agg].validable = true;
        }

        // Gestion des statut calculés
        console.log(statutInt, ' /// ', out[year].validations[period_person_agg].statusInt);
        statutInt = Math.min(statutInt, out[year].validations[period_person_agg].statusInt);
        statutText = statutTextOrder[statutInt];
        statutKey = statutKeys[statutInt];

        out[year].validations[period_person_agg].validations.push(e);
        e.validators.forEach(validator => {
          if( out[year].validations[period_person_agg].validators.indexOf(validator) < 0 ){
            out[year].validations[period_person_agg].validators.push(validator);
          }
        });

        if( project && out[year].validations[period_person_agg].projects.indexOf(project) == -1 ){
          out[year].validations[period_person_agg].projects.push(project);
        }

        out[year].validations[period_person_agg].statusText = statutText;
        out[year].validations[period_person_agg].statusInt = statutInt;
        out[year].validations[period_person_agg].statusKey = statutKey;
//        out[year].validations[period_person_agg].validators = out[year].validations[period_person_agg].validators.concat(e.validators);

      });
      return out;
    },

    categories() {
      let out = {
        'persons': {},
        'activities': {},
        'periodes': {}
      };
      if (this.synthesis) {
        this.synthesis.forEach(e => {
          let declarer_id = e.declarer_id;
          let activity_id = e.activity_id;
          let spt = e.period.split('-');
          let year = spt[0];
          let month = spt[1];
          let validable = e.validable;
          let light = false;

          if (this.selectedPerson && this.selectedPerson.id != declarer_id) {
            light = true;
          }

          // Pack persons
          if (!out.persons[declarer_id]) {
            out.persons[declarer_id] = {
              declarer_id: declarer_id,
              declarer_fullname: e.declarer_fullname,
              declarer_affectation: e.declarer_affectation,
              total: 0,
              unvalidated: 0,
              light: light,
              completed: true
            }
          }
          out.persons[declarer_id].total += 1;
          if (validable) {
            out.persons[declarer_id].unvalidated += 1;
            out.persons[declarer_id].completed = false;
          }

          // --- Pack activity
          if (!out.activities[activity_id]) {
            out.activities[activity_id] = {
              activity_id: activity_id,
              activity_acronym: e.activity_acronym,
              total: 0,
              unvalidated: 0,
              completed: true
            }
          }
          out.activities[activity_id].total += 1;
          if (validable) {
            out.activities[activity_id].unvalidated += 1;
            out.activities[activity_id].completed = false;
          }

        })
      }
      return out;
    }
  },

  methods: {

    /** Selection des détails à afficher **/
    handlerDetail(p) {
      this.selectedPeriod = {
        'declarer_id': p.declarer_id,
        'period': p.period
      };
      this.fetchDetails();
    },

    /** Selection d'une personne (Filtre les lignes de déclaration **/
    handlerAddSelectedPerson(declarer) {
      if (this.selectedPerson && this.selectedPerson.id == declarer.declarer_id) {
        this.selectedPerson = null;
        this.details = null;
      } else {
        this.selectedPerson = {
          id: declarer.declarer_id,
          fullname: declarer.declarer_fullname
        };
      }
    },

    /** Selection d'une personne (Filtre les lignes de déclaration **/
    handlerAddSelectedActivity(activity) {
      console.log(activity);
      if (this.selectedActivity && this.selectedActivity.id == activity.id) {
        this.selectedActivity = null;
        this.details = null;
      } else {
        this.selectedActivity = {
          id: activity.activity_id,
          name: activity.activity_acronym
        };
      }
    },

    handlerRemoveSelectedPerson() {
      this.selectedPerson = null;
    },

    handlerRemoveSelectedActivity(){
      this.selectedActivity = null;
    },

    handlerReject(details) {
      this.reject_dt = {
        declarer_id: details.validation.infos.declarer.id,
        declarer: details.validation.infos.declarer.displayname,
        period_label: details.validation.infos.period.periodLabel,
        period: details.validation.infos.period.periodCode,
        message: ""
      }
    },

    fetchDetails() {
      this.loadingDetails = true;
      this.$http.get(
          '?action=details&declarer_id=' + this.selectedPeriod.declarer_id + '&period=' + this.selectedPeriod.period
      ).then(
          ok => {
            this.loadingDetails = false;
            this.details = ok.data;
            console.log("tutu")
          },
          ko => {

          }
      );
    },

    fetch() {
      this.$http.get(this.url).then(
          ok => {
            this.synthesis = ok.data.synthesis;
            console.log(ok);
          },
          ko => {
            console.log(ko)
          })
    },

    parseLocationDatas() {
      if (window.location.hash) {
        let split = window.location.hash.substring(1).split('-');
        if (split.length == 3) {
          this.handlerDetail({
            'declarer_id': split[2],
            'period': split[0] + '-' + split[1]
          })
        }
      }
    },

    debug(variable) {
      this.debug_dt = variable;
    }
  },

  mounted() {
    this.parseLocationDatas();
    this.fetch();
  }

}
</script>