<template>
  <div class="item-tree item" v-show="show">
    <div class="item-label" @click="handlerEmitSelect()" :style="{'pointer-events': selectable ? 'auto' : 'none', 'text-decoration': selectable ? 'underline' : 'none'}">
      <i class="icon-archive" v-if="isDir"></i>
      <i class="icon-tag" v-else></i>
      <strong>{{ infos.label }}</strong>
      <small v-if="route"> ~ {{ route }}</small>
    </div>

    <div v-if="infos.children.length > 0" class="children">
      <activity-type-item v-for="child in infos.children"
          @select="relayEmitSelect"
          :class="{'selected': selected == child.id}"
          :selected="selected"
          :infos="child" :key="infos.id"
          :filter="filter" :route="customRoute"></activity-type-item>
    </div>
  </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
 node node_modules/.bin/vue-cli-service build --name ActivityTypeItem --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/components/ActivityTypeItem.vue

Pour compiler :
node node_modules/.bin/gulp contratTypePCRU

 */
export default {
  props: {
    // Données de l'option { id: INT, label: STRING, children: ARRAY }
    infos: { required: true },

    // Chaîne de recherche
    filter: { default: "" },

    // Chemin
    route: { default: "" },

    // ID de l'item selectionné
    selected: { default: null },

    // Authorise la selection d'item avec des enfants
    allowNodeSelection: { default: false }
  },


  computed: {
    /**
     * @returns {boolean}
     */
    selectable(){
      if( this.allowNodeSelection == true ){
        return true;
      } else {
        return this.infos.children.length == 0;
      }
    },

    /**
     * Est un "dossier" (contient des enfants)
     * @returns {boolean}
     */
    isDir(){
      return this.infos.children.length > 0
    },

    /**
     * L'élément est visible ?
     * @returns {boolean}
     */
    show(){
      let displayed = this.filter == "" || this.searchableText.indexOf(this.filter.toLowerCase()) >= 0;
      if( displayed == false && this.infos.children.length > 0 ){
        for( let i = 0; i<this.infos.children.length; i++ ){
          let child = this.infos.children[i];
          if( child.label.toLowerCase().indexOf(this.filter.toLowerCase()) >= 0 ){
            return true;
          }
        }
      }
      return displayed;
    },

    /**
     * Retourne le "chemin"
     * @returns {*}
     */
    customRoute(){
      let r = this.route.split(',');
      r.push(this.infos.label);
      return r.join(',');
    },

    searchableText(){
      return (this.infos.label +" "+ this.route).toLowerCase();
    }
  },


  methods: {
    handlerEmitSelect( id = null ){
      console.log("SELECT !", this.selectable);
      if( this.selectable ){
        this.relayEmitSelect(id);
      }
    },

    relayEmitSelect( id = null ){
      let send = id ? id : this.infos.id;
      this.$emit('select', send);
    }
  }
}
</script>