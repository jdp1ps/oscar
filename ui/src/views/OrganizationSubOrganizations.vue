<template>
  <div v-if="remote">
    Chargement
  </div>

  <oscar-dialog :options="dialogDelete"/>

  <div class="overlay" v-if="manageSubOragnization">
    <div class="overlay-content" style="overflow: visible">
      <h2>Nouvelle sous-structure</h2>
      <organization-auto-complete v-model="manageSubOragnization.selectedId" @change="handlerChange"/>
      <div class="buttons-bar">
        <button class="btn btn-danger" @click="manageSubOragnization = null">
          <i class="icon-cancel-circled"></i>
          Annuler
        </button>
        <button class="btn btn-success" @click="handlerSave">
          <i class="icon-floppy"></i>
          Enregistrer
        </button>
      </div>
    </div>
  </div>

  <div class="alert alert-danger" v-if="error">
    {{ error }}
    <hr>
    <a href="#" @click.prevent="error = ''">Fermer</a>
  </div>

  <section class="suborganizations">
    <article class="card suborganization" v-for="o in organizations">
      <h2 class="card-title">
        <code class="organization-code code">{{ o.code }}</code>
        <strong  class="organization-shortname">{{ o.shortname }}</strong>
        <span class="card-title-subsection">
          <i  class="organization-longname">{{ o.longname }}</i>
        </span>
      </h2>
      <section class="card-content" v-if="o.persons.length != 0">
        <h4>
          Personnel
        </h4>
        <div v-for="p in o.persons">
          <i class="icon-user"></i>
          <strong>{{ p.label }}</strong>
          <em v-if="p.roles.length">
            ({{ p.roles.join(', ') }})
          </em>
        </div>
      </section>
      <section class="card-content suborganizations" v-if="o.organizations.length != 0">
        <h4>
          Sous-structure
        </h4>
        <div v-for="o in o.organizations">
          <i class="icon-building"></i>
          <strong>{{ o.shortname }}</strong>
          &nbsp;
          <em>
            {{ o.longname }}
          </em>
        </div>
      </section>

      <nav class="card-footer buttons-bar">
        <a :href="o.show" class="btn btn-info btn-xs">
          <i class="icon-link-outline"></i>
          Voir la fiche
        </a>
        <a href="#" class="btn btn-danger btn-xs" v-if="manage"
           @click.prevent="handlerRemoveSubStructure(o)">
          <i class="icon-trash"></i>
          Retirer
        </a>
      </nav>
    </article>
  </section>

  <!--
  <button class="btn btn-primary" @click="fetch">
    Recharger
  </button>
  -->
  <button class="btn btn-primary" @click="handlerNew" v-if="manage">
    Ajouter une sous-structure
  </button>
</template>
<script>
import axios from "axios";
import OrganizationAutoComplete from "../components/OrganizationAutoComplete.vue";
import OscarDialog from "../components/OscarDialog.vue";

export default {
  props: {
    url: { require: true }
  },

  components: {
    OrganizationAutoComplete, OscarDialog
  },

  data(){
    return {
      selectedId: null,
      remote: "Initialisation",
      error: "",
      organizations: [],
      dialogDelete: null,
      manage: false,
      manageSubOragnization: null
    }
  },

  methods: {
    fetch(){
      this.remote = "Chargement des sous-structures";
      axios.get(this.url).then(ok => {

        this.remote = "";
        this.organizations = ok.data.organizations;
        this.manage = ok.data.manage;
      }, ko => {
        this.remote = "";
        this.error = "Impossible de charger les sous-structures : " + ko.response.data;
      })
    },

    handlerNew(){
      this.manageSubOragnization = {
        idOrganization: null
      }
    },

    handlerRemoveSubStructure( subStructure ){
      this.remote = "Suppression de la sous-structure";
      this.dialogDelete = {
        dispayed: true,
        title: "Suppression de la sous-structure",
        message : "Retirer '" +subStructure.label +"' des sous-structures ?",
        onSuccess: () => {
          console.log('sub structure', subStructure)
          this.remote = "Suppression de la sous-structure";
          axios.delete(this.url+'?idsubstructure=' +subStructure.id).then(ok => {
            this.remote = "";
            this.fetch();
          }, ko => {
            this.remote = "";
            this.error = "Impossible de supprimer la sous-structure : " + ko.response.data;
          }).finally(foo => {
            this.manageSubOragnization = null;
          })
        }
      };
    },

    handlerChange(selected){
      this.manageSubOragnization.idOrganization = selected.id;
    },

    handlerSave(){
      this.remote = "Chargement des sous-structures";
      let datas = new FormData();
      datas.append("idSubStructure", this.manageSubOragnization.idOrganization);
      axios.post(this.url, datas).then(ok => {
        this.remote = "";
        this.fetch();
      }, ko => {
        this.remote = "";
        this.error = ko.response.data ;
      }).finally(foo => {
        this.manageSubOragnization = null;
      })
    }
  },

  mounted() {
    this.fetch()
  }
}
</script>

<style lang="scss">
.suborganizations {
  .suborganization {
    margin: .3em;
    width: 100%;
  }
}

.card-content {
  > div {
    font-size: .8em;
  }
}

.card.suborganization .card-title {
  display: block;
  .code {
    flex-grow: 0;
    font-size: .8em;
    background: #b9ceda;
    padding: 0 .5em;
    text-shadow: 1px -1px 0 rgba(255,255,255,.3);
    //line-height: 1em;
    border-radius: 0px;
    margin-right: .3em;
  }
  .organization-shortname {
    display: inline-block;
    margin-right: .3em;
  }
  .card-title-subsection {
    display: inline-block;
    font-size: .7em;
    color: #314752;
  }
}
</style>