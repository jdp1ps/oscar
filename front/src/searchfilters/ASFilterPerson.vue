<template>
  <!-- Impliquant la personne -->
  <div class="criteria card" :class="valueObj.type + error ? ' has-error' : ''">
    <span class="filter-label">
      <i class="icon-user"></i>
      {{ label }}
      <input type="hidden" name="f[]" :value="valueObj.type +';' +valueObj.value1 +';' + valueObj.value2"/>
    </span>
    <span>
      <person-auto-completer v-model="valueObj.value1" :multiple="multiple" />
      <div class="alert alert-danger" v-if="error">
       {{ error }}
      </div>
    </span>
    <span v-if="multiple == false">
      ayant le rôle
      <select v-model="valueObj.value2">
        <option value="-1">N'importe quel role</option>
        <option :value="id" v-for="role,id in roles_values">{{ role }}</option>
      </select>
    </span>
    <span class="nav-actions" @click.prevent="$emit('delete')">
      <i class="icon-trash"></i>
    </span>
  </div>
</template>
<script>

import PersonAutoCompleter from "../components/PersonAutoCompleter2"

export default {
  props: {
    value: {require: true},
    value1: {require: true},
    value2: {require: true},
    error: {require: false, default: ""},
    label: {default: "Impliquant la personne"},
    type: {default: "ap"},
    multiple: { default: false },
    initaleOptions: [],

    searched_values: [],
    roles_values: []
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

  components: {
    PersonAutoCompleter
  },

  computed: {},

  methods: {
    setValue(val) {
      let split = val.split(';');
      this.valueObj.type = split[0];
      this.valueObj.value1 = split[1];
      this.valueObj.value2 = split[2];
    }
  },

  mounted() {
    if (this.value) {
      this.setValue(this.value);
    }
  }
}
</script>