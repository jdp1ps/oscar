<template>
  <section>
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

    <!-- LOADING -->
    <transition name="fade">
        <div class="pending overlay" v-if="loading">
            <div class="overlay-content">
                <i class="icon-spinner animate-spin"></i>
                {{ loading }}
            </div>
        </div>
    </transition>
    
    <!-- LISTE DES NOTES -->
    <h2><i class="icon-doc"></i> Notes</h2>
    <nav class="admin-bar text-right">
      <a class="oscar-link" v-if="manageadminallowed || manageuserallowed" @click="handlerNew()"><i class="icon-doc-add"></i> Nouvelle </a>
    </nav>
    <div class="note" v-for="c in notes" v-if="showallowed">
        <div style="margin-bottom: 1em; display: flex; justify-content: space-between">
          <div>
            <span>
              <i class="icon-calendar"></i> {{ format(c.date_updated) }}
            </span>
            <span>
              <i class="icon-user"></i> {{ c.created_by.first_name + ' ' +c.created_by.last_name }}
            </span>
          </div>
          <div v-if="manageadminallowed || (manageuserallowed && c.created_by.id == this.userid)">
            <button type="button" class="btn btn-danger" @click="handlerDelete(c.id)" style="margin-right: 1em;"><i class="icon-trash" style="background: transparent;"></i> Supprimer</button>
            <button type="button" class="btn btn-default" @click="handleModify(c)"><i class="icon-pencil"></i> Modifier</button>
          </div>
        </div>
        <div style="white-space: pre; border: solid thin #999; padding: 0.8em;">{{ c.content }}</div>
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

export default {
  directives: {
    // enables v-focus in template
    focus: (el) => el.focus()
  },

  props: {
    activityid: {default: null},
    url: {default: null},
    showallowed: { default: false },
    manageuserallowed: { default:false },
    manageadminallowed: { default:false },
    userid: { default: null }
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
      axios.post(this.url + "?activityid=" + this.activityid, { action: this.mode, note_id: this.editedNote.id, content: this.editedNote.content, activity_id: this.activityid }).then(
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
      axios.get(this.url + "?activityid=" + this.activityid).then(ok => {
        this.notes = ok.data.notes;
      }, err => {
        this.handleError(err);
      });
    },

    handlerDelete(noteID) {
      this.loading = "Suppression en cours";
      axios.post(this.url + "?activityid=" + this.activityid, { action: "delete", note_id: noteID } ).then(
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
      
      this.error = err.response.data;
    },

    format(isodate) {
      return new Intl.DateTimeFormat(
          undefined,
          {
              dateStyle: 'short',
              timeStyle: 'medium',
          }).format(Date.parse(isodate));
    }
  },

  mounted() {
    if (this.showallowed) {
      this.fetch();
    }
  }

}
</script>

<style scoped>

.note {
  background-color: white;
  margin: 1em;
  padding: 1em;
}

</style>