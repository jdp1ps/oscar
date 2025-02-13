<template>
  <div style="position: relative;">

    <loader :visible="loading" :text="loading"></loader>

    <modal title="Erreur" :visible="error">
      <div class="alert alert-danger">
        {{ error }}
      </div>
    </modal>

    <div class="overlay" v-if="entityDelete">
      <div class="overlay-content">
        <i class="icon-cancel-outline overlay-closer" @click="entityDelete = null"></i>

        <h2>Supprimer le rôle <strong>{{ entityDelete.role }}</strong> de <strong>{{ entityDelete.enrolledLabel
          }}</strong> ?</h2>

        <nav class="admin-bar">
          <button class="btn btn-default button-back" @click="entityDelete = null">
            <i class="icon-angle-left"></i>
            Annuler
          </button>
          <button class="btn btn-primary" @click="performDelete">
            <i class="icon-trash"></i>
            Confirmer la suppression
          </button>
        </nav>
      </div>
    </div>

    <div class="overlay" v-if="toPaste">
      <div class="overlay-content">
        <i class="icon-cancel-outline overlay-closer" @click="toPaste = null"></i>

        <h2>Ajouter des {{ title }} ?</h2>

        <table class="table-bordered table-borderless table-responsive-md">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ title }}</th>
            <td>Rôle</td>
          </tr>
          </thead>
          <tbody>
          <tr v-for="item in toPaste">
            <td><input type="checkbox" v-model="item.selected"/></td>
            <td>{{ item.enrolledLabel }}</td>
            <td>
              <select name="role" class=" form-control" v-model="item.roleId">
                <option :value="role.id" v-for="role in rolesList">
                  {{ role.label }}
                </option>
              </select>
            </td>
          </tr>
          </tbody>
          <tr>

          </tr>
        </table>

        <nav class="admin-bar">
          <button class="btn btn-default button-back" @click="error = ''">
            <i class="icon-angle-left"></i>
            Annuler
          </button>
          <button class="btn btn-primary" @click="performPast">
            <i class="icon-trash"></i>
            Confirmer
          </button>
        </nav>
      </div>
    </div>

    <div class="overlay" v-if="entityEdited">
      <div class="overlay-content" style="overflow: visible">
        <i class="icon-cancel-outline overlay-closer" @click="entityEdited = null"></i>

        <form :action="entityEdited.urlEdit" method="post" @submit.prevent.stop="performEdit">
          <h2>
            <span v-if="entityEdited.enrolledLabel">
                Modifier <strong>{{ entityEdited.enrolledLabel }}</strong>
                en tant que <em>{{ entityEdited.role }}</em>
            </span>
          </h2>

          <input type="hidden" name="enroled" class="form-control select2" v-model="entityEdited.enrolled"/>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class=" control-label" for="role">Rôle</label>
                <select name="role" class=" form-control" v-model="entityEdited.roleId">
                  <option :value="role.id" v-for="role in rolesList">
                    {{ role.label }}
                  </option>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label control-label" for="dateStart">Date de début</label>
                <datepicker :moment="moment"
                            :value="entityEdited.start"
                            @input="value => {entityEdited.start = value}"/>
              </div>
              <div class="form-group">
                <label class="form-label control-label" for="dateEnd">Date de fin</label>
                <datepicker :moment="moment"
                            :value="entityEdited.end"
                            @input="value => {entityEdited.end = value}"/>
              </div>
            </div>
          </div>

          <nav class="admin-bar">
            <button class="btn btn-default button-back" @click="entityEdited = null">
              <i class="icon-angle-left"></i>
              Annuler
            </button>
            <button class="btn btn-primary" type="submit">
              <i class="icon-floppy"></i>
              Enregistrer
            </button>
          </nav>
        </form>
      </div>
    </div>

    <div class="overlay" v-if="entityNew">
      <div class="overlay-content" style="overflow: visible">
        <i class="icon-cancel-outline overlay-closer" @click="entityNew = null"></i>

        <h2>Rôle de <strong>{{ entityNew.enroledLabel }}</strong> : </h2>
        <input type="hidden" name="enroled" class="form-control select2" v-model="entityNew.enrolled"/>

        <div class="row">
          <div class="col-md-6">
            <span v-if="entityNew.enroledLabel" class="cartouche">
                {{ entityNew.enroledLabel }}
                <i class="icon-cancel-alt icon-clickable" @click="handlerCancel"></i>
                <span class="addon" v-if="entityNew.role">
                    {{ getRoleById(entityNew.role).label }}
                </span>
            </span>
            <div class="form-group" v-else>
              <label class=" control-label" for="enroled">{{ title }}</label>
              <personselector @change="handlerEnrolledSelectedPerson($event)" v-if="title == 'Personne'"
                              v-model="selected"/>
              <organizationselector @change="handlerEnrolledSelected($event)" v-else v-model="selected"/>
            </div>

            <div class="form-group">
              <label class=" control-label" for="role">Rôle</label>
              <select name="role" class=" form-control" v-model="entityNew.role">
                <option :value="role.id" v-for="role in rolesList">
                  {{ role.label }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label control-label" for="dateStart">Date de début</label>
              <datepicker :moment="moment"
                          :value="entityNew.start"
                          @input="value => {entityNew.start = value}"/>
            </div>
            <div class="form-group">
              <label class="form-label control-label" for="dateEnd">Date de fin</label>
              <datepicker :moment="moment"
                          :value="entityNew.end"
                          @input="value => {entityNew.end = value}"/>
            </div>
          </div>
        </div>

        <nav class="admin-bar">
          <button class="btn btn-default button-back" type="button" @click="entityNew = null">
            <i class="icon-angle-left"></i>
            Annuler
          </button>
          <button class="btn btn-primary" type="button" @click="performNew">
            <i class="icon-floppy"></i>
            Enregistrer
          </button>
        </nav>
      </div>
    </div>

    <nav class="admin-bar text-right" v-if="manage">
      <a class="btn btn-xs btn-default" @click="handlerNew()">
        <i class="icon-doc-add"></i>
        Nouveau
      </a>
      <a class="btn btn-xs btn-default" @click="handlerEditEnable()">
        <i class="icon-edit"></i>
        <span v-if="editMode">Mode visualisation</span>
        <span v-else>Mode Edition</span>
      </a>
      <a class="btn btn-xs btn-default" @click="handlerCopy()">
        <i class="icon-doc"></i>
        Copier
      </a>
      <a class="btn btn-xs btn-default" @click="handlerPaste()">
        <i class="icon-paste"></i>
        Coller
      </a>
      <a class="btn btn-xs btn-warning" v-if="debugEnabled" @click="fetch()">
        <i class="icon-bug"></i>
        Fetch
      </a>
    </nav>

    <section v-if="editMode">
      <div class="alert alert-info">
        Détails des affectations. Un élément peut apparaître plusieurs fois selon le contexte et le rôle.
        Les affectation aux activités sont indiquées par un cube simple <i class="icon-cube"></i>, les affectations aux
        projets par plusieurs cubes <i class="icon-cubes"></i>
      </div>
      <article class="row card" v-for="e in sortedFull">
        <div class="col-md-6">
          <i class="icon-cube" v-if="e.context == 'activity'"></i>
          <i class="icon-cubes" v-else></i>
            <PersonDisplay :person="e" v-if="e.firstname" />
            <strong v-else>{{ e.enrolledLabel }}</strong>
          <small>
            (<span v-if="e.context == 'activity'">
                <i class="icon-cube"></i>
                {{ e.contextKey }}</span>
            <span v-else>
                <i class="icon-cubes"></i>
                {{ e.contextKey }}</span>)
          </small>
        </div>
        <div class="col-md-3">
          <em :class="{'bold': e.rolePrincipal }">{{ e.roleLabel }}</em>
        </div>
        <div class="col-md-3 text-right">
          <a @click="handlerEdit(e)" style="white-space: nowrap">
            <i class="icon-pencil-1 icon-clickable" v-if="manage"></i>&nbsp;Modifier
          </a>

          <a @click="handlerDelete(e)" style="white-space: nowrap">
            <i class="icon-trash icon-clickable" v-if="manage"></i>&nbsp;Supprimer
          </a>
        </div>
      </article>
    </section>
    <section v-else>
      <span class="cartouche" v-for="e in stacked" :class="{
        'person' : title == 'Personne',
        'organization' : title == 'Organisation',
        'primary': e.hasPrimary, 'default': !e.hasPrimary}">
        <a :href="e.urlShow" v-if="entityLinkShow">
          <PersonDisplay :person="e" v-if="title == 'Personne'" :allow-tooltip="entityLinkShow"/>
          <span v-else>
            {{ e.enrolledLabel }}
          </span>
        </a>
        <span v-else>
          <PersonDisplay :person="e" v-if="title == 'Personne'" :allow-tooltip="entityLinkShow"/>
          <span v-else>
            {{ e.enrolledLabel }}
          </span>
        </span>
        <span class="addon principal">
          <span v-for="r in e.roles" class="addon-module" :class="{'primary': r.rolePrincipal}">
            {{ r.role }}
          </span>
        </span>
      </span>
    </section>
  </div>
