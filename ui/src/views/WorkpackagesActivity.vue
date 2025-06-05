<template>
  <section>
    <transition name="fade">
      <div class="vue-loader" v-if="errors.length">
        <div class="alert alert-danger" v-for="(error, i) in errors">
          {{ error }}
          <a href="" @click.prevent="errors.splice(i,1)"><i class="icon-cancel-outline"></i></a>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="overlay" v-if="confirm">
        <div class="overlay-content">
          <div class="overlay-title">
            {{ confirm }}
            <a href="#" class="overlay-closer" @click.prevent="confirm = null">x</a>
          </div>
          <nav class="buttons">
            <a href="#" class="btn btn-danger" @click.prevent="confirm = null">Annuler</a>
            <a href="#" class="btn btn-danger" @click.prevent="handlerConfirm()">Confirmer</a>
          </nav>
        </div>
      </div>
    </transition>

    <nav class="admin-bar">
      <a href="" class="btn btn-primary btn-xs" @click.prevent="handlerWorkPackageNew" v-if="editable">
        <i class="icon-book"></i>
        Nouveau lot</a>
      <a href="" class="btn btn-warning btn-xs" @click.prevent="fetch" v-if="debugEnabled">
        <i class="icon-bug"></i>
        fetch</a>
    </nav>

    <section class="workpackages">
      <workpackage v-for="wp in workpackages"
                   v-bind:key="wp.id"
                   :allow-tooltip="allowTooltip"
                   :workpackage="wp"
                   :persons="persons"
                   :editable="editable"
                   :is-validateur="isValidateur"
                   @addperson="addperson"
                   @workpackageupdate="handlerWorkPackageUpdate"
                   @workpackagepersonupdate="handlerUpdateWorkPackagePerson"
                   @workpackagepersondelete="handlerWorkPackagePersonDelete"
                   @workpackagedelete="handlerWorkPackageDelete"
                   @workpackagecancelnew="handlerWorkPackageCancelNew"
      ></workpackage>
    </section>
  </section>
</template>
<script>

import axios from "axios";
import Workpackage from "./Workpackage.vue";
import AxiosMessage from "../utils/AxiosMessage.js";

export default {
  components: {
    Workpackage
  },

  data() {
    return {
      loading: false,
      errors: [],
      isDeclarant: false,
      isValidateur: false,
      confirm: null,
      confirmData: null,
      confirmHandler: null,
      editedWorlpackage: null
    }
  },

  props: {
    url: {required: true},
    editable: {required: false, default: false},
    allowTooltip: { default: false},
    isValidateur: {required: true},
    outsidePerson: {required: true},
    persons: {required: true},
    workpackages: {required: true},
    debugEnabled: {default: false},
  },

  methods: {
    ////////////////////////////////////////////////////////////////////////
    // HANDLER
    handlerWorkPackageCancelNew(workpackage) {
      this.workpackages.splice(this.workpackages.indexOf(workpackage), 1);
    },

    handlerWorkPackageNew() {
      this.workpackages.push({
        id: -1,
        code: "Nouveau Lot",
        label: "",
        persons: [],
        description: ""
      })
    },

    handlerConfirm() {
      console.log("handlerConfirm");
      this.confirmHandler(this.confirmData);
      this.confirm = null;
    },

    /////////////////////////////////////////////////////////////////// SUPPRESSION des DECLARANTS

    handlerWorkPackagePersonDelete(workpackageperson) {
      this.confirm = "Supprimer le déclarant ?";
      this.confirmData = workpackageperson;
      this.confirmHandler = this.handlerWorkPackagePersonDeleteDo;
    },

    handlerWorkPackagePersonDeleteDo(workpackageperson) {
      axios.delete(this.url + "?workpackagepersonid=" + workpackageperson.id).then(
          (res) => {
            this.fetch();
          },
          (err) => {
            this.errors.push("Impossible de supprimer le déclarant : " + err.body);
          }
      );
    },

    /////////////////////////////////////////////////////////////////// SUPPRESSION des LOTS
    handlerWorkPackageDelete(workpackage) {
      this.confirm = "Supprimer le lot de travail ?";
      this.confirmData = workpackage;
      this.confirmHandler = this.handlerWorkPackageDeleteDo;
    },

    handlerWorkPackageDeleteDo(workpackage) {
      axios.delete(this.url + "?workpackageid=" + workpackage.id).then(
          (res) => {
            this.fetch();
          },
          (err) => {
            this.errors.push("Impossible de supprimer le lot : " + err.body);
          }
      );
    },


    /////////////////////////////////////////////////////////////////// CREATION/EDITION d'un LOT
    handlerWorkPackageUpdate(workPackageData) {
      var datas = new FormData();
      for (var key in workPackageData) {
        datas.append(key, workPackageData[key]);
      }
      if (workPackageData.id > 0) {
        console.log("MAJ du LOT");
        // Mise à jour
        datas.append('workpackageid', workPackageData.id);
        axios.post(this.url, datas).then(
            (res) => {
              this.fetch();
            },
            (err) => {
              this.errors.push("Impossible de mettre à jour le lot de travail : " + err.body);
            }
        );
      } else {
        console.log("NOUVEAU du LOT ", this.url);
        let dataSend = JSON.parse(JSON.stringify(workPackageData));
        dataSend.workpackageid = -1;
        axios.put(this.url, dataSend).then(
            (res) => {
              this.fetch();
            },
            (err) => {
              this.errors.push("Impossible de créer le lot de travail : " + err.body);
            }
        ).then(foo => this.fetch());
      }
    },

    handlerUpdateWorkPackagePerson(workpackageperson, duration) {
      var datas = new FormData();
      datas.append('workpackagepersonid', workpackageperson.id);
      datas.append('duration', duration);
      axios.post(this.url, datas).then(
          (res) => {
            workpackageperson.duration = duration;
          },
          (err) => {
            this.errors.push("Impossible de mettre à jour les heures prévues : " + err.body);
          }
      );
    },

    addperson(personid, workpackageid) {
      console.log(arguments);
      var data = {
        idworkpackage: workpackageid,
        idperson: personid
      };

      axios.put(this.url, data).then(
          (res) => {
            this.fetch();
          },
          (err) => {
            this.errors.push("Impossible d'ajouter le déclarant : " + err.body);
          }
      ).then(() => this.loading = false);
    },

    fetch() {
      this.loading = "Chargement des lots de travails";
      console.log(this.url);
      axios.get(this.url + "?v=2").then(
          (res) => {
            console.log("Chargement des lots de travail : ", res);
            try {
              let datas = res.data.datas.workpackages;
              this.$emit('update', datas);
            } catch (err) {
              this.errors.push("UI ERROR " + err);
            }
          },
          (err) => {

            this.errors.push(AxiosMessage.manageErrorResponse(err).message);
          }
      ).then(() => this.loading = false);

    },
    fetchPersons() {
      this.fetch();
    }
  }
}
</script>
<style scoped>
.workpackage {
  max-width: 31%;
  margin: 1%;
}
</style>