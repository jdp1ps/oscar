<template>
  <!-- Impliquant la personne -->
  <div class="criteria card" :class="valueObj.type + error ? ' has-error' : ''">
    <span class="filter-label">
      <i class="icon-user"></i>
      {{ label }}
      <input type="hidden" name="f[]" :value="valueObj.type +';' +valueObj.value1 +';' + valueObj.value2"/>
      <small>{{ options }}</small>
    </span>
    <span>
      <v-select
            placeholder="Rechercher une personne"
            :options="foo"
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
    label: {default: "Liste (label)"},
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
    foo(){
      return [ "POLOGNE", "Sénégal", "Cambodge", "Irelande", "Inde", "ITALY", "Italy", "Italie", "Tunisie", "USA", "Ile Maurice", "Irelande du Nord", "Chili", "Finlande", "Grande Bretagne", "Canada", "Afrique du Sud", "Chine", "ITALIE", "SPAIN", "Etas-Unis", "Cuba", "England", "Grèce", "Maryland", "India", "Allemagne", "Iran", "France", "Suisse", "Danemark", "Vietnam", "Espagne", "Tahiti", "FRANCE", "Irlande", "Norvège", "New Jersey", "Brésil", "Roumanie", "", "Australia", "Munich", "Suède", "Belgique", "F", "Etats-Unis", "GERMANY", "Spain", "Royaume Uni", "angleterre", "France ", "Mali", "Japon", "Mexique", "United Kingdom, PE1 1JY", "BELGIQUE", "Maroc", "35000", "ESPAGNE", "Algérie", "Taiwan", "Angleterre" ] ;
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