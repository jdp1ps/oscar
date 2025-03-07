<template>
  <div v-if="modal" class="overlay confirm-dialog">
    <div class="overlay-content">
      <h3>
        <i class="icon-attention-1"></i>
        <slot name="title">
          Confirmer ?
        </slot>
      </h3>
      <div class="overlay-message">
        <slot name="message">
          Voulez-vous continuer ?
        </slot>
      </div>
      <div v-if="checkbox">
        <label for="confirm_ok" class="checkbox">
          <input type="checkbox" id="confirm_ok" v-model="checked" />
          J'ai bien compris
        </label>
      </div>
      <nav>
        <a href="#" @click.prevent="handlerCancel" class="btn btn-danger">
          <i class="icon-block"></i>
          Annuler
        </a>
        <a href="#" @click.prevent="handlerConfirm" class="btn btn-success" :class="{'disabled': !confirmEnabled}">
          <i class="icon-ok-circled"></i>
          Confirmer</a>
      </nav>
    </div>
  </div>
  <a href="#" :class="class" @click.prevent="modal = true">
    <slot>Texte par défaut</slot>
  </a>
</template>
<script>
export default {
  name: 'ButtonConfirm',
  props: {
    class: {
      type: String,
      default: 'btn btn-default'
    },
    checkbox: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      modal: false,
      checked: false
    }
  },
  computed: {
    confirmEnabled () {
      return (this.checkbox && this.checked) || this.checkbox === false;
    }
  },
  watch: {
    modal(val) {
      this.checked = false;
    }
  },
  methods: {
    handlerClick() {
      this.$emit('click');
    },
    handlerCancel() {
      this.modal = false;
      this.$emit('cancel');
    },
    handlerConfirm() {
      if( this.confirmEnabled ){
        this.modal = false;
        this.$emit('confirm');
      }
    }
  },
  activated() {
    console.log('ButtonConfirm activated');
  }
}
</script>
<style scoped>
.confirm-dialog {
  z-index: 10000;

  .overlay-content {
    flex-basis: 30% !important;

    h3 {
      border-bottom: thin solid #eee;
      margin: .25em .5em;
      padding: .25em .5em;
    }

    .overlay-message {
      font-size: 1.4em;
    }

    nav {
      height: 50px;
      width: 100%;
      padding: .25em .5em;
      margin: .25em .5em;
      border-top: thin solid #eee;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-content: space-between;

      a.btn {

      }
    }
  }
}
</style>