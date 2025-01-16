<template>
  <section class="organizationtype">
    <transition name="fade">
      <div class="error overlay" v-if="error">
        <div class="overlay-content">
          <i class="icon-warning-empty"></i>
          {{ error }}
          <br>
          <a href="#" @click="error = null" class="btn btn-default">
            <i class="icon-cancel-circled"></i>
            Fermer</a>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="pending overlay" v-if="pendingMsg">
        <div class="overlay-content">
          <i class="icon-spinner animate-spin"></i>
          {{ pendingMsg }}
        </div>
      </div>
    </transition>

    <transition name="fade">
      <modal title="Type d'organisation" :visible="formData" @modal-cancel="formData = null" @modal-valid="save">
        <form action="" class="form" @submit.prevent="save">
          <div class="form-group">
            <label for="form_label">Intitulé</label>
            <input class="form-control" v-model="formData.label" id="form_label" placeholder="Intitulé" />
          </div>
          <div class="form-group">
            <label for="form_label">Sous Type de : </label>
            <select name="root_id" id="" v-model="formData.root_id" class="form-control">
              <option value="">Aucun</option>
              <option :value="type.id" v-for="(type, i) in organizationtypes" >{{ type.label }}</option>
            </select>
          </div>
          <div class="form-group">
            <label for="form_description">Description</label>
            <textarea class="form-control" v-model="formData.description" id="form_description" placeholder=""></textarea>
          </div>
        </form>
      </modal>
    </transition>

    <organizationtypeitem :organizationtype="t"
                          :key="t.id"
                          :creatable="creatable"
                          v-for="t in organizationtypes"
                          @edit="edit"
                          @remove="remove"
    />

    <nav class="text-right">
      <a href="#" @click.prevent="handlerNew" v-show="manage" class="btn btn-primary">
        <i class="icon-building-filled"></i>
        Nouveau type d'organisation
      </a>
    </nav>
  </section>
</template>
<script>
import axios from "axios";
import AxiosMessage from "../utils/AxiosMessage.js";
import GlobalModel from "../models/GlobalModel.js";
import OrganizationTypeItem from '../components/items/OrganizationTypeItem.vue';
import Modal from "../components/Modal.vue";

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest ';

export default {
  props: {
    'url': { required: true },
    'manage': { default: false },
  },
  components: {
    Modal,
    organizationtypeitem: OrganizationTypeItem
  },
  data() {
    return {
      organizationtypes: [],
      error: null,
      pendingMsg: "",
      formData: null,
      loading: false,
      creatable: false
    }
  },

  methods: {
    /**
     * Suppression du type.
     */
    remove(type){
      axios.delete(this.url+"/"+type.id).then((response) => {
            this.getOrganizationtypes();
      }, fail => {
        GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(fail).message);
      })
    },

    edit(type){
      this.formData = type;
    },

    save(){
      axios.post(this.url,this.formData).then(
          success => {
            this.getOrganizationtypes();
          },
          fail => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(fail).message);
          }
      ).then(f=>{
        this.pendingMsg = "";
        this.formData = null;
      })
    },

    handlerNew(){
      this.formData = {
        id: "",
        label: "",
        description: "",
        root_id: ""
      };
    },

    /**
     * Chargement des jalons depuis l'API
     */
    getOrganizationtypes() {


      this.pendingMsg = "Chargement des types d'organisation : " + this.url;

      axios.get(this.url).then(
          success => {
            this.organizationtypes = success.data.organizationtypes;
            console.log("SUCCESS", success);
          },
          error => {
            this.error = "Impossible de charger les types d'oganisations : " + error.body
          }
      ).then(n => { this.pendingMsg = ""; });
    }
  },

  mounted() {
    this.getOrganizationtypes()
  }
}
</script>