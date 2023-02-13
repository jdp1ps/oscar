<script>
export default {
  data(){
    return {
      count: 0
    }
  },
  methods:{
    handelerIncrement(){
      this.count++;
    }
  }
}
</script>

<template>
  <h1>Example</h1>
  <button @click="count++">Clicked {{ count }} time(s).</button>
</template>

<style scoped>
  h1 {
    color: red;
  }
</style>