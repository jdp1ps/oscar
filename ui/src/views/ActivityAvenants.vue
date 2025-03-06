<template>
  <modal title="Détails de l'avenant" :visible="edit" @modal-valid="handlerSave" @modal-cancel="handlerCancel">
    <form action="">

      <div class="alert alert-info">
        Vous pourrez revenir modifier cet avenant plus tard tant qu'il est en mode <strong>brouillon</strong>.
      </div>

      <div class="form-group">
        <label for="dateAvenant">Date : </label>
        <div class="help">
          Date de <strong>signature</strong> de l'avenant
        </div>
        <datepicker v-model="edit.dateAvenant" id="dateAvenant" />
      </div>

      <div class="form-group">
        <label for="name" class="">Modifications : </label><br>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                  aria-expanded="false">
            Ajouter une modification <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#" @click.prevent="handlerAddChange('personAdd')" :class="modificationsEnabled['personAdd'] ? '' : 'disabled'">Ajout d'une personne</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('personDel')" :class="modificationsEnabled['personDel'] ? '' : 'disabled'">Suppression d'une personne</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('organizationAdd')" :class="modificationsEnabled['organizationAdd'] ? '' : 'disabled'">Ajout d'une organisation</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('organizationDel')" :class="modificationsEnabled['organizationDel'] ? '' : 'disabled'">Suppression d'une organisation</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('changeAmount')" :class="modificationsEnabled['changeAmount'] ? '' : 'disabled'">Modification du montant</a></li>
            <li><a href="#" @click.prevent="handlerAddChange('dateEnd')" :class="modificationsEnabled['dateEnd'] ? '' : 'disabled'">Modification de la date de fin</a></li>
          </ul>
        </div>
        <section class="modifications unsaved">
          <!-- UNSAVED -->
          <article v-for="change in edit.modifications.filter(i => i.mode === 'new')" class="change">
            <!-- EDITION dateEnd : Date de fin -->
            <div v-if="change.type === 'dateEnd'">
              <span class="text"> Date de fin : </span>
              <span><Datepicker v-model="change.value1"/></span>
            </div>
            <!-- EDITION changeAmount : Montant -->
            <div v-else-if="change.type === 'changeAmount'">
              <span class="text">Nouveau montant : </span>
              <Amount v-model="change.value1" />
            </div>
            <div v-else-if="change.type === 'personDel'">
              <span class="text">Suppression d'un membre</span>
              <select name="" id="" @click.stop v-model="change.valueObj"
                      @change="handlerSelectToDel(change, $event)">
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
            <div v-else-if="change.type === 'personAdd'">
              <span class="text"> Ajout d'une personne : </span>
              <span class="cartouche" v-if="change.valueObj">
                {{ change.valueObj.firstName }} {{ change.valueObj.lastName }}
                <i class="icon-cancel-alt" @click="change.valueObj = null"></i>
              </span>
              <person-auto-completer @personSelected="handlerUpdatePersonChange(change, $event)" v-else/>
              <select name="rolesPerson" v-model="change.value2" class="form-control" @click.stop>
                <option :value="item.id" v-for="item in rolesPerson">{{ item.label }}</option>
              </select>
            </div>
            <div v-else-if="change.type === 'organizationDel'">
              <span class="text"> Suppression d'une organisation : </span>
              <select name="" id="" @click.stop v-model="change.valueObj"  class="form-control"
                      @change="handlerSelectToDel(change, $event)">
                <option :value="p" v-for="(p,id) in organizations">
                  {{ p.label }}
                </option>
              </select>
              <select name="" id="" @click.stop v-model="change.value2" v-if="change.valueObj" class="form-control">
                <option :value="id" v-for="(role, id) in change.valueObj.roles">
                  {{ role }}
                </option>
              </select>
            </div>
            <div v-else-if="change.type === 'organizationAdd'">
              <span class="text"> Ajout d'une organization : </span>
              <organization-auto-complete @change="handlerUpdateOrganizationChange(change, $event)" />
              <select name="rolesOrganization" v-model="change.value2" class="form-control" @click.stop>
                <option :value="item.id" v-for="item in rolesOrganization">{{ item.label }}</option>
              </select>
            </div>
            <div v-else>
              {{ change }}
            </div>
            <nav>
              <button class="btn btn-xs btn-danger" @click.prevent="handlerRemoveChange(change)">
                <i class="icon-trash"></i>
              </button>
            </nav>
          </article>
        </section>
        <hr>
        <section class="modifications saved">
          <!-- SAVED -->
          <article v-for="change in edit.modifications.filter(i => i.mode !== 'new')" class="change">
            <div>
              <i class="icon-calendar" v-if="change.type === 'dateEnd'"></i>
              <i class="icon-user" v-if="change.type === 'personAdd'"></i>
              <i class="icon-bank" v-if="change.type === 'changeAmount'"></i>
              <i class="icon-user text-danger" v-if="change.type === 'personDel'"></i>
              <i class="icon-building-filled" v-if="change.type === 'organizationAdd'"></i>
              <i class="icon-building-filled text-danger" v-if="change.type === 'organizationDel'"></i>
              <strong>
                {{ change.info }}
              </strong>
              <small>
                <code> ({{ change.type }})</code>
              </small>
            </div>
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
        <section v-if="edit.previousFile">
          Fichier précédent
        </section>
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
      <h4>
        <i class="icon-ok-circled text-success" v-if="a.status == 200"></i>
        <i class="icon-pencil" v-if="a.status == 100"></i>
        <strong>
          {{ $filters.dateFull(a.date) }}
        </strong>
        <small>
          - {{ a.status_text }}
        </small>
      </h4>
      <section class="modification small">
        <article class="change" v-for="change in a.modifications">
          <i class="icon-calendar" v-if="change.type === 'dateEnd'"></i>
          <i class="icon-user" v-if="change.type === 'personAdd'"></i>
          <i class="icon-user text-danger" v-if="change.type === 'personDel'"></i>
          <i class="icon-bank" v-if="change.type === 'changeAmount'"></i>
          <i class="icon-building-filled" v-if="change.type === 'organizationAdd'"></i>
          <i class="icon-building-filled text-danger" v-if="change.type === 'organizationDel'"></i>
          <span>
            {{ change.info }}
          </span>
        </article>
      </section>
      <p>{{ a.comment }}</p>
      <nav>

        <a :href="a.url_download" class="btn btn-xs btn-primary">
          <i class="icon-file-pdf"></i>
          Télécharger</a>

        <ButtonConfirm @confirm="handlerDelete(a)"
                       :class="'btn btn-xs btn-danger'"
                       v-if="manage" :checkbox="true">
          <i class="icon-trash"></i>
          Supprimer
          <template #message>
            Supprimer <strong>définitivement</strong> cet avenant ?
          </template>
        </ButtonConfirm>

        <a href="#" class="btn btn-xs btn-default" @click.prevent="handlerEdit(a)" v-if="manage && a.editable">
          <i class="icon-pencil"></i>
          Editer
        </a>

        <ButtonConfirm @confirm="handlerApplyAvenant(a)"
                       :class="'btn btn-xs btn-success'"
                       v-if="a.status === 100 && manage">
          <i class="icon-trash"></i>
          Appliquer
          <template #message>
            Confirmer l'application de l'avenant pour cette activité ?
            <strong>L'activité sera verrouillée</strong>
          </template>
        </ButtonConfirm>

      </nav>
    </article>
  </section>

  <button class="btn btn-primary" @click="handlerNew" v-if="manage">
    Nouvel avenant
  </button>
