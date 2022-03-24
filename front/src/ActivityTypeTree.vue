<template>
  <div class="tree">

    <span @click="open = !open" v-if="tree.children.length > 0" class="open-handler">
      <i class="icon-right-dir" v-if="!open"></i>
      <i class="icon-down-dir" v-else></i>
    </span>

    <strong @click="handlerSelect" class="select-handler">{{ tree.label == 'ROOT' ? './' : tree.label }}</strong>
    <div v-if="tree.children.length > 0" class="children" v-show="open">
      <tree v-for="c in tree.children" :tree="c" :key="c.id" @select="$emit('select', $event)"></tree>
    </div>
  </div>
</template>


<script>
export default {
  props: {
    tree: { required: true }
  },

  data(){
    return {
      open: false
    }
  },

  methods: {

    handlerSelect() {
      this.$emit("select", this.tree);
    }
  },

  beforeCreate: function () {
    this.$options.components.tree = require('./ActivityTypeTree.vue').default
  },

  mounted(){
    console.log("mounted tree");
    if( this.tree.label == "ROOT" ){
      this.open = true;
    }
  }

}
</script>