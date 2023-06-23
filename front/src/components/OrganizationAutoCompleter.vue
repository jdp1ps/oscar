<template>
  <div>
    <small>{{ filteredOptions.length }}</small>
    <v-select @search="handlerSearchOrganisation"
              placeholder="Rechercher une structure"
              :options="options"
              label="label"
              v-model="value"
              :reduce="item => item.id"
              :dropdown-should-open="dropdownShoultOpen"
              @input="setSelected"
    >
      <template #list-header>
        <li style="text-align: center">
          <label for="display_closed">
            Afficher les structures fermées
            <input type="checkbox" v-model="displayClosed" id="display_closed" />
          </label>
        </li>
      </template>

      <template #option="{ id,code, shortname, longname, city, country, label, closed, email, phone }"
                class="result-item">
        <div class="result-item" :class="{'organization-closed': closed }">
          <h4 style="margin: 0">
            <code v-if="code">{{ code }}</code>
            <span>
              <strong v-if="shortname">{{ shortname }}</strong>
              <em v-if="longname">{{ longname }}</em>
            </span>
          </h4>
          <div v-if="email || phone" class="infos">
            <span v-if="email"><i class="icon-mail"></i> {{ email }}</span>
            <span v-if="phone"><i class="icon-phone-outline"></i> {{ phone }}</span>
          </div>
          <div v-if="country || city" class="location">
            <i class="icon-location"></i>
            <strong v-if="city">{{ city }}</strong>
            <em v-if="city">{{ country }}</em>
          </div>
        </div>
      </template>
    </v-select>
  </div>
</template>
<script>

import vSelect from 'vue-select';

export default {

  props: {
    value: {default: null}
  },

  components: {
    vSelect
  },

  data() {
    return {
      // Liste des structures
      options: [],

      lastUpdatedSearch: 0,
      delay: null,
      preloadedValue: false,
      selectedValue: null,
      displayClosed: false,
      last_search: "",
      loading_ref: null
    }
  },


  mounted() {
    // Détection d'un valeur initiale
    if (this.value) {
      this.selectedValue = this.value;
      this.options.push({
        id: this.value,
        label: "Waiting for data"
      })
      this.value = null;
      this.searchOrganization(null, 'id:' + this.selectedValue);
    } else {
      this.preloadedValue = true;
    }
  },

  computed: {
    filteredOptions(){
      let opts = [];
      if( this.displayClosed ){
        return this.options;
      } else {
        this.options.forEach(item => {
          if( !item.closed ){
            opts.push(item);
          }
        });
        return opts;
      }
    }
  },

  methods: {

    /**
     * Selection d'une option.
     *
     * @param selected
     */
    setSelected(selected) {
      this.value = selected;
      this.$emit('change', this.value);
      this.$emit('input', this.value);
    },

    /**
     * Déclenchement de la recherche (à la saisie).
     *
     * @param search
     * @param loading
     */
    handlerSearchOrganisation(search, loading) {
      this.last_search = search;
      this.loading_ref = loading;
      if (search.length) {
        loading(true);
        // Système de retardement Eco+
        let delayFunction = function () {
          this.searchOrganization(loading, search, this);
          this.delay = null;
        }.bind(this);
        if (this.delay != null) {
          clearTimeout(this.delay);
        }
        this.delay = setTimeout(delayFunction, 300);
      }
    },

    /**
     * Recherche via l'API
     * @param loading
     * @param search
     * @param vm
     */
    searchOrganization(loading, search, vm) {

      let closeOpt = this.displayClosed ? '&active=' : '&active=ON'

      console.log("PRELOADVALUE:", this.preloadedValue);
      this.$http.get('/organization?l=m&q=' + encodeURI(search)).then(
          ok => {
            if (this.preloadedValue == false) {
              this.preloadedValue = true;
              this.value = this.selectedValue;
              this.options[0].label = ok.data.datas[0].label;
            } else {
              this.options = ok.data.datas;
            }
            if (loading)
              loading(false);
          },
          ko => {
            // TODO
          }
      )
    },
    dropdownShouldOpen(VueSelect) {
      console.log("dropdownShouldOpen");
      return VueSelect.close;
    },
  }
}
</script>