</template>
<script>

import axios from 'axios';
import Datepicker from "../components/Datepicker.vue";
import Loader from "../components/Loader.vue";
import OrganizationAutoCompleter from "../components/OrganizationAutoComplete.vue";
import PersonAutoCompleter from "../components/PersonAutoCompleter.vue";
import Modal from "../components/Modal.vue";
import {standalone} from "poi/lib/webpack/css-loaders.js";
import PersonDisplay from "../components/PersonDisplay.vue";


export default {
  components: {
    PersonDisplay,
    Modal,
    datepicker: Datepicker,
    Loader,
    organizationselector: OrganizationAutoCompleter,
    personselector: PersonAutoCompleter,
  },

  props: {
    urlNew: {required: true, type: String},
    url: {required: true, type: String},
    title: {required: true},
    items: {required: true},
    roles: {required: true},
    entityLinkShow: {default: false},
    manage: {required: true, default: true, type: Boolean},
    standalone: {required: true, default: true, type: Boolean},
    debugEnabled: {default: false, type: Boolean},
  },

  data() {
    return {
      selectedPerson: null,
      selected: null,
      entityEdited: null,
      entityDelete: null,
      entityNew: null,
      error: null,
      loading: false,
      editMode: false,

      standalone_items: [],
      standalone_roles: [],
      standalone_manage: false,
      standalone_urlNew: null,

      // Utils
      toPaste: null
    };
  },

  computed: {
    rolesList(){
      return this.standalone ? this.standalone_roles : this.roles;
    },

    urlNewUse(){
      return this.standalone ? this.standalone_urlNew : this.urlNew;
    },

    entities() {
      if( this.standalone ){
        return this.standalone_items;
      } else {
        if( this.items )
          return this.items;
        else return [];
      }
    },
    sortedFull() {
      return this.entities.sort((a, b) => a.enrolled - b.enrolled)
    },

    stacked() {
      let stacks = {};
      if (this.entities) {
        this.entities.forEach(i => {
          let id = i.enrolled;
          if (!stacks.hasOwnProperty(id)) {
            stacks[id] = {
              id: id,
              firstname: i.firstname,
              lastname: i.lastname,
              enrolled: i.enrolled,
              urlShow: i.urlShow,
              enrolledLabel: i.enrolledLabel,
              hasPrimary: false
            }
            stacks[id]['roles'] = {};
          }
          stacks[id]['roles'][i.roleId] = {
            role: i.roleLabel,
            roleId: i.roleId,
            rolePrincipal: i.rolePrincipal,
            context: i.context,
          };
          if (i.rolePrincipal) {
            stacks[id].hasPrimary = true;
          }
        })
      }
      return stacks;
    }
  },

  methods: {
    handlerEdit(item) {
      this.entityEdited = item;
    },

    handlerDelete(item) {
      this.entityDelete = item;
    },

    handlerNew() {
      this.entityNew = {
        end: '',
        start: '',
        role: null,
        enroled: null,
        enroledLabel: ""
      };
    },

    handlerEnrolledSelected(data) {
      this.entityNew.enroled = data.id;
      this.entityNew.enroledLabel = data.label;
    },

    handlerEnrolledSelectedPerson(data) {
      this.selected = data.id;
      this.entityNew.enroled = data.id;
      this.entityNew.enroledLabel = data.label;
    },

    handlerCancel() {
      this.entityNew.enroled = null;
      this.entityNew.enroledLabel = "";
      this.entityNew.role = null;
    },

    open(url) {
      document.location = url;
    },

    handlerEditEnable() {
      this.editMode = !this.editMode;
    },

    performDelete() {
      this.loading = "Suppression...";
      var url = this.entityDelete.urlDelete;
      this.entityDelete = null;

      axios.post(url, {}).then(ok => {
      }, ko => {
        this.error = ko.body;
      }).then(foo => {
        this.loading = false;
        this.fetch();
      })
    },

    getRoleById(id){
      return this.rolesList.find(i => i.id === id);
    },

    performEdit() {
      this.loading = "Enregistrement des modifications";
      let data = new FormData();
      data.append('dateStart', this.entityEdited.start);
      data.append('dateEnd', this.entityEdited.end);
      data.append('role', this.entityEdited.roleId);
      data.append('enrolled', this.entityEdited.enrolled);
      var url = this.entityEdited.urlEdit;
      this.entityEdited = null;
      axios.post(url, data).then(ok => {

      }, ko => {
        this.error = "Erreur : Impossible de modifier le rôle : " + ko.body;
      }).then(foo => {
        this.loading = false;
        this.fetch();
      });
      return false;
    },

    performNew() {
      let data = new FormData();
      this.loading = "Création...";
      var enroled = this.entityNew.enroled;
      data.append('dateStart', this.entityNew.start);
      data.append('dateEnd', this.entityNew.end);
      data.append('role', this.entityNew.role);
      data.append('enroled', enroled);
      this.entityNew = null;

      axios.post(this.urlNewUse + '/' + enroled, data).then(ok => {

      }, ko => {
        this.error = ko.status == 403 ? "Vous n'êtes pas authorisé à faire ça" : "Erreur : " + ko.body;
      }).then(foo => {
        this.loading = false;
        this.fetch();
      })
    },

    performPast() {
      let data = new FormData();
      this.loading = "Création...";
      let json = JSON.stringify(this.toPaste.filter(item => item.selected));
      data.append('action', 'multi');
      data.append('json', json);

      axios.post(this.urlNewUse, data).then(ok => {

      }, ko => {
        this.error = ko.status == 403 ? "Vous n'êtes pas authorisé à faire ça" : "Erreur : " + ko.body;
      }).then(foo => {
        this.loading = false;
        this.toPaste = null;
        this.fetch();
      })
    },

    fetch() {
      this.loading = "Chargement des données";
      axios.get(this.url).then(ok => {
            if (this.standalone) {
              if (ok.data.roles) {
                this.standalone_roles = ok.data.roles;
              }
              if (ok.data.manage) {
                this.standalone_manage = ok.data.manage;
              }
              if (ok.data.urlNew) {
                this.standalone_urlNew = ok.data.urlNew;
              }
              if (ok.data.persons) {
                this.standalone_items = ok.data.persons;
              } else if (ok.data.organizations) {
                this.standalone_items = ok.data.organizations;
              } else {
                this.standalone_items = ok.data;
              }
            } else {
              let items = null;
              if (ok.data.persons) {
                items = ok.data.persons;
              } else if (ok.data.organizations) {
                items = ok.data.organizations;
              } else {
                items = ok.data;
              }
              console.log("emit", items);
              this.$emit('update', {
                datas: {
                  items: items,
                  urlNew: ok.data.urlNew,
                  manage: ok.data.manage,
                }
              });
            }
          },
          ko => {
            this.error = "Erreur : " + ko.body;
          }).finally(e => this.loading = null);
    },

    handlerCopy() {
      let storage_key = "copy_" + this.title;
      let datas = [];
      this.entities.forEach(item => {
        datas.push({
          'enrolled': item.enrolled,
          'enrolledLabel': item.enrolledLabel,
          'roleId': item.roleId,
          'roleLabel': item.roleLabel,
        });
      });
      localStorage.setItem(storage_key, JSON.stringify(datas));
    },

    handlerPaste() {
      let storage_key = "copy_" + this.title;
      let stored = localStorage.getItem(storage_key);
      if (stored) {
        let infos = JSON.parse(stored);
        this.toPaste = [];
        infos.forEach(item => {
          item.selected = true;
          this.toPaste.push(item);
        })
      }
    },
  },

  mounted() {
    if( this.standalone ){
      this.fetch();
    }
  }
}

</script>