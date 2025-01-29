<template>
  <modal title="Nouvelle avenant" :visible="edit" @modal-valid="handlerSave" @modal-cancel="handlerCancel">
    <form action="">
      <div class="form-group">
        <label for="name">Date (signature de l'avenant)</label>
        <datepicker v-model="edit.dateAvenant" />
      </div>
      <div class="form-group">
        <label for="name">Modifications</label>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Modifications <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#" @click.prevent="handlerAddChange('personAdd')">Ajout d'une personne</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('personDel')">Suppression d'une personne</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('organizationAdd')">Ajout d'une organisation</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('organizationDel')">Suppression d'une organisation</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('up_amount')">Modification du montant</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('dateEnd')">Modification de la date de fin</a></li>
          </ul>
        </div>
        <section class="changes">
          <article v-for="change in edit.changes" class="change">
            <div v-if="change.type === 'dateEnd'">
              <span class="text"> Date de fin : </span>
              <span><Datepicker v-model="change.value" /></span>
            </div>
            <div v-if="change.type === 'personDel'">
              <span class="text">Suppression d'un membre</span>
              <select name="" id="" @click.stop v-model="change.valueObj"
                      @change="handlerSelectPersonDel(change, $event)">
                <option :value="p" v-for="(p,id) in persons">
                  {{ p.firstName }} {{ p.lastName }}
                </option>
              </select>
              <select name="" id="" @click.stop v-model="change.value2" v-if="change.valueObj">
                <option :value="id" v-for="(role, id) in change.valueObj.roles">
                  {{ role }}
                </option>
              </select>
            </div>
            <div v-if="change.type === 'personAdd'">
              <span class="text"> Ajout d'une personne : </span>
              <span class="cartouche" v-if="change.valueObj">
                {{ change.valueObj.firstName }} {{ change.valueObj.lastName }}
                <i class="icon-cancel-alt" @click="change.valueObj = null"></i>
              </span>
              <person-auto-completer @personSelected="handlerUpdatePersonChange(change, $event)" v-else />
              <select name="rolesPerson" v-model="change.value2" class="form-control" @click.stop>
                <option :value="item.id" v-for="item in rolesPerson">{{ item.label }}</option>
              </select>
            </div>
            <div v-else>-> {{ change }}</div>
            <nav>
              <button class="btn btn-xs btn-danger" @click.prevent="handlerRemoveChange(change)">
                <i class="icon-trash"></i>
              </button>
            </nav>
          </article>
        </section>
      </div>
      <div class="form-group">
        <label for="name">Fichier</label>
        <input class="form-control" type="file" @change="handlerSelectFile"/>
      </div>
      <div class="form-group">
        <label for="name">Commentaire</label>
        <textarea v-model="edit.comment" class="form-control"></textarea>
      </div>
    </form>
  </modal>

  <section class="avenants">
    <article class="avenant card" v-for="a in avenants.avenants">
      <h3>
        <span>
          {{ $filters.dateFull(a.date) }}
        </span>
        <nav>
          <a :href="a.url_download" class="btn btn-xs btn-primary">
            <i class="icon-file-pdf"></i>
            Télécharger</a>
          <a href="#" class="btn btn-xs btn-danger" @click.prevent="handlerDelete(a)">
            <i class="icon-trash"></i>
            Supprimer</a>

        </nav>
      </h3>
      <p>{{ a.comment }}</p>
      <section class="modification">
        MODIFICATIONS ICI
      </section>
      {{ a }}
    </article>
  </section>

  <button class="btn btn-primary" @click="handlerNew">
    Nouvel avenant
  </button>
  <button class="btn btn-primary" @click="fetch">
    Fetch
  </button>

</template>
<script>

import Modal from "../components/Modal.vue";
import Datepicker from "../components/Datepicker.vue";
import AvenantDate from "./Avenants/AvenantDate.vue";
import PersonAutoCompleter from "../components/PersonAutoCompleter.vue";
import AxiosOscar from "../utils/AxiosOscar.js";

const readFileAsText = (file) => new Promise((resolve, reject) => {
  const reader = new FileReader();
  reader.onload = ({ target }) => {
    resolve(target.result);
  };
  reader.readAsText(file);
});

export default {
  name: 'ActivityAvenants',
  components: {
    PersonAutoCompleter,
    AvenantDate,
    Datepicker,
    Modal
  },

  props: ['roles-person', 'roles-organization', 'currentPersons', 'avenants'],

  data() {
    return {
      edit: null,
      selectedPerson: null,
    }
  },

  computed: {
    persons(){
      let out = {};
      if( this.currentPersons ){
        this.currentPersons.forEach(person => {
          if( !out.hasOwnProperty(person.id) ){
            out[person.id] = {
              'id': person.id,
              'firstName': person.firstName,
              'lastName': person.lastName,
              'roles' : {}
            };
          }
          if( !out[person.id].hasOwnProperty(person.roleId) ){
            out[person.id].roles[person.roleId] = person.roleLabel;
          }
        });
      }
      return out;
    }
  },

  methods: {
    handlerNew() {
      this.edit = {
        id: null,
        dateAvenant: null,
        comment: "",
        file: null,
        changes: []
      }
    },

    handlerCancel(){
      console.log("CANCEL");
      this.edit = null;
    },

    handlerSave(){
      console.log("SAVE", this.avenants.url_api);
      let formData = new FormData();
      formData.append("id", this.edit.id);
      formData.append("dateAvenant", this.edit.dateAvenant);
      formData.append("comment", this.edit.comment);
      formData.append("file", this.edit.file);
      formData.append("changes", JSON.stringify(this.edit.change));
      AxiosOscar.post(this.avenants.url_api, formData).then(response => {
        this.edit = null;
        this.fetch();
      })
    },

    handlerDelete(avenant){
      console.log("SAVE", avenant.url_api);
      AxiosOscar.delete(avenant.url_api, {pendingMsg: "Suppression de l'avenant"}).then(response => {
        this.fetch();
      })
    },

    handlerAddChange( type ){
      if( this.edit ){
        switch (type){
          case "dateEnd":
            this.edit.changes.push({
              type : type,
              label: "Date de fin",
              value: (new Date()).toISOString(),
              valueObj: null,
              value2: null,
            });
            break;
          default:
            this.edit.changes.push({
              type: type,
              label: "A définir",
              value: null,
              valueObj: null,
              value2: null
            });
        }
      }
    },

    handlerRemoveChange(change){
      let int = this.edit.changes.indexOf(change);
      if( int >= 0 ){
        this.edit.changes.splice(int, 1);
      }
    },

    handlerUpdatePersonChange(change, event){
      console.log(arguments);
      change.valueObj = {
        id: event.id,
        firstName: event.firstName,
        lastName: event.lastName,
      };
    },

    handlerSelectPersonDel(change, event){
      change.value = event.id;
      change.value2 = null;
    },

    async handlerSelectFile(event){
      if (event.target.files.length === 0) {
        this.edit.file = null;
        return;
      }
      this.edit.file = event.target.files[0];
    },

    fetch(){
      AxiosOscar.get(this.avenants.url_api, {pendingMsg: "Chargement des avenants"}).then(response => {
        this.$emit('update', response.data.datas.avenants);
      });
    }
  },

  mounted() {
    console.log("mounted")
  }
}
</script>

<style scoped>
.change {
  border: 1px solid #CCC;
  padding: .3em;
  margin: .5em 0;
  border-left: solid #CCC 4px;
  display: flex;
  >i{
    flex: 0;
  }
  >nav{
    flex: 0;
  }
  >div{
    flex: 1;
    display: flex;
  }
}
</style>