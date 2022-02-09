<template>
  <div>
    <div class="overlay" v-if="mode != ''">
      <div class="overlay-content" :style="step == 1 ? 'overflow: visible;' : 'overflow: scroll;'">
        <a href="#" @click.prevent="handlerCancel()" class="overlay-closer">X</a>
        <h1>
          {{ mode }}
          <strong>{{ person.displayname }}</strong>
        </h1>

        <div class="alert">
          Cette écran va vous permettre de remplacer <strong>{{ person.displayname }}</strong> par une
          autre personne. Choisissez un remplaçant, un récapitulatif sera affiché avant confirmation.
        </div>

        <div class="steps-bar">
          <div class="step" :class="{'current': step == 1, 'done': step > 1}">
            Choisir une personne
          </div>
          <div class="step" :class="{'current': step == 2, 'done': step > 2, 'futur': step < 2}">
            Vérifier les affectations
          </div>
        </div>

        <div class="step-content">

          <!-- Messages -->
          <div class="alert-info alert" v-if="pendingMessage">
            <i class="animate-spin icon-spinner"></i>
            {{ pendingMessage }}
          </div>

          <div v-if="errorMessage" class="alert-danger alert">
            <i class="icon-attention-1"></i>
            <strong>Erreur</strong> :
            {{ errorMessage }}
          </div>



          <div v-if="step == 1">
            <div v-if="!replacer_id">
              <p>Choisissez une personne :</p>
              <person-auto-completer @change="handlerSelectPerson" style="z-index: 10"/>
            </div>
          </div>

          <div class="alert alert-info" v-if="step1info">
            {{ step1info }}
          </div>

          <nav class="buttons text-center" v-if="step == 1">
            <button class="btn btn-success" @click="handlerConfirmStep1()" :class="{ 'disabled': !replacer_id }" >
              <i class="icon-spinner animate-spin" v-if="pendingMessage"></i>
              <i class="icon-angle-right" v-else></i>
              Vérifier les affectations
            </button>
          </nav>

          <section class="">

            <!-- <h2>INFOS : {{ recap.infos }}</h2> -->
            <section class="affectations" v-if="step == 2">
              <section>
                <h2>
                  <i class="icon-beaker"></i>
                  Activité/projet de recherche
                </h2>

                <section class="projects">
                  <h4><i class="icon-cubes"></i> Projets</h4>
                  <p v-if="replacer_id">
                    <strong>{{ replacer }}</strong>
                    va être ajouté dans le(s) <strong>{{ projects.length }}</strong> projet(s) suivant :
                  </p>
                  <div class="columns-3">
                    <div class="card xs applyable"
                         @click="handlerSwitchApply(p)"
                         v-for="p in projects" :class="{ 'apply': p.apply, 'active': p.active }">
                      <div class="">
                        <strong>
                          <i class="icon-cubes"></i>
                          {{ p.label }}
                        </strong>
                      </div>
                      <span class="cartouche xs" v-for="role in p.roles"
                            :class="{'past obsolete': !role.active, 'success': p.apply}">
                      {{ role.roleId }}
                    </span>
                    </div>
                  </div>
                </section>

                <h4><i class="icon-cube"></i> Activités</h4>
                <div class="columns-3">
                  <div class="card xs applyable"
                       @click="handlerSwitchApply(p)"
                       v-for="p in activities" :class="{ 'apply': p.apply, 'active': p.active }">
                    <div class="">
                      <strong>
                        <i class="icon-cube"></i>
                        {{ p.label }}
                      </strong>
                    </div>
                    <span class="cartouche xs" v-for="role in p.roles"
                          :class="{'past obsolete': !role.active, 'success': p.apply}">
                      {{ role.roleId }}
                    </span>
                  </div>
                </div>
              </section>

              <section>
                <h2>
                  <i class="icon-building-filled"></i>
                  Structure
                </h2>
                <div v-for="p in structures" class="card xs applyable"
                     @click="handlerSwitchApply(p)"
                     :class="{'apply': p.apply }">
                  <strong>{{ p.label }}</strong>
                  <span class="cartouche xs" v-for="role in p.roles"
                        :class="{'past obsolete': !role.active, 'success': p.apply}">
                  {{ role.roleId }}
                </span>
                </div>
              </section>

              <section>
                <h2>
                  <i class="icon-calendar"></i>
                  Validation
                </h2>

                <div v-if="validations.prj">
                  <h4><i class="icon-cube"></i>Validation projet</h4>
                  <div class="card xs applyable"
                       @click="v.apply = !v.apply; console.log('OK')"
                       v-for="v in validations.prj"
                       :class="{'apply': v.apply }">
                    <strong>{{ v.acronym }}</strong>
                    <em>{{ v.label }}</em>
                  </div>
                </div>

                <div v-if="validations.sci">
                  <h4><i class="icon-beaker"></i>Validation scientifique</h4>
                  <div class="card xs applyable"
                       @click="v.apply = !v.apply"
                       v-for="v in validations.sci"
                       :class="{'apply': v.apply }">
                    <strong>{{ v.acronym }}</strong>
                    <em>{{ v.label }}</em>
                  </div>
                </div>

                <div v-if="validations.adm">
                  <h4><i class="icon-book"></i>Validation administratif</h4>
                  <div class="card xs applyable"
                       @click="v.apply = !v.apply"
                       v-for="v in validations.adm"
                       :class="{'apply': v.apply }">
                    <strong>{{ v.acronym }}</strong>
                    <em>{{ v.label }}</em>
                  </div>
                </div>
              </section>

              <section>
                <h2>
                  <i class="icon-calendar"></i>
                  Hiérarchie
                </h2>

                <div>
                  <h4><i class="icon-book"></i>Reférent(s)</h4>
                  <p>
                    <i class="icon-info-circled"></i>
                    Personnes qui seront chargées de valider
                    les déclaration <strong>Hors-lot</strong>
                    de {{ person.displayname }}.
                  </p>
                  <div class="card xs person-card" v-for="p in subordinates" :class="{'apply': p.apply}">
                    <div class="card-title">
                      <img :src="'//www.gravatar.com/avatar/' +p.mailmd5 +'&s=20'" alt="" class="gravatar" width="20">
                      <strong>
                        <i class="icon-user"></i>
                        {{ p.label }}
                      </strong>
                    </div>
                    <small>
                      <i class="icon-mail"></i>
                      {{ p.mail }}
                    </small>
                  </div>
                </div>

                <div>
                  <h4><i class="icon-beaker"></i>Subordonné(s)</h4>
                  <p>
                    <i class="icon-info-circled"></i>
                    Personnes pour qui <strong>{{ person.displayname }}</strong> devra
                    valider les déclarations <strong>Hors-lot</strong>
                  </p>
                  <div class="card xs person-card" v-for="p in referents" :class="{'apply': p.apply}">
                    <div class="card-title">
                      <img :src="'//www.gravatar.com/avatar/' +p.mailmd5 +'&s=20'" alt="" class="gravatar" width="20">
                      <strong>
                        <i class="icon-user"></i>
                        {{ p.label }}
                      </strong>
                    </div>
                    <small>
                      <i class="icon-mail"></i>
                      {{ p.mail }}
                    </small>
                  </div>
                </div>
              </section>

              <nav class="buttons">
                <button class="btn btn-success" @click="handlerConfirmReplace">
                  Terminé
                </button>
              </nav>
            </section>

          </section>
        </div>
      </div>
    </div>

    <div class="replace-strengthen-person actions text-center">
      <button @click="handlerReplace" class="btn btn-lg btn-info">
        <i class="icon-rewind-outline"></i> Remplacer
      </button>
    </div>
  </div>
