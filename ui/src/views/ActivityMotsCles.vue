<template>
  <section style="position: relative;">
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

    <label>Mots clés</label>

    <select style="display: none;" name="motscles[]" multiple="" tabindex="-1" aria-hidden="true">
      <template v-for="m in touslesmotscles">
        <option v-if="m.selected" :value="m.id" selected>{{ m.label }}</option>
      </template>
    </select>

    <div style="display: flex;">
      <div style="width: 50%; min-width: 50%;">
        <span style="display: flex; background-color: white; border: 1px solid #aaa; border-radius: 4px; min-height: 34px;">
          <ul style="display: flex; flex-wrap: wrap; margin: 0; padding-left: 5px; padding-right: 5px; padding-bottom: 5px; width: 100%; align-items: center;">
            <template v-for="m in touslesmotscles">
              <li class="selectedmotcle" v-if="m.selected">
                <span @click="m.selected = false">×</span>{{ m.label }}
              </li>
            </template>
            <li style="margin-top: 5px; flex-grow: 1; overflow: auto;">
              <input @focusin="clickInput" @click="clickInput" v-on:keydown="onKeyDown" v-model="userInput" size="1" class="inputNouveauMotCle" />
            </li>
          </ul>
        </span>

        <span v-if="affichertouslesmotscles" style="background-color: white; border: 1px solid #aaa; border-radius: 4px; border-top-left-radius: 0; border-top-right-radius: 0; position: absolute; z-index: 1051; width: 50%;" >
          <ul class="listeDeTousLesMotsCles" >
            <template v-for="m in touslesmotscles">
              <li v-if="m.label.toLocaleLowerCase().includes(userInput.toLocaleLowerCase())" class="undetouslesmotscles" :class="{ selected: m.selected }" @click="m.selected = (m.selected ? false : true); affichertouslesmotscles = false; userInput = '';">{{ m.label }}</li>
            </template>
            <li v-if="aucunMotCleNeCorrespond" style="padding: 6px;">Aucun mot clé existant correspondant</li>
          </ul>
        </span>
      </div>
      <div style="max-width: 50%; overflow: auto; margin-left: 10px;">
        <button class="bouton" @click.stop="créerMotClé" :disabled="!userInput.trim() || motCléExiste()">Créer le nouveau mot clé : <span v-if="!userInput.trim()">∅</span><span v-if="userInput.trim()" style="white-space: preserve-spaces;">{{ userInput.trim() }}</span></button>
        <div v-if="!userInput.trim()" style="background: #efefef; color: #666666; padding: 0.5em 1em; margin-top: 0.5em; font-size: 0.9em; border-radius: 0.5em; font-weight: 100;">ℹ️ Pour créer un nouveau mot clé, veuillez d'abord saisir son libellé dans le champ "Mots clés"</div>
        <div v-if="userInput.trim() && motCléExiste()" style="background: #efefef; color: #666666; padding: 0.5em 1em; margin-top: 0.5em; font-size: 0.9em; border-radius: 0.5em; font-weight: 100;">ℹ️ Le mot clé existe déjà</div>
      </div>
    </div>

  </section>
</template>
<script>

import axios from 'axios';
import AxiosMessage from "../utils/AxiosMessage.js";
import Loader from '../components/Loader.vue';

export default {

  components: {
    Loader
  },

  props: {
    url: {default: null},
    motsclesselectionnes: {default: []},
  },

  computed:{
    aucunMotCleNeCorrespond(){
      return this.userInput && this.touslesmotscles.filter(
        m => m.label.toLocaleLowerCase().includes(this.userInput.trim().toLocaleLowerCase()))
        .length == 0;
    }
  },

  data() {
    return {
      touslesmotscles: [],
      error: null,
      loading: null,
      affichertouslesmotscles: false,
      userInput: ""
    }
  },

  methods: {

    motCléExiste() {
      for (const unMotCle of this.touslesmotscles) {
        if (unMotCle.label.toLocaleLowerCase() == this.userInput.toLocaleLowerCase()) {
          return true;
        }
      }
      return false;
    },

    créerMotClé(e) {
      e.preventDefault();

      this.affichertouslesmotscles = false;
      this.loading = "Création du mot clé";
      axios.post(this.url, { action: "create", label: this.userInput.trim() }).then(
          (ok) => {
            let nouveauMotCle = ok.data;
            nouveauMotCle.selected = true;
            this.touslesmotscles.push(nouveauMotCle);
            this.userInput = "";
          }, err => {
            this.handleError(err);
          }
      ).finally(() => { 
        this.loading = null;
      });
    },

    onKeyDown(event) {
      if (event.key === "Enter") {
        event.preventDefault();
        return;
      }

      if (event.key === "Tab") {
        this.affichertouslesmotscles = false;
        return;
      }
    },

    clickInput() {
      this.affichertouslesmotscles = true;
    },

    onMouseDown(e) {
      if (!e.target.classList.contains("undetouslesmotscles")
          && !e.target.classList.contains("inputNouveauMotCle")
          && !e.target.classList.contains("listeDeTousLesMotsCles")) {
        this.affichertouslesmotscles = false;
      }
    },

    fetch() {
      this.loading = "Chargement des mots clés";
      axios.get(this.url).then(ok => {
        this.touslesmotscles = ok.data.motscles;
        const dejaSelectionnes = JSON.parse(this.motsclesselectionnes);
        for (let unMotCle of this.touslesmotscles) {
          if (dejaSelectionnes.includes(unMotCle.id)) {
            unMotCle.selected = true;
          }
        }
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
      }

      this.error = AxiosMessage.manageErrorResponse(err);
    },
  },

  mounted() {
    document.addEventListener('mousedown', this.onMouseDown);

    this.fetch();
  },

  unmounted() {
    document.removeEventListener('mousedown', this.onMouseDown);
  }
}
</script>


<style scoped>

li {
  list-style: none;
}

.selectedmotcle {
  display: inline;
  background-color: #e4e4e4;
  border: 1px solid #aaa;
  border-radius: 4px;
  margin-right: 5px;
  margin-top: 5px;
  padding: 0 5px;
  cursor: default;
  line-break: anywhere;
}

.selectedmotcle > span{
  color: #999;
  cursor: pointer;
  font-weight: bold;
  margin-right: 2px;
}

.undetouslesmotscles {
  padding: 6px;
}

.undetouslesmotscles:hover {
  background-color: #5897fb;
  color: white;
  cursor: pointer;
}

.selected {
  background-color: #ddd;
}

.inputNouveauMotCle {
  width: 100%;
  min-width: 80px;
  border: none;
  outline: 0;
}

.bouton {
  background-color: white;
  border-radius: 0;
  border: 1px solid transparent;
  line-height: 22px;
  font-size: 14px;
  padding: 6px 12px;
}

.bouton:hover {
  background-color: #e6e6e6;
  border-color: #adadad;
}
.bouton[disabled] {
  cursor: not-allowed;
  opacity: 0.65;
}
.bouton:hover[disabled] {
  background-color: #fff;
  border-color: #ccc;
}

.listeDeTousLesMotsCles {
  margin: 0;
  padding: 0;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
}
</style>
