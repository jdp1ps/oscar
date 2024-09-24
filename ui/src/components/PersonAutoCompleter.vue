<template>
  <div class="oscar-selector">
    <div class="input-group" style="position: relative;">
      <div v-if="error" class="displayed-value text-danger" style="">
        <i class="icon-attention-1"/>
        {{ error }}
        <i @click="error = ''" class="icon-cancel-circled-outline button-cancel-value"></i>
      </div>
      <div v-if="displayValue" class="displayed-value" style="">
        {{ selectedLabel }}
        <i v-if="selectedValue" @click="handlerUnselect" class="icon-cancel-circled-outline button-cancel-value"></i>
      </div>
      <span class="input-group-addon">
            <i v-show="loading" class="icon-spinner animate-spin"></i>
            <i v-show="!loading && noresult == false" class="icon-user"></i>
            <i v-show="noresult" class="icon-user-times"></i>
          </span>
      <input type="text" v-model="expression"
             placeholder="Rechercher une personne..."
             class="form-control"/>
    </div>


    <div class="options" v-show="showSelector && persons.length">
      <header>
        Résultat(s) : {{ persons.length }} /
        <label for="hidder">
          Afficher les comptes expirés
          <input type="checkbox" id="hidder" value="on" v-model="displayClosed"/>
        </label>
      </header>
      <div class="option" v-for="c, i in optionsFiltered"
           @mouseover="highlightedIndex = i"
           @click.prevent.stop="handlerSelectPerson(c.id)"
           :id="'item_'+i"
           :class="{
                 'active': i == highlightedIndex,
                 'selected': c.id == selectedValue,
                 'closed': c.closed
               }">
        <div class="option-title">
          <span style="display: inline-block; width: 50px; height: 50px">
          <img :src="'https://www.gravatar.com/avatar/'+c.mailMd5+'?s=50'" :alt="c.displayname" style="width: 100%"/>
          </span>
          <strong class="displayname" style="font-weight: 700; font-size: 1.1em; padding-left: 0">
            {{ c.displayname }}
            <em v-if="c.email" style="font-weight: 100; font-size: .9em"> ({{ c.email }})</em>
          </strong>
        </div>
        <div class="option-infos">
          <span>
              <i class="icon-location"></i>
              {{ c.affectation }}
              <span v-if="c.ucbnSiteLocalisation"> ~ {{ c.ucbnSiteLocalisation }}</span>
          </span>
        </div>
      </div>

    </div>
  </div>

</template>
<script>

import axios from 'axios';

let tempo;


export default {
  // Ajout props pour config url si souhaité (respecter Api de retour)
  props: {
    url: {
      default: "/person?l=m&q="
    }
  },
  data() {
    return {
      //url: "/person?l=m&q=",
      persons: [],
      expression: "",
      loading: false,
      selectedPerson: null,
      showSelector: true,
      request: null,
      displayClosed: false,
      highlightedIndex: null,
      noresult: false,
      error: ""
    }
  },

  computed: {
    optionsFiltered() {
      let opts = [];
      if (this.displayClosed) {
        return this.persons;
      } else {
        this.persons.forEach(item => {
          if (!item.closed) {
            opts.push(item);
          }
        });
        return opts;
      }
    }
  },

  watch: {
    expression(n, o) {
      console.log('Expression changed');
      if (n.length >= 2) {
        if (tempo) {
          console.log('Reset de la recherche');
          clearTimeout(tempo);
        }
        tempo = setTimeout(() => {
        console.log('Déclenchement de la recherche');
          this.search();
          clearTimeout(tempo);
        }, 500)

      }
    }
  },
  methods: {
    search() {
      this.loading = true;
      this.noresult = false;
      axios.get(this.url + this.expression + '&f=json', {
        before(r) {
          if (this.request) {
            this.request.abort();
          }
          this.request = r;
        }
      }).then(
          ok => {
            this.persons = ok.data.datas;
            this.showSelector = true;
            this.noresult = !this.persons || this.persons.length === 0;
            this.error = this.persons.length + " résultat(s)";
          },
          ko => {
            this.persons = [];
            this.noresult = false;
            if (ko.status === 403) {
              this.error = "403 Unauthorized";
            } else if (ko.response.data) {
              this.error = 'ERROR : ' + ko.response.data;
            } else if (ko.body) {
              this.error = ko.body;
            }
          }
      ).then(foo => {
        this.loading = false;
        this.request = null;
      });
    },
    handlerSelectPerson(id) {
      let data = this.persons.find(obj => obj.id == id);
      console.log('handlerSelectPerson', data);
      this.selectedPerson = data;
      this.showSelector = false;
      this.expression = "";
      this.$emit('change', data);
    }
  }
}
</script>
