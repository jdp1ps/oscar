<template>
  <div id="contrattypepcru">
    <h1>Correspondance Type d'activité / Type de contrat PCRU</h1>

    <ajax-oscar :oscar-remote-data="remoteState"/>

    <div class="overlay" v-if="selectedPcru">
      <div class="overlay-content" v-if="oscartypes.length">
        <a class="overlay-closer" @click="handlerClose">Fermer</a>
        <h3>Correspondance</h3>
        <p>Choississez un type d'activité correspondant dans Oscar pour les contrats PCRI
          <strong></strong> : </p>
        <tree :tree="oscartypes[0]" @select="handlerSelect"></tree>
        <button class="button button-danger" @click="handlerClose">
          Fermer
        </button>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <h2>Types côté <strong>PCRU</strong></h2>
        <article class="card xs" v-for="t in pcrutypes" :key="t.id"
                 :class="t.activitytype_id != null ? 'active' : 'disabled'">
          <h3><code>[{{ t.id }}]</code>
            <strong>{{ t.label }}</strong>
          </h3>
          <div v-if="t.activitytype_id">
            Associé au activité de type <strong>{{ t.activitytype_label }}</strong>
          </div>
          <button class="btn xs" @click="handlerAssociate(t)">
            Associer à un type dans Oscar
          </button>
        </article>
      </div>
    </div>
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

export default {

  components: {
    "ajax-oscar": AjaxOscar,
    "tree": Tree
  },

  props: {
    url: {required: true}
  },

  data() {
    return {
      formData: null,
      remoterState: oscarRemoteData.state,
      configuration: null,
      oscartypes: [],
      pcrutypes: [],
      remoteState: oscarRemoteData.state,
      selectedPcru: null
    }
  },

  methods: {

    /**
     * Quand l'utilisateur à selectionner un type d'activité Oscar depuis le liste proposée.
     * @param evt
     */
    handlerSelect(evt) {
      console.log("Selection sur ", evt, 'pour', this.selectedPcru);
      let pcru = this.selectedPcru;
      let oscar = evt;
      let message = "Association des contrats '" + pcru.label + "' aux activités de recherche '" + oscar.label + "'";
      oscarRemoteData
          .setDebug(true)
          .setPendingMessage(message)
          .setErrorMessage("L'association a échouée.")
          .performPost(this.url, {
            pcru_id: pcru.id,
            oscar_id: oscar.id
          }, this.handlerAssociateSuccess);

      this.selectedPcru = null;
    },

    handlerClose(){
      console.log("CLOSE");
        this.selectedPcru = null;
    },

    handlerAssociateError(){
      console.log("handlerAssociateError", arguments);
    },

    handlerAssociateSuccess(){
      console.log("handlerAssociateSuccess", arguments);
      this.fetch();
    },

    /**
     * L'utilisateur a selection un type de contrat PCRU pour configurer l'association.
     * @param typePcru
     */
    handlerAssociate(typePcru) {
      this.selectedPcru = typePcru;
    },

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // TRAITEMENT des DONNEES

    /**
     * Chargement des données
     */
    fetch() {
      oscarRemoteData
          .setDebug(true)
          .setPendingMessage("Chargement des associations pour les types d'activité")
          .setErrorMessage("Erreur de chargement des associations des types activités")
          .performGet(this.url, this.handlerSuccess);
    },

    /**
     * Fin du chargement des données
     * @param success
     */
    handlerSuccess(success) {
      let datas = success.data;
      this.oscartypes = datas.activitytypes;
      this.pcrutypes = datas.pcrucontracttypes;
    }
  },

  mounted() {
    this.fetch();
  }

}
</script>