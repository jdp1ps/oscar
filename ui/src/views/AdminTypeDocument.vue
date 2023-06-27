<template>
  <section>
    <div class="vue-loader" v-if="loading">
      <span> {{ loadingMsg }}</span>
    </div>
    <transition name="popup">
      <div class="form-wrapper" v-if="form">
        <form action="" @submit.prevent="save" class="container oscar-form">
          <header>
            <h1>
              <span v-if='form-id'>Modification de <strong>{{ form.label }}</strong></span>
              <span v-else>Nouveau type de documents</span>
            </h1>
          </header>
          <div class="form-group">
            <label>Nom du type de document</label>
            <input id='typedoc_label' type="text" class="form-control" v-model="form.label" name="label"/>
          </div>
          <footer class="buttons-bar">
            <div class="btn-group">
              <button type="submit" class="btn btn-primary">
                <i class="icon-floppy"></i>
                Enregistrer
              </button>
              <button type="submit" class="btn btn-default" @click="form=null">
                <i class="icon-floppy"></i>
                Annuler
              </button>
            </div>
          </footer>
        </form>
      </div>
    </transition>
    <!-- Vue principale pour les types de documents -->
    <article v-for="typedoc in types" class="card xs">
      <h1 class="card-title">
                    <span>
                        {{ typedoc.label }}
                    </span>
      </h1>
      <nav class="card-footer" v-if="manage">
        <button class="btn btn-xs btn-primary" @click="form=JSON.parse(JSON.stringify(typedoc))">
          <i class="icon-pencil"></i>
          Éditer
        </button>
        <button class="btn btn-xs btn-default" @click="remove(typedoc)">
          <i class="icon-trash"></i>
          Supprimer
        </button>
      </nav>
    </article>
    <button @click="formNew" class="btn btn-primary" v-if="manage">
      <i class="icon-circled-plus"></i>
      Ajouter
    </button>
  </section>
</template>
<script>

import axios from "axios";

export default {
  props: {
    url: { required: true },
    manage: false,
    bootbox: { required: true }
  },
  data(){
    return {
      types: [],
      loadingMsg: null,
      form: null
    }
  },
  computed: {
    loading() {
      return this.loadingMsg != null;
    }
  },
  methods: {
    formNew() {
      this.form = {
        label: "",
        description: ""
      }
    },
    save(){

      var datas = new FormData();
      datas.append('label', this.form.label);
      datas.append('description', this.form.description);

      if( this.form.id ){
        this.loadingMsg = "Mise à jour du type de document...";
        datas.append('typedocumentid', this.form.id);
        this.$http.put(this.url +"", datas).then(
            (res)=>{
              this.fetch();
            },
            (err)=>{
              flashMessage('error', err.body);
            }
        ).then(()=> { this.loadingMsg = null; this.form = null; });
      }
      else {
        this.loadingMsg = "Ajout d'un nouveau type de document...";
        this.$http.post(this.url,datas).then(
            (res)=>{
              this.fetch()
            },
            (err)=>{
              flashMessage('error', err.body);
            }
        ).then(()=> { this.loadingMsg = null; this.form = null; });
      }
    },
    remove(typedoc) {
      this.bootbox.confirm("Êtes-vous sûr de supprimer : " + typedoc.label + "?", (res)=> {
        if ( !res ) return;
        this.loadingMsg = "Suppression du type de document...";
        this.$http.delete(this.url +'?typedocumentid='+typedoc.id).then(
            (res)=>{
              this.fetch();
            },
            (err)=>{
              flashMessage('error', err.body);
            }
        ).then(()=> {this.loadingMsg = null, this.form = null;});
      })
    },

    fetch() {
      this.loadingMsg = "Chargement des types de documents";
      axios.get(this.url)
          .then( response => {
            this.types = response.data;
          })
          .catch(err => flashMessage('error', "Impossible de charger les types de documents : " + err.body))
          .finally(()=> { this.loadingMsg = null } );


      //this.url);
      // this.$http.get(this.url).then(
      //     (res)=>{
      //       this.types = res.body;
      //     }, (err)=>{
      //       flashMessage('error',err.body);
      //     }).then(()=> this.loadingMsg = null);

    }
  },
  created() {
    this.fetch();
  }
}
</script>