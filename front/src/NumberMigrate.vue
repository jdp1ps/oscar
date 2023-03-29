<template>
  <div>
    <button @click="handlerDisplayUi()" class="btn btn-primary">
      Migration des numérotations
      TEST
    </button>
    <div class="overlay" v-if="display">
      <div class="overlay-content">
        <h3 v-if="message">
          {{ message }}
        </h3>
        <article class="row" v-else>
          <div class="col-md-12">
            <h3>Outils de migration de clef de numérotation</h3>
            <p class="alert alert-info">
              Cette écran permet de mettre à jour automatiquement les activités utilisant certaines clefs vers une des clefs déclarée dans la liste des clefs configurer. <strong>Les valeurs sont concervées.</strong>
            </p>
          </div>
          <div class="col-md-6">
            Déplacer la clef :
            <select name="from" v-model="formData.from">
              <option value="">Selectionnez un clef à déplacer</option>
              <option v-for="k in unreferencedSorted" :value="k">{{ k }}</option>
            </select>
          </div>
          <div class="col-md-6">
            vers la clef :
            <select name="to" id="" v-model="formData.to">
              <option value="">Choisir une clef</option>
              <option v-for="dest in referenced" :value="dest">{{ dest }}</option>
            </select>
          </div>
        </article>
        <hr>
        <nav class="buttons">
          <button @click="handlerHideUi()" class="btn btn-danger">
            Fermer
          </button>
          <button v-if="!message" @click="handlerSave()"
                  class="btn btn-success"
                  :class="{'disabled': !formData.from || !formData.to }">
            Enregistrer
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>
<script>
// node node_modules/.bin/vue-cli-service build --name NumberMigrate --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/NumberMigrate.vue
export default {
  data(){
    return {
      display: false,
      formData: {
        from: "",
        to: ""
      },
      message: "",
      unreferenced: [],
      referenced: []
    }
  },
  computed: {
    unreferencedSorted(){
      return this.unreferenced.sort();
    }
  },
  methods:{
    handlerSave(){
      let form = new FormData();
      form.append("from", this.formData.from);
      form.append("to", this.formData.to);
      this.$http.post('?action=migrate', form).then(
          ok => {
            this.$emit("migrate");
            this.message = ok.data;
          },
          ko => {
            this.message = ko.data;
          }
      )
    },

    handlerDisplayUi(){
      this.display = true;
      this.$http.get('?action=migrate').then(
          ok => {
            console.log('OK', ok);
            this.unreferenced = ok.data.unreferenced;
            this.referenced = ok.data.referenced;
          },
          ko => {
            this.message = ko.data;
          }
      )
    },
    handlerHideUi(){
      this.display = false;
      if( this.message ){
        document.location.reload();
      }
    }
  }
}
</script>