</template>
<script>

import Modal from "../components/Modal.vue";
import Datepicker from "../components/Datepicker.vue";
import AvenantDate from "./Avenants/AvenantDate.vue";
import PersonAutoCompleter from "../components/PersonAutoCompleter.vue";
import AxiosOscar from "../utils/AxiosOscar.js";
import OrganizationAutoComplete from "../components/OrganizationAutoComplete.vue";
import Amount from "../components/Amount.vue";
import ButtonConfirm from "../utils/ButtonConfirm.vue";
//import Test from "../../../vendor/unicaen/signature/public/src/views/SignatureFlows.vue";

export default {
  name: 'ActivityAvenants',
  components: {
    ButtonConfirm,
    Amount,
    AvenantDate,
    Datepicker,
    Modal,
    OrganizationAutoComplete,
    PersonAutoCompleter
  },

  props: ['roles-person', 'roles-organization', 'currentPersons', 'currentOrganizations', 'avenants', 'manage'],

  data() {
    return {
      edit: null,
      selectedPerson: null
    }
  },

  computed: {

    modificationsEnabled(){
      return {
        'personAdd': true,
        'personDel': true,
        'organizationAdd': true,
        'organizationDel': true,
        'changeAmount': !this.edit.modifications.find( m => m.type == 'changeAmount'),
        'dateEnd': !this.edit.modifications.find( m => m.type == 'dateEnd'),
      }
    },

    persons() {
      let out = {};
      if (this.currentPersons) {
        this.currentPersons.forEach(person => {
          if (!out.hasOwnProperty(person.enrolled)) {
            out[person.enrolled] = {
              'id': person.enrolled,
              'firstName': person.firstName,
              'lastName': person.lastName,
              'roles': {}
            };
          }
          if (!out[person.enrolled].hasOwnProperty(person.roleId)) {
            out[person.enrolled].roles[person.roleId] = person.roleLabel;
          }
        });
      }
      return out;
    },
    organizations() {
      let out = {};
      if (this.currentOrganizations) {
        this.currentOrganizations.forEach(organization => {
          if (!out.hasOwnProperty(organization.enrolled)) {
            console.log(organization);
            out[organization.enrolled] = {
              'id': organization.enrolled,
              'label': organization.enrolledLabel,
              'roles': {}
            };
          }
          if (!out[organization.enrolled].hasOwnProperty(organization.roleId)) {
            out[organization.enrolled].roles[organization.roleId] = organization.roleLabel;
          }
        });
      }
      return out;
    }
  },

  methods: {
    handlerConfirm(message, handler, args){
      console.log("confirm", message);
      handler.call(this, args);
    },

    handlerOk(arg){
      console.log(JSON.stringify(arg));
      console.log(this.avenants.url_api);
    },

    handlerNew() {
      this.edit = {
        id: null,
        dateAvenant: null,
        comment: "",
        previousFile: null,
        file: null,
        modifications: []
      }
    },

    handlerEdit(avenant) {
      this.edit = {
        id: avenant.id,
        dateAvenant: avenant.date,
        comment: avenant.comment,
        previousFile: avenant.filename,
        file: null,
        modifications: JSON.parse(JSON.stringify(avenant.modifications))
      };
    },

    handlerCancel() {
      console.log("CANCEL");
      this.edit = null;
    },

    handlerSave() {
      let formData = new FormData();
      formData.append("id", this.edit.id ?? "");
      formData.append("dateAvenant", this.edit.dateAvenant ?? "");
      formData.append("comment", this.edit.comment ?? "");
      formData.append("file", this.edit.file ?? "");
      formData.append("modifications", this.edit.modifications ? JSON.stringify(this.edit.modifications) : "");

      let pending = "Mise à jour de l'avenant";
      if (this.edit.id) {
        formData.append("action", "update");
      } else {
        formData.append("action", "create");
        pending = "Création de l'avenant";
      }
      AxiosOscar.post(this.avenants.url_api, formData, {pendingMsg: pending}).then(response => {
        this.edit = null;
        this.fetch();
      });
    },

    handlerDelete(avenant) {
      AxiosOscar.delete(avenant.url_api, {pendingMsg: "Suppression de l'avenant"}).then(response => {
        this.fetch();
      })
    },

    handlerApplyAvenant(avenant) {
      console.log("Application de l'avenant");
      let formData = new FormData();
      formData.append("id", avenant.id);
      formData.append("action", "apply");

      let pending = "Application de l'avenant";

      AxiosOscar.post(this.avenants.url_api, formData, {pendingMsg: pending, pendingBack: false}).then(response => {
        document.location.reload();
      });
    },

    handlerAddChange(type) {
      if (this.edit) {
        switch (type) {
          case "dateEnd":
            this.edit.modifications.push({
              type: type,
              mode: 'new',
              label: "Date de fin",
              value1: (new Date()).toISOString(),
            });
            break;
          default:
            this.edit.modifications.push({
              type: type,
              mode: 'new',
              label: "A définir",
              value1: null,
              valueObj: null,
              value2: null
            });
        }
      }
    },

    handlerRemoveChange(change) {
      let int = this.edit.modifications.indexOf(change);
      if (int >= 0) {
        this.edit.modifications.splice(int, 1);
      }
    },

    handlerUpdatePersonChange(change, event) {
      change.valueObj = {
        id: event.id,
        firstName: event.firstName,
        lastName: event.lastName,
      };
      change.value1 = event.id;
    },

    handlerUpdateOrganizationChange(change, event) {
      console.log(JSON.stringify(change));
      change.valueObj = {
        id: event.id,
        label: event.label,
      };
      change.value1 = event.id;
    },

    handlerSelectToDel(change, event) {
      console.log("Changement de la personne à supprimer", change, event);
      change.value1 = change.valueObj.id;
      change.value2 = null;
    },

    async handlerSelectFile(event) {
      if (event.target.files.length === 0) {
        this.edit.file = null;
        return;
      }
      this.edit.file = event.target.files[0];
    },

    fetch() {
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
.disabled {
  cursor: not-allowed;
  color: #AAA;
  text-decoration: line-through;
}
.change {
  border: 1px solid #CCC;
  padding: .3em;
  margin: .5em 0;
  border-left: solid #CCC 4px;
  display: flex;

  > div {
    flex: 1;
    display: flex;
    span.text {
      flex: 0;
      font-weight: bold;
      white-space: nowrap;
    }
    select,div{
      flex: 1;
    }
  }

  > i {
    flex: 0;
  }

  > nav {
    flex: 0;
    border-top: solid #CCC 4px;
    text-align: center;
  }

  > div {
    flex: 1;
    display: flex;
  }
}

.avenant {
  p {
    border-top: solid #CCC 1px;
    padding: .5em 2em;
  }
  nav {
    padding: .3em 0;
    border-top: solid #CCC 1px;
    text-align: center;
  }
  .modification {
    padding: 0 1em;
  }
}
</style>