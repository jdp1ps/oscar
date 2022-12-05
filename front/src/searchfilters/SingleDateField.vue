<template>
  <!-- Impliquant la personne -->
  <div class="criteria card" :class="valueObj.type + error ? ' has-error' : ''">
    <span class="filter-label">
      <i class="icon-calendar"></i>
      {{ label }}
      <input type="hidden" name="f[]" :value="valueObj.type +';' +valueObj.value1 +';' + valueObj.value2"/>
    </span>

    <span class="date-input">
      <span>
        Entre / à partir
      </span>
      <span>
        <datepicker :moment="moment" v-model="valueObj.value1" />
      </span>
    </span>

    <span class="date-input">
      <span>
        Jusqu'à
      </span>
      <span>
        <datepicker :moment="moment" v-model="valueObj.value2" :format="'YYYY-mm-dd'" />
      </span>
    </span>

      <div class="alert alert-danger" v-if="error">
       {{ error }}
      </div>

    <span class="nav-actions" @click.prevent="$emit('delete')">
      <i class="icon-trash"></i>
    </span>
  </div>
</template>
<script>

import Datepicker from "../components/Datepicker";

export default {
  props: {
    moment: {require: true},
    value: {require: true},
    value1: {require: true},
    value2: {require: true},
    error: {require: false, default: ""},
    icon: { default: 'icon-tag' },
    label: {default: "Liste (label)"},
    placeholder: {default: ""},
    type: {default: "ap"},
    multiple: { default: false },
    options: { default: []}
  },

  data() {
    return {
      valueObj: {
        type: this.type,
        value1: this.value1,
        value2: this.value2
      }
    }
  },

  computed: {

  },

  components: {
    Datepicker
    //Datepicker
  },

  methods: {
    setValue(val) {
      console.log('setValue', val);
      if( val ){
        let split = val.split(';');
        this.valueObj.type = split[0];
        this.valueObj.value1 = split[1];
        this.valueObj.value2 = split[2] ? split[2] : '';
      }
    }
  },

  mounted() {
    console.log("Datepicker mounted");
    if (this.value) {
      this.setValue(this.value);
    }
  }
}
</script>