</template>

<script>
// MANUAL COMPILATION
// node node_modules/.bin/vue-cli-service build --name ReplaceStrengthenPerson --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/ReplaceStrengthenPerson.vue

import PersonAutoCompleter from "./components/PersonAutoCompleter";

export default {
  name: 'ReplaceStrengthenPerson',

  components: {
    PersonAutoCompleter
  },

  props: {
    person: {
      required: true
    },
    urlReplace: {
      required: true
    },
    urlAffectation: {
      required: true
    },
    urlStrengthen: {
      required: true
    }
  },

  data() {
    return {
      mode: "",
      recap: null,
      recapPending: "",
      errorRecap: "",
      error: "",

      step: 1,

      pendingMessage: "",
      errorMessage: "",

      replacer: "",
      replacer_id: null,
      step1info: "",

      projects: {},
      activities: {},
      structures: {},
      validations: {},
      referents: {},
      subordinates: {}
    }
  },

  methods: {
    handlerReplace() {
      this.mode = 'Remplacer';
      this.loadingMessage = "Chargement de l'aperçu";
    },

    handlerSwitchApply(item) {
      console.log("handlerSwitchApply", item);
      item.apply = !item.apply;
    },

    /**
     * Récap des changements.
     *
     * @param e
     */
    handlerSelectPerson(e) {
      this.replacer = e.displayname;
      this.replacer_id = e.id;
      this.step1info = "Remplacer " + this.person.displayname + " par " + this.replacer;
    },

    handlerConfirmStep1(){
      this.pendingMessage = "Chargement des affectations...";
      this.$http.get(this.urlAffectation+"?person=" + this.replacer_id).then(
          ok => {
            this.step = 2;
            this.projects = ok.body.affectations.projects;
            this.activities = ok.body.affectations.activities;
            this.structures = ok.body.affectations.structures;
            this.validations = ok.body.affectations.validations;
            this.referents = ok.body.affectations.referents;
            this.subordinates = ok.body.affectations.subordinates;
          },
          ko => {
            this.errorMessage = ko.body;
          }

      ).then( () => {
        this.pendingMessage = "";
      });
    },

    handlerConfirmReplace() {
      let data = new FormData();
      let out = {
        'projects': this.projects,
        'activities': this.activities,
        'structures': this.structures,
        'validations': this.validations,
        'subordinates': this.subordinates,
        'referents': this.referents,
        'replacer_id': this.replacer_id
      };

      this.pendingMessage = "Remplacement...";
      this.step = 3;

      data.append("out", JSON.stringify(out));

      this.$http.post(this.urlAffectation, data).then(
          ok => {
            document.location.reload();
          }, ko => {
            if (ko.status == 403) {
              this.errorMessage = "Non-autorisé";
            } else {
              if (ko.body.error) {
                this.errorMessage = "Erreur : " + ko.body.error;
              } else {
                this.errorMessage = ko.body;
              }
            }
          }
      ).then( foo => {
        this.pendingMessage = "";
      })
    },

    handlerCancel() {
      this.step = 1;
      this.errorMessage = "";
      this.mode = "";
      this.replacer_id = null;
      this.replacer = "";
      this.step1info = "";
    },
  },


  // Lifecycle
  mounted() {
    // amazing void content
  }
}
</script>