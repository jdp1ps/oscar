<template>
  <section style="position: relative;">
    <Loader :visible="loading" :text="loading" />
    <!-- ERREUR -->
    <div class="overlay" v-if="error" style="z-index: 101">
      <div class="overlay-content" style="max-width: 50%">
        <h2>
          Erreur notes
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

    <!-- LISTE DES NOTES -->
    <nav class="admin-bar text-right">
      <a class="btn btn-default btn-xs" v-if="manageadminallowed || manageuserallowed" @click="handlerNew()"><i class="icon-doc-add"></i> Nouvelle </a>
      <a class="btn btn-default btn-xs" @click="fetch()" v-if="debugEnabled">
        <i class="icon-ref"></i> Reload
      </a>
    </nav>
    <div class="note" v-for="c in items">
        <header class="note-header">
          <div>
            <span>
              <i class="icon-calendar"></i> {{ $filters.dateFull(c.dateRef) }}
            </span>
            <span>
              <i class="icon-user"></i>
              <PersonDisplay :person="c.createdBy" v-if="c.createdBy.id"/>
              <em v-else>
                {{ c.createdBy.username ? c.createdBy.username : 'Inconnu' }}
              </em>
            </span>
          </div>
          <div v-if="manageadminallowed || c.mine && manageuserallowed">
            <button type="button" class="btn-xs btn btn-danger" @click="handlerDelete(c.id)" style="margin-right: 1em;">
              <i class="icon-trash" style="background: transparent;"></i> Supprimer</button>
            <button type="button" class="btn btn-xs btn-default" @click="handleModify(c)">
              <i class="icon-pencil"></i> Modifier
            </button>
          </div>
        </header>
        <div class="note-content">
          {{ c.content }}
        </div>
    </div>

    <!-- Formulaire Modification/Version/Nouveau -->
    <div class="overlay" v-if="editedNote">
      <div class="overlay-content" style="height: 80vh; display: flex; flex-direction: column;">
        <h2>
          <small>
            <i class="icon-doc"></i>
            <span v-if="mode == 'create'">Rédaction d'une nouvelle note</span>
            <span v-if="mode == 'update'">Modification d'une note</span>
          </small>
          <span class="overlay-closer" @click="editedNote = null">X</span>
        </h2>

        <div class="row" style="flex-grow: 2;">
          <div class="col-md-12" style="height: 100%; display: flex; flex-direction: column;">
            <label for="content">Contenu</label>
            <textarea maxlength="9000" v-focus v-model="editedNote.content" id="content" class="form-control" style="flex-grow: 2;"></textarea>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <nav class="buttons-bar">
              <button class="btn btn-danger" style="margin-right: 1em;" @click="editedNote = null">
                <i class="icon-cancel-alt"></i> Annuler
              </button>
              <a class="btn btn-success" href="#" @click.prevent="applyEdit()">
                <i class="icon-valid"></i> Enregistrer
              </a>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<script>

import axios from 'axios';
import Loader from '../components/Loader.vue';
import AxiosMessage from "../utils/AxiosMessage.js";
import PersonDisplay from "../components/PersonDisplay.vue";

export default {
  directives: {
    // enables v-focus in template
    focus: (el) => el.focus()
  },

  components: {
    PersonDisplay,
    Loader
  },

  props: {
    url: {default: null},
    showallowed: { default: false },
    manageuserallowed: { default:false },
    manageadminallowed: { default:false },
    items: { default: [] },
    debugEnabled: { default: false }
  },

  data() {
    return {
      notes: [],
      editedNote: null,
      mode: null,
      error: null,
      loading: null
    }
  },

  methods: {
    handlerNew() {
      this.mode = 'create';
      this.editedNote = {
        id: -1,
        content: ""
      };
    },

    applyEdit() {
      this.loading = "Enregistrement en cours";
      axios.post(this.url, { action: this.mode, note_id: this.editedNote.id, content: this.editedNote.content }).then(
          () => {
            this.fetch();
          }, err => {
            this.handleError(err);
          }
      ).finally(() => { 
        this.editedNote = null;
        this.loading = null;
      });
    },

    fetch() {
      this.loading = "Chargement des notes";
      axios.get(this.url).then(ok => {
        console.log("Update note", ok);
        this.$emit("update", ok.data);
        this.loading = null;
      }, err => {
        this.handleError(err);
        this.loading = null;
      });
    },

    handlerDelete(noteID) {
      this.loading = "Suppression en cours";
      axios.post(this.url, { action: "delete", note_id: noteID } ).then(
          () => {
            this.fetch();
          }, err => {
            this.handleError(err);
          }
      ).finally(() => this.loading = null);
    },

    handleModify(c) {
      this.mode = 'update';
      this.editedNote = { id: c.id, content: c.content };
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
      }

      this.error = AxiosMessage.manageErrorResponse(err);
    },
  },

  mounted() {

  }

}
</script>

<style scoped>

.note {
  background-color: white;
  margin: 0 0 .5em;
  padding: 0em;
  .note-header {
    padding: .2em 1em;
    border-bottom: solid thin #DDD;
    display: flex;
    justify-content: space-between;
  }
  .note-content {
    padding: .2em 1em;
    white-space: pre-wrap;
    font-family: monospace;
  }
}



</style>