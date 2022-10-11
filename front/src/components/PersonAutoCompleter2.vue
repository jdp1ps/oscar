<template>
  <v-select @search="handlerSearch"
            placeholder="Rechercher une personne"
            :options="options"
            label="label"
            multiple
            :selectable="() => selectable"
            v-model="value"
            :reduce="item => item.id"
            @input="setSelected"
  >
    <template #option="{ id,label, firstName, lastName, affectation, ucbnSiteLocalisation }" class="result-item">
      <div class="result-item" :class="{'person-closed': closed }">
        <h4 style="margin: 0">
          <span>
            <em>{{ firstName }}</em>
            <strong>{{ lastName }}</strong>
          </span>
        </h4>
        <div v-if="affectation || location" class="location">
          <i class="icon-location"></i>
          <strong v-if="affectation">{{ affectation }}</strong>
          <em v-if="ucbnSiteLocalisation">{{ ucbnSiteLocalisation }}</em>
        </div>
      </div>
    </template>
  </v-select>
</template>
<script>

/**
 * {
	"2": {
		"id": 11232,
		"label": "!FERME! [904] UFR SCIENCES HOMME ",
		"closed": true
	}
}
 */
import vSelect from 'vue-select';


let intify = function( value ){
  if( value ){
    let ints = value.split(',');
    return ints.map( i => parseInt(i));
  }
  return [];
}

export default {
  props: {
    value: { default: null },
    multiple: { default: false }
  },

  components: {
    vSelect
  },

  data(){
    return {
      options: [],
      lastUpdatedSearch: 0,
      delay: null,
      preloadedValue: false,
      selectedValue: null
    }
  },



  mounted() {
    if( this.value ){
      this.selectedValue = this.value;
      this.options.push({
        id: this.value,
        label: "Waiting for data"
      })
      this.value = null;
      this.searchPerson(null, 'id:' +this.selectedValue);
    } else {
      this.preloadedValue = true;
    }
  },

  computed: {
    values(){
      if( !Array.isArray(this.value) ){
        return this.value ? intify(this.value) : [];
      } else {
        return this.value;
      }
    },
    selectable(){
      if( this.multiple ) return true;
      else {
        return this.value.length == 0;
      }

    }
  },

  methods: {

    isSelectable(){
      return this.selectable;
    },

    setSelected(selected){
      this.value = selected;
      this.$emit('change', this.value);
      this.$emit('input', this.value);
    },

    handlerSearch(search, loading) {
      if (search.length) {
        loading(true);
        // Système de retardement Eco+
        let delayFunction = function(){
          this.searchPerson(loading, search, this);
          this.delay = null;
        }.bind(this);

        if( this.delay != null ) {
          clearTimeout(this.delay);
        }
        this.delay = setTimeout(delayFunction, 1000);
      }
    },

    searchPerson(loading, search, vm) {
      this.$http.get('/person?l=m&q=' + encodeURI(search)).then(
          ok => {
            // Cas 1 : Premier chargement
            if( this.preloadedValue == false ){
              console.log("Préchargement");
              this.preloadedValue = true;
              this.options = ok.data.datas;
              this.value = intify(this.selectedValue);
              console.log(this.options, typeof this.selectedValue);
            } else {
              console.log("Résultat de recherche");
              let newOptions = [];
              if( this.options && this.value ){
                console.log("On garde les anciennes données ?", this.values);
                this.options.forEach(item => {
                  console.log(item.id, item.label, this.values.indexOf(item.id));
                  if( this.values.indexOf(item.id) >= 0 ){
                    console.log("On garde ", item);
                    newOptions.push(item);
                  }
                });
              }

              console.log("on ajoute les résultats de la recherche")
              ok.data.datas.forEach(item => {
                newOptions.push(item)
              });

              console.log("On affecte", JSON.parse(JSON.stringify(newOptions)));
              this.options = newOptions;
            }

            if( loading )
              loading(false);
          },
          ko => {

          }
      )
    },
  }
}
</script>