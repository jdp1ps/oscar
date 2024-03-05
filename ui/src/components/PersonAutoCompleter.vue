<template>
  <div>
    <div class="alert alert-danger" v-if="error">
      {{ error }}
      <button class="btn btn-default" @click="error = ''">Fermer</button>
    </div>
    <input type="text" v-model="expression" @keyup.enter.prevent="search"/>
    <span v-show="loading">
      <i class="icon-spinner animate-spin"></i>
    </span>
    <div class="choose"
         style="position: absolute; z-index: 3000; max-height: 400px; overflow: hidden; overflow-y: scroll"
         v-show="persons.length > 0 && showSelector">
      <div class="choice" :key="c.id" v-for="c in persons" @click.prevent.stop="handlerSelectPerson(c.id)">
        <div style="display: block; width: 50px; height: 50px">
          <img :src="'https://www.gravatar.com/avatar/'+c.mailMd5+'?s=50'" :alt="c.displayname" style="width: 100%"/>
        </div>
        <div class="infos">
          <strong style="font-weight: 700; font-size: 1.1em; padding-left: 0">{{ c.displayname }}</strong><br>
          <span style="font-weight: 100; font-size: .8em; padding-left: 0"><i class="icon-location"></i>
                        {{ c.affectation }}
                        <span v-if="c.ucbnSiteLocalisation"> ~ {{ c.ucbnSiteLocalisation }}</span>
                    </span><br>
          <em style="font-weight: 100; font-size: .8em"><i class="icon-mail"></i>{{ c.email }}</em>
        </div>
      </div>
    </div>
    <div class="alert alert-danger" v-if="error">
      <i class="icon-attention-1"></i>
      {{ error }}
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
      error: ""
    }
  },
  watch: {
    expression(n, o) {

      if (n.length >= 2) {
        if (tempo) {
          clearTimeout(tempo);
        }
        tempo = setTimeout(() => {
          this.search();
        }, 500)

      }
    }
  },
  methods: {
    search() {
      this.loading = true;
      axios.get(this.url + this.expression+'&f=json', {
        before(r) {
          if (this.request) {
            this.request.abort();
          }
          this.request = r;
        }
      }).then(
          ok => {
            console.log(ok);
            this.persons = ok.data.datas;
            this.showSelector = true;
          },
          ko => {
            console.log(ko);
            if( ko.status == 403 ){
              this.error = "403 Unauthorized";
            }
            else if( ko.message ){
              this.error = 'ERROR : ' + ko.message;
            }
            else if( ko.body ){
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
