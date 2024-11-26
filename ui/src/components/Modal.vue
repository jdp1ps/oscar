<template>
  <transition name="fade">
    <div class="overlay" v-if="visible" @click="handlerClickOutside">
      <div class="overlay-content" @mouseenter="cursorIn = true" @mouseleave="cursorIn = false">
        <h2 class="overlay-title">
          <i :class="titleIcon" v-if="titleIcon !== 'no-icon'"></i>
          {{ title }}
          <a href="#" @click="handlerCancel" class="overlay-closer">x</a>
        </h2>
        <transition name="fade" mode="out-in">
        <div v-if="pending" class="overlay-pending">
          <i class="animate-spin icon-spinner"></i>{{ pendingMessage }}
        </div>
        <slot v-else>
          Some content here
        </slot>
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
    title: { type: String, required: true },
    titleIcon: { type: String, default: "no-icon" },
    visible: { type: Boolean, required: true },
    pending: { type: Boolean, required: false, default: false },
    pendingMessage: { type: String, required: false, default: "Chargement des données" },
  },

  data() {
    return {
      cursorIn: true
    }
  },

  methods: {
    handlerClickOutside() {
      if( this.cursorIn === false ){
        this.handlerCancel();
      }
    },

    handlerCancel() {
      this.$emit("modal-cancel");
    },

    handlerValid(){
      this.$emit("modal-valid");
    }
  }
}
</script>
<style scoped>
.overlay {
  z-index: 5000;
}

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

.overlay-buttons {
  text-align: center;
  border-top: thin solid #ddd;
  padding: .5em 0 .3em 0;
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