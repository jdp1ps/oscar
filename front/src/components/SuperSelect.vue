<template>
  <div class="superselect" @mouseleave="mode='display'" @click="handlerSwitchMode">
    <span>
      <span class="labeling">
        <em v-if="selected.length == 0" style="cursor: pointer">{{ label }}</em>
        <strong class="cartouche" v-for="v in selected">{{ options[v] }}</strong>
      </span>
      <span><i class="icon-down-dir"></i></span>
    </span>

    <div v-show="mode == 'edit'" class="selector">
      <input type="hidden" :value="sendValue" :name="name" />
      <div v-for="label, key in options">
        <label :for="'choose-' +key">
          <input type="checkbox" v-model="selected"
                 :id="'choose-' +key"
                 :value="key"
                  />
          {{ label }}
        </label>
      </div>
      <hr>
      <button @click.prevent="value = []" class="btn btn-default">Effacer</button>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    options: {
      required: true
    },
    value: {
      required: true
    },
    label: {
      default: "Selectionnez une valeur"
    },
    name: {
      default: "foo"
    }
  },
  watch:{
    selected(){
      this.value = this.sendValue;
      this.$emit('change', this.sendValue);
      this.$emit('input', this.sendValue);
    }
  },
  computed: {
    sendValue(){
      return this.selected ? this.selected.join(',') : '';
    }
  },
  methods: {
    handlerSwitchMode(evt){
      this.mode = this.mode == 'display' ? 'edit' : 'display';
    }
  },
  data(){
    return {
      open: false,
      mode: 'display',
      selected: []
    }
  },

  mounted(){
    console.log('options:', this.options);
    console.log('value:', this.value);
    if( this.value ){
      this.selected = this.value.split(',');
    }
  }
}
</script>