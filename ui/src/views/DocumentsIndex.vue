<template>
  <section class="documents-content">
    <div class="documents-pager">
      {{ filterActivity }}
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-addon">
            <i class="icon-cubes"></i>
            Recherche textuelle
          </span>
          <input type="text" placeholder="Recherche dans l'activité" class="form-control" @input="handlerInput" v-model="filterActivity">
        </div>
      </div>
      <div class="col-md-3">
        <div class="input-group">
          <span class="input-group-addon">
            <i class="icon-tag"></i>
            Type
          </span>
          <select name="filterType" id="filterType" v-model="filterType" class="form-control" @change="fetchFiltered">
            <option value="0">Ignorer</option>
            <option v-for="(label, value) in filtersTypes" :value="value">{{ label }}</option>
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="input-group">
          <span class="input-group-addon">
            <i class="icon-edit"></i>
            Signature
          </span>
          <select name="filterSign" id="filterSign" v-model="filterSign" class="form-control" @change="fetchFiltered">
            <option value="">Ignorer</option>
            <option value="1">Avec</option>
            <option value="0">Sans</option>
          </select>
        </div>
      </div>
    </div>

    <nav aria-label="Page navigation">
      <ul class="pagination">
        <li v-if="currentPage > 10" class="page-item">
          <a href="#" aria-label="Previous" @click="fetchPage(currentPage - 10)">
            <span aria-hidden="true">&laquo;&laquo;</span>
          </a>
        </li>
        <li v-if="currentPage > 1" class="page-item">
          <a href="#" aria-label="Previous" @click="fetchPage(currentPage - 1)">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        <li class="page-item">
          <a href="#" aria-label="infos">
            {{ totalDocuments }} document(s) au total / page {{ currentPage }} sur {{ totalPages }}
          </a>
        </li>
        <li>
          <a href="#" aria-label="Next" v-if="currentPage < totalPages" @click="fetchPage(currentPage + 1)">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
        <li>
          <a href="#" aria-label="Next" v-if="currentPage < totalPages-10" @click="fetchPage(currentPage + 10)">
            <span aria-hidden="true">&raquo;&raquo;</span>
          </a>
        </li>
      </ul>
    </nav>
    <div class="card card-danger" v-if="error">
      <i class="icon-attention-1"></i>
      {{ error }}
    </div>
    <div class="card text-center" v-if="loading">
      <i class="icon-spinner animate-spin"></i>
      Chargement ici
    </div>
    <div class="tab-content" v-else>
      <documents-list
          :documents="packedDocuments"
          :tabs="tabsWithDocuments"
          :types="typesDocuments"
          :display-button-sign="false"
          :display-activity="true"
          @fetch="fetch"
      />
    </div>
  </section>
</template>
<script>
import DocumentsList from "./DocumentsList.vue";
import axios from "axios";
import timeout from "../../../public/js/vendor/vue-resource/src/http/interceptor/timeout.js";

var searchTimer = null;

export default {
  components: {DocumentsList},

  props: {
    manage: {default: false},
    url: {required: true}
  },

  data() {
    return {
      documents: [],
      tabsWithDocuments: [],
      typesDocuments: [],
      totalDocuments: 0,
      totalPages: 0,
      currentPage: 1,
      loading: true,
      error: null,

      // Filtres
      filterType: null,
      filterActivity: "",
      filterSign: ""
    }
  },

  computed: {
    displayedPages(){

    },
    filtersTypes(){
      let filters = {};
      this.typesDocuments.forEach(i => {
        filters[i.id] = i.label;
      })
      return filters
    },
    packedDocuments() {
      let documents = {};
      if (this.documents) {

        for (const [j, doc] of Object.entries(this.documents)) {
          let docKey = doc.fileName;
          if (!documents.hasOwnProperty(docKey)) {
            doc.versions = [];
            documents[docKey] = doc;
          } else {
            documents[docKey].versions.push(doc);
          }
        }
      }
      return documents;
    }
  },

  methods: {
    onResponse(response){
      this.tabsWithDocuments = response.data.tabsWithDocuments;
      this.typesDocuments = response.data.typesDocuments;
      this.documents = response.data.documents;
      this.totalPages = response.data.total_pages;
      this.totalDocuments = response.data.total;
      this.currentPage = response.data.page;
      this.loading = false;
    },
    fetch(updateFilter=false, switchPage=null) {
      let page = this.currentPage;
      this.loading = true;
      if( updateFilter ){
        page = 1;
      } else if (switchPage !== null){
        page = switchPage;
      }
      axios.get(this.url+'&page='+page
          +'&type=' +this.filterType
          + '&sign=' + this.filterSign
          + '&s=' + this.filterActivity)
          .then(response => {
        this.onResponse(response);
      }, fail => {
        this.error = fail;
      })
    },
    fetchPage(page) {
      this.fetch(false, page);
    },
    fetchFiltered() {
      this.fetch(true, false);
    },

    handlerInput(e){
      if( searchTimer ){
        clearTimeout(searchTimer);
      }
      searchTimer = setTimeout(()=>{
        this.fetchFiltered();
        clearTimeout(searchTimer);
      }, 1000)
    }
  },

  mounted() {
    this.fetch();
  }
}
</script>