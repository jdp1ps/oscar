<template>
  <div id="contrattypepcru">
    <ajax-oscar :oscar-remote-data="remoteState"/>

    <div class="overlay" v-if="searchActivity">
      <div class="overlay-content">
        <h3>Rechercher une activité</h3>
        <input type="search" v-model="search" class="form-control lg" />
        <section class="results">
          <article class="card xs" v-for="a in activities">
            <h3 class="card-title">
              <code>{{ a.numOscar }}</code>
              <strong><i class="icon-cubes"></i> {{ a.projectacronym }}</strong>
              <em><i class="icon-cube"></i> {{ a.label }}</em>
              {{ a.label }}({{ a.PFI }})</h3>
            <div v-if="a.pcruenable">
                <code>{{ a.pcru.status }}</code>
              <div class="alert alert-danger" v-if="a.pcru.errors.length > 0">
                {{ a.pcru.errors }}
              </div>
              <div v-else>
                {{ a.pcru.datas }}
              </div>

            </div>
            <div v-else>
              <button class="btn xs" @click="handlerSelectActivity(a)">Activation PCRU</button>
            </div>
          </article>
        </section>
      </div>
    </div>

    <button class="btn btn-primary" @click="handlerSearchActivity">
      <i class="icon-search-outline"></i>
      Rechercher une activité
    </button>


    <pre>
      {{ search }}
    </pre>
  </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_modules/.bin/gulp contratTypePCRUWatch

Pour compiler :
node node_modules/.bin/gulp contratTypePCRU

 */

import AjaxOscar from "./remote/AjaxOscar";
import OscarRemoteData from "./remote/OscarRemoteData";


// test
let oscarRemoteData = new OscarRemoteData();
let searchTempo = null;

export default {

  components: {
    "ajax-oscar": AjaxOscar
  },

  props: {
    urlSearchActivities: { required: true },
    urlActivityPCRUCreate: { required: true },
    urlPreviewPCRU: { required: true },
  },

  data() {
    return {
      remoterState: oscarRemoteData.state,
      remoteState: oscarRemoteData.state,
      activities: [],
      searchActivity: false,
      selectedActivity: null,
      search: ""
    }
  },

  watch: {
    search(val){
      if( searchTempo ){
        clearTimeout(searchTempo);
      }
      searchTempo = setTimeout(this.performSearch, 700);
    }
  },

  methods: {

    handlerSearchActivity(){
      this.searchActivity = true;
    },

    handlerSelectActivity(activity){
      this.selectedActivity = activity;
      let searchQuery = this.urlPreviewPCRU+'&activity_id=' + activity.id;
      oscarRemoteData
          .setDebug(true)
          .setPendingMessage("Calcule des données PCRU pour " + activity.id +"...")
          .setErrorMessage("Impossible de charger les données PCRU")
          .performGet(searchQuery, this.handlerPreviewSuccess);
    },

    handlerPreviewSuccess(response){
      this.selectedActivity.pcruenable = true;
      this.selectedActivity.pcru = {
        'datas': response.data.preview.datas,
        'errors': response.data.preview.errors,
        'status': response.data.preview.status
      };
    },

    performSearch(){
      let searchQuery = this.urlSearchActivities+'&search=' + this.search;
      oscarRemoteData
          .setDebug(true)
          .setPendingMessage("Recherche...")
          .setErrorMessage("Erreur de recherche")
          .performGet(searchQuery, this.handlerSearchSuccess);
    },



    handlerClose(){

    },

    handlerSearchSuccess(response){
      this.activities = response.data.activities;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // TRAITEMENT des DONNEES


  },

  mounted() {
    console.log("CreateProcessusPCRU mounted")
  }

}
</script>