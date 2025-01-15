<template>
  <section>

    <h2>
      <small>
        <i class="icon-doc"></i>
        <span>Fusionner des mots clés</span>
      </small>
      <span class="overlay-closer" @click="$emit('close')" >X</span>
    </h2>

    <div class="row" v-if="motsClesSelectionnes.size < 2">
      <div style="background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; margin: 1em; padding: 1em;">
        <span style="font-size: xx-large;">⚠</span> Veuillez sélectionner au moins deux mots clés à fusionner
      </div>
    </div>

    <div class="row" style="margin-bottom: 2em;">
      <div class="col-md-12" style="display: flex; flex-direction: column;">
        <label for="nouveauMotCle">Sélectionnez les mots clés à fusionner</label>
        <div>
          <span style="display: flex; background-color: white; border: 1px solid #aaa; border-radius: 4px; min-height: 34px;">
            <ul style="display: flex; flex-wrap: wrap; margin: 0; padding-left: 5px; padding-right: 5px; padding-bottom: 5px; width: 100%; align-items: center;">
              <template v-for="m in touslesmotscles">
                <li class="selectedmotcle" v-if="motsClesSelectionnes.has(m.id)">
                  <span @click="motsClesSelectionnes.delete(m.id)">×</span><strong>{{ m.label }}</strong> (id : {{ m.id }}, {{ m.activity_count }} activité{{ m.activity_count > 1 ? 's' : '' }})
                </li>
              </template>
              <li style="margin-top: 5px; flex-grow: 1; overflow: auto;">
                <input id="nouveauMotCle" @focusin="clickInput" @click="clickInput" v-on:keydown="onKeyDown" v-model="userInput" size="1" class="inputNouveauMotCle" />
              </li>
            </ul>
          </span>

          <div v-if="affichertouslesmotscles" style="background-color: white; border: 1px solid #aaa; border-radius: 4px; border-top-left-radius: 0; border-top-right-radius: 0; z-index: 1051;" >
            <ul class="listeDeTousLesMotsCles" >
              <template v-for="m in touslesmotscles">
                <li v-if="m.label.toLocaleLowerCase().includes(userInput.toLocaleLowerCase())" class="undetouslesmotscles" :class="{ selected: motsClesSelectionnes.has(m.id) }" @click="motsClesSelectionnes.has(m.id) ? motsClesSelectionnes.delete(m.id) : motsClesSelectionnes.add(m.id); affichertouslesmotscles = false; userInput = '';"><strong class="labelDuMotCle">{{ m.label }}</strong> (id : {{ m.id }}, {{ m.activity_count }} activité{{ m.activity_count > 1 ? 's' : '' }})</li>
              </template>
              <li v-if="aucunMotCleNeCorrespond" style="padding: 6px;">Aucun mot clé existant correspondant</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row" v-if="!idMotCleCible">
      <div style="background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; margin: 1em; padding: 1em;">
        <span style="font-size: xx-large;">⚠</span> Veuillez sélectionner le nom du mot clé qui remplacera tous les autres après la fusion
      </div>
    </div>

    <div class="row">
      <div class="col-md-12" style="display: flex; flex-direction: column;">
        <label for="motCleCible">Sélectionnez le nom du mot clé qui remplacera tous les autres après la fusion</label>
        <select id="motCleCible" v-model="idMotCleCible">
          <option value=""></option>
          <template v-for="m in touslesmotscles">
            <option :value="m.id" v-if="motsClesSelectionnes.has(m.id)"><strong>{{ m.label }}</strong> (id : {{ m.id }}, {{ m.activity_count }} activité{{ m.activity_count > 1 ? 's' : '' }})</option>
          </template>
        </select>
      </div>
    </div>

    <div class="row" v-if="motsClesSelectionnes.size >= 2 && idMotCleCible">
      <div style="background-color: #dff0d8; border-color: #d6e9c6; color: #3c763d; margin: 1em; padding: 1em;">
        En cliquant sur fusionner, le mot clé  <strong style="line-break: anywhere;">{{ motCleParId(idMotCleCible).label }}</strong> (id: {{ idMotCleCible }}, {{ motCleParId(idMotCleCible).activity_count }} activité{{ motCleParId(idMotCleCible).activity_count > 1 ? 's' : '' }}) va être ajouté :
        <ul>
          <template v-for="m in touslesmotscles">
            <li v-if="motsClesSelectionnes.has(m.id) && m.id != idMotCleCible">aux {{ m.activity_count }} activité{{ m.activity_count > 1 ? 's' : '' }} ayant le mot clé <strong style="line-break: anywhere;">{{ m.label }}</strong> (id : {{ m.id }})</li>
          </template>
        </ul>
        Ensuite, ces mots clés seront supprimés : 
          <template v-for="m in touslesmotscles">
            <span v-if="motsClesSelectionnes.has(m.id) && m.id != idMotCleCible"><strong style="line-break: anywhere;">&nbsp;{{ m.label }}</strong> (id : {{ m.id }})</span>
          </template>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <nav class="buttons-bar">
          <button class="btn btn-danger" style="margin-right: 1em;" @click="$emit('close')">
            <i class="icon-cancel-alt"></i> Annuler
          </button>
          <button class="btn btn-success" :disabled="motsClesSelectionnes.size < 2 || !idMotCleCible" @click="$emit('fusion', Array.from(motsClesSelectionnes), idMotCleCible)">
            <i class="icon-valid"></i> Fusionner
          </button>
        </nav>
      </div>
    </div>

  </section>
</template>
<script>

export default {

  props: {
    touslesmotscles: {default: []},
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
      affichertouslesmotscles: false,
      userInput: "",
      idMotCleCible: null,
      motsClesSelectionnes: new Set()
    }
  },

  methods: {

    motCleParId(id) {
      for (const unMotCle of this.touslesmotscles) {
        if (unMotCle.id == id) {
          return unMotCle;
        }
      }
      return null;
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
          && !e.target.classList.contains("listeDeTousLesMotsCles")
          && !e.target.classList.contains("labelDuMotCle")) {
        this.affichertouslesmotscles = false;
      }
    },
  },

  mounted() {
    document.addEventListener('mousedown', this.onMouseDown);
  },

  unmounted() {
    document.removeEventListener('mousedown', this.onMouseDown);
  }
}
</script>


<style scoped>

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

.listeDeTousLesMotsCles {
  margin: 0;
  padding: 0;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
}

</style>
