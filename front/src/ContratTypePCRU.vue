<template>
  <div id="contrattypepcru">
    <h1>Correspondance Type d'activité / Type de contrat PCRU</h1>

    <div class="overlay">
      <div class="overlay-content" v-if="oscartypes.length">
        TYPES OSCAR
        <tree :tree="oscartypes[0]"></tree>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <h2>Types côté <strong>PCRU</strong></h2>
        <article class="card xs" v-for="t in pcrutypes" :key="t.id" :class="t.activitytype_id != null ? 'active' : 'disabled'">
          <h3><code>[{{ t.id }}]</code>
            <strong>{{ t.label }}</strong>
          </h3>
          <div v-if="t.activitytype_id">
            Associé au activité <strong>{{ t.activitytype_label }}</strong>
          </div>
          <button class="btn xs">
            Associer à un type dans Oscar
          </button>
        </article>
      </div>
    </div>
    HOP {{ url }}
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
import Tree from "./ActivityTypeTree";

// test
let oscarRemoteData = new OscarRemoteData();

function flashMessage(){
  // TODO
}



export default {

  components: {
    "ajax-oscar": AjaxOscar,
    "tree": Tree
  },

  props: {
    url: { required: true }
  },

  data(){
    return {
      formData: null,
      remoterState: oscarRemoteData.state,
      configuration: null,
      oscartypes: [],
      pcrutypes: []
    }
  },

  methods:{

    handlerSuccess(success){
      let datas = success.data;
      this.oscartypes = datas.activitytypes;
      this.pcrutypes = datas.pcrucontracttypes;
    },

    fetch(){
      console.log("FETCH()");
      oscarRemoteData
          .setDebug(true)
          .setPendingMessage("Chargement des types de contrat")
          .setErrorMessage("Impossible de charger la configuration")
          .performGet(this.url, this.handlerSuccess);
    }
  },

  mounted(){
    this.fetch();
  }

}
</script>