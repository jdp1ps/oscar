<template>
  <div id="activitytype" class="type-tree-selector" @mouseleave="handlerHideSelector">
    <div class="value-area" v-show="!showSelector" @click="handlerShowSelector()">
      <span>
        <i class="icon" :class="{'icon-tag' : selectedItem.children.length == 0, 'icon-archive': selectedItem.children.length > 0}"></i>
        {{ selectedItem.label }}
      </span>
      <i class="icon-down-dir"></i>
    </div>
    <input type="hidden" :name="inputName" v-model="selectedItem.id" />
    <div class="search-area" v-show="showSelector" >
      <div class="search">
        <input type="search" ref="search"
             :placeholder="selectedItem.label"
             v-model="filter"
             class="input-search" />
      </div>
      <div class="icon">
        <i class="icon-trash" @click="selected = ''"></i>
      </div>
    </div>

    <div class="types-selector" v-show="showSelector">
      <activity-type-item v-for="item in options" :infos="item"
              @select="handlerSelect"
              :selected="selected"
              :allow-node-selection="allowNodeSelection"
              :filter="filter" :key="item.id"></activity-type-item>
    </div>
  </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
 node node_modules/.bin/vue-cli-service build --name ActivityTypeSelector --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/ActivityTypeSelector.vue

Pour compiler :
node node_modules/.bin/gulp contratTypePCRU

 */

export default {
  props: {
    // Données JSON pour les types d'activié
    typesAvailable: { required: true },

    // ID du type sélectionné
    initialSelected: { default: "" },

    //
    allowNodeSelection: { default: false },

    inputName: { default: "" }
  },

  data() {
    return {
      formData: null,
      configuration: null,
      types: [],
      filter: "",
      selected: null,
      showSelector: false
    }
  },

  computed: {
    options(){
      if( this.typesAvailable[0] ){
        return this.typesAvailable[0].children
      }
    },

    selectedItem(){
      if( !this.selected ){
        return {
          id: "",
          label: "",
          children: []
        }
      } else {
        return this.getItem(this.typesAvailable[0])
      }
    }
  },

  methods: {

    handlerShowSelector(){
      this.showSelector = true;
      this.focusSearchInput();
    },

    handlerHideSelector(){
      this.filter = "";
      this.showSelector = false;
    },

    focusSearchInput(){
      this.$nextTick(() => {
        this.$refs.search.focus();
      });
    },

    getItem(itemRoot){

      if( itemRoot.id == this.selected ){
        return itemRoot;
      }

      for( let i=0; i<itemRoot.children.length; i++ ){
        let item = itemRoot.children[i];
        let find = this.getItem(item);
        if( find ){
          return find;
        }
      }

      return null;
    },

    /**
     * Quand l'utilisateur à selectionner un type d'activité Oscar depuis le liste proposée.
     * @param evt
     */
    handlerSelect(evt) {
      this.selected = evt;
      this.handlerHideSelector();
      this.$emit('select', this.selectedItem.id);
    },

    handlerClose(){

    },

    handlerAssociateError(){
    },

    handlerAssociateSuccess(){
    },

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // TRAITEMENT des DONNEES

    /**
     * Chargement des données
     */
    fetch() {
    },

    /**
     * Fin du chargement des données
     * @param success
     */
    handlerSuccess(success) {
    }
  },

  mounted() {
    this.selected = this.initialSelected;
  }

}
</script>