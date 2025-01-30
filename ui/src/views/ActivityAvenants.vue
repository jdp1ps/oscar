<template>
  <modal title="Détails de l'avenant" :visible="edit" @modal-valid="handlerSave" @modal-cancel="handlerCancel">
    <form action="">
      <div class="form-group">
        <label for="name">Date (signature de l'avenant)</label>
        <datepicker v-model="edit.dateAvenant"/>
      </div>
      <div class="form-group">
        <label for="name">Modifications</label>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                  aria-expanded="false">
            Ajouter une modification <span class="caret"></span>
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
        <section class="modifications">
          <article v-for="change in edit.modifications" class="change">
            <div v-if="change.mode == 'new'">
              <div v-if="change.type === 'dateEnd'">
                <span class="text"> Date de fin : </span>
                <span><Datepicker v-model="change.value1"/></span>
              </div>
              <div v-else-if="change.type === 'personDel'">
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
                {{ change }}
              </div>
              <div v-else>-> {{ change }}</div>
            </div>
            <div v-else>
              <i class="icon-calendar" v-if="change.type === 'dateEnd'"></i>
              <i class="icon-user" v-if="change.type === 'personAdd'"></i>
              <i class="icon-user text-danger" v-if="change.type === 'personDel'"></i>
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
      <pre>{{ edit }}</pre>
    </form>
  </modal>

  <section class="avenants">
    <article class="avenant card" v-for="a in avenants.avenants">
      <h3>
        <strong>
          {{ $filters.dateFull(a.date) }}
        </strong>
        <small>
          - {{ a.status_text }}
        </small>
      </h3>
      <section class="modification">
        <article class="change" v-for="change in a.modifications">
          <i class="icon-calendar" v-if="change.type === 'dateEnd'"></i>
          <i class="icon-user" v-if="change.type === 'personAdd'"></i>
          <i class="icon-user text-danger" v-if="change.type === 'personDel'"></i>
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
        <a href="#" class="btn btn-xs btn-danger" @click.prevent="handlerDelete(a)">
          <i class="icon-trash"></i>
          Supprimer</a>
        <a href="#" class="btn btn-xs btn-default" @click.prevent="handlerEdit(a)">
          <i class="icon-pencil"></i>
          Editer
        </a>
        <a href="#" class="btn btn-xs btn-success" @click.prevent="handlerApply(a)" v-if="a.status === 100">
          <i class="icon-valid"></i>
          Appliquer l'avenant
        </a>
      </nav>
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
//import Test from "../../../vendor/unicaen/signature/public/src/views/SignatureFlows.vue";

const readFileAsText = (file) => new Promise((resolve, reject) => {
  const reader = new FileReader();
  reader.onload = ({target}) => {
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
    }
  },

  methods: {
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
      console.log("SAVE", this.avenants.url_api);
      let formData = new FormData();
      console.log(JSON.stringify(this.edit));
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
      console.log("SAVE", avenant.url_api);
      AxiosOscar.delete(avenant.url_api, {pendingMsg: "Suppression de l'avenant"}).then(response => {
        this.fetch();
      })
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

    handlerSelectPersonDel(change, event) {
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
.change {
  border: 1px solid #CCC;
  padding: .3em;
  margin: .5em 0;
  border-left: solid #CCC 4px;
  display: flex;

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