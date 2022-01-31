<template>
  <div>
    <div class="overlay" v-if="mode != ''">
      <div class="overlay-content" :style="{'overflow': recap ? 'scroll' : 'visible'}">
        <a href="#" @click.prevent="handlerCancel()" class="overlay-closer">X</a>
        <h1>
          {{ mode }}
          <strong>{{ person }}</strong>
        </h1>
        <div class="alert-info alert">
          Remplacer <strong>{{ person }}</strong> par une autre personnes pour la validation des feuilles de temps dans les activités de recherche
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
        <div v-else-if="recap" class="alert alert-info">
          <h2>{{ recap.info }}</h2>
          <ul>
            <li v-for="prj in recap.prj">
              <i class="icon-cube"></i><em>Validation project</em> <strong>{{ prj.label }}</strong>
            </li>
            <li v-for="sci in recap.sci">
              <i class="icon-beaker"></i><em>Validation scientifique</em> <strong>{{ sci.label }}</strong>
            </li>
            <li v-for="adm in recap.adm">
              <i class="icon-book"></i><em>Validation administratif</em> <strong>{{ adm.label }}</strong>
            </li>
          </ul>
          <hr>
          <nav class="buttons text-center">
            <button class="btn btn-success" @click="handlerConfirmReplace()">
              Confirmer
            </button>
          </nav>
        </div>
        <div v-else>
          <p>Choisissez une personne pour {{ mode }}</p>
          <person-auto-completer @change="handlerSelectPerson" style="z-index: 10"/>
        </div>
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
    }
  },

  data() {
    return {
      mode: "",
      recap: null,
      recapPending: "",
      errorRecap: "",
      error: ""
    }
  },

  methods: {
    handlerReplace() {
      this.mode = 'Remplacer';
    },

    /**
     * Récap des changements.
     *
     * @param e
     */
    handlerSelectPerson(e) {
      let data = new FormData();
      this.recapPending = "Chargement";
      data.append("replacer_id", e.id);
      this.$http.post(this.urlReplace, data).then(
          ok => {
            this.recap = ok.body.summary;
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

    handlerConfirmReplace(){
      let data = new FormData();
      this.recapPending = "Remplacement...";
      data.append("summary", JSON.stringify(this.recap));

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
  }
}
</script>