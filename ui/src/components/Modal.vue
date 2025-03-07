<template>
  <transition name="fade">
    <div class="overlay" v-if="visible" @click="handlerClickOutside">
      <div class="overlay-content" @mouseenter="cursorIn = true" @mouseleave="cursorIn = false">
        <h2 class="overlay-title">
          <i :class="titleIcon" v-if="titleIcon !== 'no-icon'"></i>
          {{ title }}
          <a href="#" @click.prevent="handlerCancel" class="overlay-closer">x</a>
        </h2>
        <transition name="fade" mode="out-in">
          <div class="overlay-body">
            <div v-if="pending" class="overlay-pending">
              <i class="animate-spin icon-spinner"></i>{{ pendingMessage }}
            </div>
            <slot v-else>
              Some content here
            </slot>
          </div>
        </transition>
        <nav class="overlay-buttons">
          <slot name="buttons">
            <button class="btn btn-danger" @click="handlerCancel">Annuler</button>
            <button class="btn btn-success" @click="handlerValid">Confirmer</button>
          </slot>
        </nav>
      </div>
    </div>
  </transition>
</template>
<script>
export default {
  name: "Modal",
  props: {
    title: {type: String, required: true},
    titleIcon: {type: String, default: "no-icon"},
    visible: {type: Boolean, required: true},
    pending: {type: Boolean, required: false, default: false},
    pendingMessage: {type: String, required: false, default: "Chargement des données"},
  },

  data() {
    return {
      cursorIn: true
    }
  },

  methods: {
    handlerClickOutside() {
      if (this.cursorIn === false) {
        this.handlerCancel();
      }
    },

    handlerCancel() {
      this.$emit("modal-cancel");
    },

    handlerValid() {
      console.log("emit : modal-valid")
      this.$emit("modal-valid");
    }
  }
}
</script>
<style scoped>
.overlay {
  z-index: 5000;

  .overlay-content {
    height: 90vh;
    position: relative;

    .overlay-pending {
      font-weight: 600;
      min-height: 10em;
      line-height: 10em;
      text-align: center;

      -webkit-animation-name: animation;
      -webkit-animation-duration: 1s;
      -webkit-animation-timing-function: ease-in-out;
      -webkit-animation-iteration-count: infinite;
      -webkit-animation-play-state: running;

      animation-name: animation;
      animation-duration: 1s;
      animation-timing-function: ease-in-out;
      animation-iteration-count: infinite;
      animation-play-state: running;
    }

    .overlay-title {
      color: #333;
      padding: .25em;
      position: relative;
      height: 2em;
      .overlay-closer {
        position: absolute;
        right: 0em;
        top: 0;
      }
    }

    .overlay-buttons {
      text-align: center;
      padding: .5em 0 .3em 0;
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
    }

    .overlay-body {
      border: thin solid #eee;
      background: #FEFEFE;
      margin: 1em;
      position: absolute;
      top: 2.5em;
      left: 0;
      right: 0;
      bottom: 2.5em;
      overflow: scroll;
    }
  }
}


@-webkit-keyframes animation {
  0% {
    color: #333333;
  }
  50.0% {
    color: #0a53be;
  }
  100.0% {
    color: #333333;
  }
}

@keyframes animation {
  0% {
    color: #333333;
  }
  50.0% {
    color: #0a53be;
  }
  100.0% {
    color: #333333;
  }
}
</style>