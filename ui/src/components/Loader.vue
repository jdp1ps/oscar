<template>
  <transition mode="out-in" name="fade">
    <div class="loader" v-if="visible">
      <div class="message">
        <div>
          <i class="icon-spinner animate-spin"></i> {{ text }}
        </div>
        <div class="from" v-if="from">
          <small>({{ from }})</small>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'Loader',
  props: {
    text: {
      type: String,
      default: 'Loading...',
      required: false
    },
    from: {
      type: String,
      default: '',
      required: false
    },
    visible: {
      type: Boolean,
      required: true
    }
  }
}
</script>

<style scoped>
.loader {
  background-color: rgba(255,255,255,.8);
  display: flex;
  align-items: center;
  justify-content: center;
  .message {
    text-align: center;
    font-size: 1em;
    font-weight: 600;
    -webkit-animation-name: animation;
    -webkit-animation-duration: .5s;
    -webkit-animation-timing-function: ease-in-out;
    -webkit-animation-iteration-count: infinite;
    -webkit-animation-play-state: running;

    animation-name: animation;
    animation-duration: .5s;
    animation-timing-function: ease-in-out;
    animation-iteration-count: infinite;
    animation-play-state: running;
    .from {
      font-size: .7em;
      font-weight: 300;
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