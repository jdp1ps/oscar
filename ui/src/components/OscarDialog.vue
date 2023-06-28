<template>
  <div class="overlay" v-show="displayed">
    <div class="overlay-content">
      <h1 class="overlay-title" v-show="title">
        <i class="icon-help-circled"></i>
        {{ title }}
      </h1>
      <div class="overlay-message">
        {{ message }}
      </div>
      <nav class="buttons-bar">
        <button @click="handlerCancel" class="btn btn-danger">
          <i class="icon-cancel-circled-outline"></i>
          Annuler
        </button>
        <button @click="handlerSuccess" class="btn btn-success">
          <i class="icon-valid"></i>
          Confirmer
        </button>
      </nav>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    options: {default: null}
  },

  computed: {
    displayed() {
      if (this.options == null) {
        return false;
      } else {
        if (this.options.hasOwnProperty('display')) {
          return this.options.display;
        } else {
          return true;
        }
      }
    },
    title() {
      if (this.options && this.options.hasOwnProperty('title')) {
        return this.options.title;
      } else {
        return "";
      }
    },

    message() {
      if (this.options && this.options.hasOwnProperty('message')) {
        return this.options.message;
      } else {
        return "";
      }
    }
  },

  methods: {
    handlerCancel() {
      this.options.display = false;
    },
    handlerSuccess() {
      this.options.display = false;
      if (this.options.onSuccess)
        this.options.onSuccess();
    }
  }
}
</script>