<template>
  <div>
    <div class="overlay" v-if="open">
      <div class="overlay-content">
        <a href="#" class="overlay-closer" @click="handlerCloseSynthesis()">Close</a>
        OPTIONS ICI
      </div>
    </div>
    <pre>{{ url }}</pre>
    <button class="btn btn-primary" @click="handlerOpenSynthesis()">
      <i class="icon-calendar"></i>
      Synthèse des déclarations
    </button>
  </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_modules/.bin/gulp activityTimesheetSynthesisMenuWatch

Pour compiler :
node node_modules/.bin/gulp activityTimesheetSynthesisMenu

 */

// import AjaxOscar from "./remote/AjaxOscar";
// import OscarRemoteData from "./remote/OscarRemoteData";

// test
export default {

  // components: {
  //   "ajax-oscar": AjaxOscar
  // },

  props: {
    url: { require: true }
  },

  data() {
    return {
      configuration: null,
      open: false,
      synthese: null
    }
  },

  methods: {
    handlerOpenSynthesis() {
      this.open = true;
      this.loadSynthesis();
    },
    handlerCloseSynthesis() {
      this.open = false;
      console.log("CLOSE Synthesis");
    },
    loadSynthesis(){
      this.loading = "Chargement de la synthèse...";
      this.$http.get(this.url).then(
          ok => {
            console.log(ok);
          }, ko => {

          }
      )
    }
  },

  mounted() {
    console.log("ActivityTimesheetSynthesisMenu.vue")
  }

}
</script>