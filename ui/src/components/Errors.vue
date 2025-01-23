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
        <time class="time">{{ err.time }}</time>
        <span class="message">
        {{ err.message }}
        </span>
        <i class="icon-cancel-outline" @click="handlerRemoveError(i)"></i>
      </li>
    </ul>
  </div>
  <div class="oscar-error-fullscreen" v-if="errorFullScreen">
    <div class="content">
      <h3>
        <span><i class="icon-attention-circled"></i>Erreur</span>
        <nav>
          <a href="#" @click.prevent="handlerCloseErrorFullScreen">X</a>
        </nav>
      </h3>
      <pre>{{ errorFullScreen }}</pre>
    </div>
  </div>
  <div class="oscar-pending-fullscreen" v-if="pendingFullScreen && pending && pending.length">
    <div class="content">
      <div v-for="p in pending">
        <i class="icon-spinner animate-spin"></i>
        {{ p }}
      </div>
    </div>
  </div>
  <div class="oscar-pending">
    <div v-for="p in pending">
      <i class="icon-spinner animate-spin"></i>
      {{ p }}
    </div>
  </div>
</template>

<script>
import GlobalModel from "../models/GlobalModel.js";

export default {
  name: 'oscar-error',
  props: {},

  data() {
    return {
      display: true
    }
  },

  computed: {
    errors() {
      return GlobalModel.getters.errors;
    },
    pending() {
      return GlobalModel.getters.pending;
    },
    pendingFullScreen() {
      return GlobalModel.getters.pendingFullScreen;
    },
    errorFullScreen() {
      return GlobalModel.getters.errorFullScreen;
    }
  },

  watch: {
    errors() {
      this.display = true;
    },
    errorFullScreen() {
      this.display = false;
    }
  },

  methods: {
    handlerRemoveError(index) {
      GlobalModel.dispatch('removeError', index);
    },
    handlerPurge() {
      GlobalModel.dispatch('removeErrors');
    },
    handlerReduce() {
      this.display = false;
    },
    handlerCloseErrorFullScreen(){
      GlobalModel.commit('removeErrorFullScreen');
    }
  }
}
</script>

<style scoped>
.oscar-pending-fullscreen {
  background: rgba(255,255,255,.9);
  font-size: 1em;
  position: fixed;
  z-index: 20000;
  bottom: 0;
  right: 0;
  left: 0;
  top: 0;
  padding: .3em 1em;
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  .content {
    border: none;
  }
}
.oscar-error-fullscreen {
  background: rgba(255,255,255,.9);
  font-size: 1em;
  position: fixed;
  z-index: 20000;
  bottom: 0;
  right: 0;
  left: 0;
  top: 0;
  padding: .3em 1em;
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  .content {
    min-width: 50vw;
    max-width: 80vw;
    color: white;
    background: #b40a0a;
    box-shadow: 0 0 .2em rgba(0, 0, 0, 0.5);
    padding: 0;
    h3 {
      margin: 0;
      border-bottom: white thin solid;
      font-weight: 700;
      padding: .3em;
      display: flex;
      span {
        flex: 1;
      }

      nav {
        flex: 0;
        text-align: right;
        a {
          font-size: .8em;
          color: white;
        }
      }
    }
    pre {
      padding: 0;
      margin: .3em 1em;
    }
  }
}
.oscar-pending {
  background: white;
  font-size: 1em;
  position: fixed;
  z-index: 10000;
  bottom: 0;
  right: 0;
  padding: .3em 1em;
  border-radius: 8px 0 0 0;
  transition: bottom .25s ease-in-out;
  background: rgba(224, 226, 238, 1);
  color: #333;
  width: 25vw;
  height: auto;
}

.oscar-errors {
  transition: bottom .25s ease-in-out;
  position: fixed;
  bottom: -30vh;
  left: 0px;
  background: rgba(129, 10, 10, 0.7);
  color: #fff;
  z-index: 9000;
  width: 60vw;
  height: calc(30vh + 40px);

  .errors {
    height: 30vw;
    overflow-y: scroll;
  }

  h3 {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    margin: 0;
    padding: .25em 1em .25em .25em;
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

  .errors {
    list-style: none;
    padding: .25em 1em;
  }

  .error {
    border-bottom: 1px solid rgba(129, 10, 10, 1);

    .time {
      flex: 0 0;
      line-height: 1.8em;
      font-size: .75em;
      padding-right: 1em;
    }

    .message {
      flex: 1;
    }

    display: flex;
    justify-content: space-between;
    padding: .25em 1em;

    &:nth-child(even) {
      background-color: rgba(129, 10, 10, .5);
    }

    opacity: .7;

    &:hover {
      opacity: 1;
      background-color: rgba(129, 10, 10, 1);
    }
  }

  &.full {
    bottom: 0px;
  }
}

</style>