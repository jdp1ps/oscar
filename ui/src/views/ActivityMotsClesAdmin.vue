<template>
  <section>
    <Loader :visible="loading != null" :text="loading" />
    <!-- ERREUR -->
    <div class="overlay" v-if="error" style="z-index: 101">
      <div class="overlay-content" style="max-width: 50%">
        <h2>
          Erreur mots clés
          <span class="overlay-closer" @click="error = null">X</span>
        </h2>
        
        <div class="alert-danger alert">
          <i class="icon-attention-1"></i><div v-html="error"></div>
        </div>

        <button class="btn btn-default" @click="error = null">
          <i class="icon-cancel-outline"></i> Fermer
        </button>
      </div>
    </div>

    <h1><span class="icon-tags"></span> Mots clés</h1>

    <div style="display: flex; flex-direction: row-reverse;">
      <button @click="editedMotCle = {}" class="btn btn-primary"><span class="icon-plus-circled"></span> Créer un nouveau mot clé</button>
      <button @click="fusionEnCours = true" class="btn btn-primary" style="margin-right: 1em;"><span class="icon-tags"></span> Fusionner des mots clés</button>
    </div>

    <ol>
      <li v-for="m in touslesmotscles">

        <div class="title">
          <div>
            <span>[id : {{ m.id }}]</span> <h3 style="display: inline; line-break: anywhere;">{{ m.label }}</h3>
          </div>
          <div>
            <button @click="editedMotCle = { id: m.id, label: m.label }" class="cardbutton"><span class="icon-pencil"></span> Éditer</button>
            <button @click="deleteData = m" class="cardbutton"><span class="icon-trash"></span> Supprimer</button>
          </div>
        </div>
        <div style="padding-left: 1em; padding-bottom: 0.3em;">{{ m.activity_count }} activité{{ m.activity_count > 1 ? 's' : '' }} pour ce mot clé</div>
      </li>
    </ol>

    <!-- Modal de suppression -->
    <div class="overlay" v-if="deleteData">
      <div class="overlay-content">
        <h2 style="padding-right: 1em;">
          Suppression du mot clé : [id : {{ deleteData.id }}] "<span style="line-break: anywhere;">{{ deleteData.label }}</span>" ?
          <span class="overlay-closer" @click="deleteData = null">X</span>
        </h2>
        <p class="alert-danger alert">
          <i class="icon-attention-1"></i>
          Souhaitez-vous supprimer le mot clé : [id : {{ deleteData.id }}] <strong style="line-break: anywhere;">{{ deleteData.label }}</strong> ?
          <br />
          Actuellement attaché à
          <span v-if="deleteData.activity_count > 0"><strong>{{ deleteData.activity_count }} activité{{ deleteData.activity_count > 1 ? 's' : '' }}</strong></span>
          <span v-if="deleteData.activity_count == 0">aucune activité</span>
        </p>

        <div class="row">
          <div class="col-md-12">
            <nav class="buttons-bar">
              <button class="btn" @click="deleteData = null" style="margin-right: 1em;">
                <i class="icon-cancel-alt"></i> Annuler
              </button>
              <button class="btn btn-danger" @click="deleteMotCle">
                <i class="icon-valid"></i> Confirmer
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulaire Modification/Nouveau -->
    <div class="overlay" v-if="editedMotCle">
      <div class="overlay-content" style="display: flex; flex-direction: column;">
        <h2>
          <small>
            <i class="icon-doc"></i>
            <span v-if="!editedMotCle.id">Création d'un nouveau mot clé</span>
            <span v-if="editedMotCle.id">Modification d'un mot clé</span>
          </small>
          <span class="overlay-closer" @click="editedMotCle = null">X</span>
        </h2>

        <div class="row" v-if="!editedMotCle.label">
          <div style="background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; margin: 1em; padding: 1em;">
            <span style="font-size: xx-large;">⚠</span> Veuillez saisir un libellé pour le mot clé
          </div>
        </div>

        <div class="row" v-if="motCleExisteDeja">
          <div style="background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; margin: 1em; padding: 1em;">
            <span style="font-size: xx-large;">⚠</span> Le mot clé saisi existe déjà
          </div>
        </div>

        <div class="row" style="flex-grow: 2;">
          <div class="col-md-12" style="display: flex; flex-direction: column;">
            <input maxlength="128" v-focus v-model="editedMotCle.label" id="content" class="form-control" style="flex-grow: 2;" />
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <nav class="buttons-bar">
              <button class="btn btn-danger" style="margin-right: 1em;" @click="editedMotCle = null">
                <i class="icon-cancel-alt"></i> Annuler
              </button>
              <button class="btn btn-success" @click.prevent="applyEdit()" :disabled="!editedMotCle.label || motCleExisteDeja">
                <i class="icon-valid"></i> Enregistrer
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulaire de fusion -->
    <div class="overlay" v-if="fusionEnCours">
      <div class="overlay-content" style="display: flex; flex-direction: column;">
        <ActivityMotsClesAdminFusion @close="fusionEnCours = false" @fusion="(idsToMerge, targetId) => fusionner(idsToMerge, targetId)" :touslesmotscles="touslesmotscles" />
      </div>
    </div>
  </section>
