<template>
  <div class="oscar-errors" v-if="errors.length" :class="{'full': display}" @mouseenter="display = true">
    <h3>
      <span class="label">
        <i class="icon-bug"></i> Erreurs
        <sup>
          {{ errors.length }}
        </sup>
      </span>
      <small class="links">
        <a href="#" @click.prevent="handlerReduce"><i class="icon-angle-down"></i>Réduire</a>
        <a href="#" @click.prevent="handlerPurge"><i class="icon-trash"></i>Purger</a>
      </small>
    </h3>
    <ul class="errors">
      <li v-for="(err, i) in errors" class="error">
        {{ err }} <i class="icon-cancel-outline" @click="handlerRemoveError(i)"></i>
      </li>
    </ul>
  </div>
</template>

<script>
import GlobalModel from "../models/GlobalModel.js";
export default {
  name: 'oscar-error',
  props: {

  },

  data() {
    return {
      display: true
    }
  },

  computed: {
    errors(){
      return GlobalModel.getters.errors;
    }
  },
  watch: {
    errors(){
      console.log("update", GlobalModel.getters.errors);
      this.display = true;
    }
  },
  methods: {
    handlerRemoveError(index){
      GlobalModel.dispatch('removeError',index);
    },
    handlerPurge(){
      GlobalModel.dispatch('removeErrors');
    },
    handlerReduce(){
      this.display = false;
    }
  }
}
</script>

<style scoped>

.oscar-errors {
  transition: bottom .25s ease-in-out;
  position: fixed;
  bottom: -50vh;
  left: 0px;
  background: rgba(129, 10, 10, 0.7);
  color: #fff;
  z-index: 9000;
  width: 75vw;
  overflow-y: scroll;
  height: calc(50vh + 40px);
  h3 {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    margin: 0;
    padding: .25em .25em;
    border-bottom: solid 1px rgba(129, 10, 10, 0.7);
    background: rgba(129, 10, 10, 0.7);
    .links {
      display: flex;
      a {
        color: white;
        margin-left: 1em;
      }
    }
  }
  .error {
    opacity: .7;
    &:hover {
      opacity: 1;
    }
  }

  &.full {
    bottom: 0;
  }
}

</style>