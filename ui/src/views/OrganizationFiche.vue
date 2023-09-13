<template>
  <div class="loader" v-if="loading">
    Chargement
  </div>
  <header v-else>
    <h2>
      <small class="parents">
        PARENTS ICI
      </small>
      <small class="type">
        Type: {{ infos.type }}
      </small>
      <div class="fullname">
        <code title="Code interne">
          {{ infos.code }}
        </code>
        <strong>
          {{ infos.shortname }}
        </strong>
        <em>
          {{ infos.longname }}
        </em>
      </div>
    </h2>
    Données de la structures
  </header>
  <section class="row">
    <div class="col-md-8">
      <h3>Sous-Structures</h3>
      Structures / Pesonnel
      <h3>Personnel</h3>
    </div>
    <div class="col-md-4">
      <h3>Informations</h3>

      <pre v-if="infos">
        {{ infos }}
      </pre>
    </div>
  </section>
  FICHE

  <footer>
    <button @click="loadInfos">
      Charger
    </button>
  </footer>
</template>
<script>

import axios from "axios";
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default {
  props: {
    entrypoint: {
      required: true
    }
  },

  data(){
    return {
      infos: null,
      loading: "Initialisation"
    }
  },

  methods: {
    loadInfos(){
      this.loading = "Chargement des données";
      axios.get(this.entrypoint +'?a=infos').then(
          ok => {
            this.infos = ok.data.infos;
          }
      ).finally( foo => {
        this.loading = ""
      })
    }
  }
}
</script>


<style lang="scss">
h2{
  background: rgba(255,255,255,.5);

  small {
    display: block;
  }

  .fullname {
    border-top: solid thin black;
    border-bottom: solid thin black;
    padding: .5em .5em;

    code {
      border-radius: 4px;
      text-shadow: -1px 1px 1px rgba(0,0,0,.5);
      background: #5e80a1;
      color: white;
      padding: 0 .3em;
    }

    strong, em {
      padding: 0 .3em;
    }

    em {
      color: rgb(150,150,150,1)
    }
  }
}
</style>