</template>
<script>

import axios from 'axios';
import AxiosMessage from "../utils/AxiosMessage.js";
import Loader from '../components/Loader.vue';
import ActivityMotsClesAdminFusion from './ActivityMotsClesAdminFusion.vue';

export default {
  directives: {
    // enables v-focus in template
    focus: (el) => el.focus()
  },

  components: {
    Loader,
    ActivityMotsClesAdminFusion
  },

  props: {
    url: {default: null},
  },

  computed:{
    motCleExisteDeja(){
      return this.editedMotCle && this.editedMotCle.label && this.touslesmotscles.filter(
        m => m.label.toLocaleLowerCase() == this.editedMotCle.label.trim().toLocaleLowerCase())
        .length > 0;
    },
  },

  data() {
    return {
      touslesmotscles: [],
      error: null,
      loading: null,
      deleteData: null,
      editedMotCle: null,
      fusionEnCours: false,
    }
  },

  methods: {
    fusionner(idsToMerge, targetId) {
      this.fusionEnCours = false;

      this.loading = "Fusion des mots clés";
      axios.post(this.url, { action: "fusion", idsToMerge: idsToMerge, targetId: targetId }).then(
          () => {
            this.fetch();
          }, err => {
            this.handleError(err);
          }
      ).finally(() => { 
        this.loading = null;
      });
    },

    applyEdit() {
      let mode = "create";
      if (this.editedMotCle.id) {
        mode = "update";
      }

      this.loading = mode == "create" ? "Création du mot clé" : "Modification du mot clé";
      axios.post(this.url, { action: mode, id: this.editedMotCle.id, label: this.editedMotCle.label.trim() }).then(
          () => {
            this.fetch();
          }, err => {
            this.handleError(err);
          }
      ).finally(() => { 
        this.loading = null;
        this.editedMotCle = null;
      });
    },

    deleteMotCle() {
      this.loading = "Suppression du mot clé";
      axios.post(this.url, { action: "delete", id: this.deleteData.id }).then(
          () => {
            this.touslesmotscles.splice(this.touslesmotscles.findIndex(m => m.id === this.deleteData.id), 1);
          }, err => {
            this.handleError(err);
          }
      ).finally(() => { 
        this.loading = null;
        this.deleteData = null;
      });
    },

    fetch() {
      this.loading = "Chargement des mots clés";
      axios.get(this.url + '?include_activity_count=true').then(ok => {
        this.touslesmotscles = ok.data.motscles;
        this.loading = null;
      }, err => {
        this.handleError(err);
        this.loading = null;
      });
    },

    handleError(err) {
      if (!err.response.data) {
        this.error = err;
        return;
      }

      if (err.response.headers.get('content-type').includes('text/html')) {
        var el = document.createElement( 'html' );
        el.innerHTML = err.response.data;
        const errorHTML = el.querySelector('[id="contenu-principal"]');
        if (errorHTML) {
          this.error = errorHTML.innerHTML;
          return;
        }
        const contentLength = err.response.headers.get('content-length');
        if (contentLength && (typeof contentLength == "string") && !isNaN(contentLength) && Number(contentLength) < 1000) {
          this.error = err.response.data;
          return;
        }
      }

      this.error = AxiosMessage.manageErrorResponse(err);
    },
  },

  mounted() {
    this.fetch();
  },

}
</script>

<style scoped>

li {
  list-style: none;
  background-color: white;
  margin: 0.5em;
}

.title {
  display: flex;
  justify-content: space-between;
  padding: 0.4em;
  border-bottom-width: thin;
  border-bottom-style: solid;
  border-color: #f2f2f2;
}

h3 {
  font-weight: 600;
  font-size: 1.25em;
  margin-top: 0;
  margin-bottom: 0;
}

.cardbutton {
	background: none;
	border: none;
  color: #6285f2;
}

.cardbutton:hover {
  background: #0b58a2;
  color: white;
}
</style>
