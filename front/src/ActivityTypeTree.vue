<template>
  <div>
    <strong @click="handlerSelect">{{ tree.label}}</strong>
    <div v-if="tree.children">
      <tree v-for="c in tree.children" :tree="c" :key="c.id" @select="$emit('select', $event)"></tree>
      {{ tree.children }}
    </div>
  </div>
</template>
<script>
export default {
  props: {
    tree: { required: true }
  },
  components: {
    'tree': this
  },
  methods: {

    handlerSelect() {
      this.$emit("select", this.tree);
    }
  },

  mounted(){
    console.log("mounted tree");
  }

}
</script>