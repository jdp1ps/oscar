<template>
  <div>
    <div class="overlay" v-if="mode != ''">
      <div class="overlay-content" style="overflow: scroll">
        <a href="#" @click.prevent="handlerCancel()" class="overlay-closer">X</a>
        <h1>
          {{ mode }}
          <strong>{{ person.displayname }}</strong>
        </h1>

        <div class="alert-info alert">
          Remplacer <strong>{{ person.displayname }}</strong> par une autre personnes
        </div>

        <div v-if="recapPending">
          <i class="icon-spinner animate-spin"></i>
          {{ recapPending }}
        </div>
        <div v-else-if="errorRecap" class="alert-danger alert">
          <i class="icon-attention-1"></i>
          <strong>Erreur</strong> :
          {{ errorRecap }}
        </div>
        <div v-else>
          <p>Choisissez une personne pour {{ mode }}</p>
          <person-auto-completer @change="handlerSelectPerson" style="z-index: 10"/>
        </div>

        <section class="">
          <!-- <h2>INFOS : {{ recap.infos }}</h2> -->
          <section class="affectations" v-if="affectations">
            <section>
              <h2>
                <i class="icon-beaker"></i>
                Activité/projet de recherche
              </h2>

              <section class="projects">
                <h4><i class="icon-cubes"></i> Projets</h4>
                <p v-if="replacer_id">
                  <strong>{{ replacer }}</strong>
                  va être ajouté dans le(s) <strong>{{ affectations.projects.length }}</strong> projet(s) suivant :
                </p>
                <div class="columns-3">
                  <div class="card xs applyable"
                       @click="handlerSwitchApply(p)"
                       v-for="p in affectations.projects" :class="{ 'apply': p.apply }">
                    <div class="">
                      <strong>
                        <i class="icon-cubes"></i>
                        {{ p.acronym }}
                      </strong>
                      <small>{{ p.label }}</small>
                      <span class="badge bg-info on-right">{{ p.activities_count }}</span>
                    </div>
                    HHH
                    <span class="cartouche xs">
                      {{ p.roles.join(', ') }}
                    </span>
                  </div>
                </div>
              </section>

              <h3><i class="icon-cube"></i> Activités</h3>

              <div style="columns: 3">
                <div v-for="p in activities" class="card xs applyable"
                     @click="handlerSwitchApply(p)"
                     :class="{ 'active': p.active, 'disabled': !p.active, 'apply': p.apply }">
                  <div style="border-bottom: solid thin #ccc">
                    <strong>
                      <i class="icon-cube"></i>
                      {{ p.acronym }}
                    </strong>
                    <em>{{ p.label }}</em>
                    <i class="on-right" :class="'icon-status-' + p.status" :title="p.status_text"></i>
                  </div>
                  <small>({{ p.roles.join(', ') }})</small>
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
                  <small>({{ p.roles.join(', ') }})</small>
                </div>

            </section>

            <section>
              <h2>
                <i class="icon-calendar"></i>
                Validation
              </h2>

              <div v-if="affectations.validations.prj">
                <h4><i class="icon-cube"></i>Validation projet</h4>
                <div class="card xs applyable"
                     @click="v.apply = !v.apply; console.log('OK')"
                     v-for="v in affectations.validations.prj"
                     :class="{'apply': v.apply }">
                  <strong>{{ v.acronym }}</strong>
                  <em>{{ v.label }}</em>
                </div>
              </div>

              <div v-if="affectations.validations.sci">
                <h4><i class="icon-beaker"></i>Validation scientifique</h4>
                <div class="card xs applyable"
                     @click="v.apply = !v.apply"
                     v-for="v in affectations.validations.sci"
                     :class="{'apply': v.apply }">
                  <strong>{{ v.acronym }}</strong>
                  <em>{{ v.label }}</em>
                </div>
              </div>

              <div v-if="affectations.validations.adm">
                <h4><i class="icon-book"></i>Validation administratif</h4>
                <div class="card xs applyable"
                     @click="v.apply = !v.apply"
                     v-for="v in affectations.validations.adm"
                     :class="{'apply': v.apply }">
                  <strong>{{ v.acronym }}</strong>
                  <em>{{ v.label }}</em>
                </div>
              </div>
            </section>
          </section>
          <hr>
          <nav class="buttons text-center">
            <button class="btn btn-success" @click="handlerConfirmReplace()">
              Confirmer
            </button>
          </nav>
        </section>
      </div>
    </div>

    <div class="replace-strengthen-person actions text-center">
      <div class="alert alert-danger">
        {{ error }}
      </div>
      <button @click="handlerReplace">
        <i class="icon-rewind-outline"></i> Remplacer
      </button>
      <button>
        <i class="icon-plus-circled"></i> Renforcer
      </button>
    </div>
  </div>
</template>

<script>
// MANUAL COMPILATION
// node node_modules/.bin/vue-cli-service build --name ReplaceStrengthenPerson --dest ../public/js/oscar/dist/ReplaceStrengthenPerson --no-clean --formats umd,umd-min --target lib src/ReplaceStrengthenPerson.vue;
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
    urlStrengthen: {
      required: true
    },
    affectations: {
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
      replacer: "",
      replacer_id: null,

      projects: {},
      activities: {},
      structures: {},
      validations: {}
    }
  },

  methods: {
    handlerReplace() {
      this.mode = 'Remplacer';
    },

    handlerSwitchApply(item){
      console.log("handlerSwitchApply", item);
      item.apply = !item.apply;
    },

    /**
     * Récap des changements.
     *
     * @param e
     */
    handlerSelectPerson(e) {
      console.log("handlerSelectPerson");

      this.replacer = e.displayname;
      this.replacer_id = e.id;

      this.recap = {
        "replacer_id": e.id,
        "infos": "Remplacer " + this.person.displayname + " par " + e.displayname,
        "dump": this.person
      };
      console.log(e);
    },

    handlerConfirmReplace(){
      let data = new FormData();
      let out = {
        'projects': this.projects,
        'activities': this.activities,
        'structures': this.structures,
        'validations': this.validations,
        'replacer_id': this.replacer_id
      };
      console.log('OUT', out);
      this.recapPending = "Remplacement...";
      data.append("out", JSON.stringify(out));

      this.$http.post(this.urlReplace, data).then(
          ok => {
            document.location.refresh();
          }, ko => {
            if( ko.status == 403 ){
              this.errorRecap = "Non-autorisé";
            } else {
              if( ko.body.error ){
                this.errorRecap = "Erreur : " + ko.body.error;
              } else {
                this.errorRecap = ko.body;
              }
            }
          }
      ).then( foo => this.recapPending = "");
    },

    handlerCancel(){
      this.recapPending = "";
      this.errorRecap = "";
      this.recap = "";
      this.mode = "";
    },
  },


  // Lifecycle
  mounted() {
    console.log("mounted");
    this.projects = this.affectations.projects;
    this.activities = this.affectations.activities;
    this.structures = this.affectations.structures;
    this.validations = this.affectations.validations;
  }
}
</script>