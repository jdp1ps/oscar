<template>
  <!-- Impliquant la personne -->
  <div class="criteria card" :class="valueObj.type + error ? ' has-error' : ''">
    <span class="filter-label">
      <i :class="icon"></i>
      {{ label }}
      <input type="text" name="f[]" :value="valueObj.type +';' +valueObj.value1.join(',') +';' + valueObj.value2"/>
    </span>
    <span>
      <v-select
            v-model="valueObj.value1"
            :placeholder="placeholder"
            multiple
            label="label"
            multiple
            :reduce="item => item.id"
            :options="chooses"
             />
      <div class="alert alert-danger" v-if="error">
       {{ error }}
      </div>
    </span>
    <span class="nav-actions" @click.prevent="$emit('delete')">
      <i class="icon-trash"></i>
    </span>
  </div>
</template>
<script>

import vSelect from 'vue-select';

export default {
  props: {
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
        value1: this.value1.split(','),
        value2: this.value2
      }
    }
  },

  computed: {
    chooses(){
       console.log("build chooses : ", this.options);
      let out = [];
      Object.keys(this.options).forEach(key => {
        out.push({
          id: key,
          label: this.options[key]
        })
      });
      return out;
    }
  },

  components: {
    vSelect
  },

  methods: {
    setValue(val) {
      let split = val.split(';');
      this.valueObj.type = split[0];
      this.valueObj.value1 = split[1];
      this.valueObj.value2 = split[2] ? split[2] : '';
    }
  },

  mounted() {
    if (this.value) {
      this.setValue(this.value);
    }
  }
}
</script>