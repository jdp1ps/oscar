<template>
  <!-- erreurs -->
  <div class="overlay" v-if="error" style="z-index: 10000">
    <div class="overlay-content" style="max-width: 50%">
      <h2>
        Erreur
        <span class="overlay-closer" @click="error = null">X</span>
      </h2>
      <p>
        {{ error.response && error.response.data ? error.response.data : error }}
      </p>
    </div>
  </div>

  <div class="overlay" v-if="deleteDocument">
    <div class="overlay-content">
      <h2>
        Supprimer le document ?
        <span class="overlay-closer" @click="deleteDocument = null">X</span>
      </h2>
      <p class="alert-danger alert">
        <i class="icon-attention-1"></i>
        Souhaitez-vous supprimer le fichier <strong>{{ deleteDocument.fileName }}</strong> ?
      </p>

      <button class="btn btn-danger" @click="deleteDocument = null">
        <i class="icon-cancel-alt"></i> Annuler
      </button>
      <a class="btn btn-success" @click.prevent="handlerDeleteDocumentConfirm()">
        <i class="icon-valid"></i> Confirmer
      </a>
    </div>
  </div>

  <!-- Formulaire Modification/Version/Nouveau -->
  <div class="overlay" v-if="editedDocument">
    <div class="overlay-content">
      <h2>
        <small>
          <i class="icon-doc"></i>
          <span v-if="mode == 'new'">Téléversement d'un document</span>
          <span v-if="mode == 'edit'">Modification des informations</span>
          <span v-if="mode == 'version'">Nouvelle version</span>
        </small><br>
        <span v-if="editedDocument.id > 0">
            <strong>{{ editedDocument.fileName }}</strong>
          </span>
        <span v-else>
            Nouveau document dans <strong>{{ editedDocument.tabDocument.label }}</strong>
          </span>
        <span>
            ({{ mode }})
          </span>
        <span class="overlay-closer" @click="editedDocument = null">X</span>
      </h2>
      <div class="row">

        <div class="col-md-6">
          <div v-if="mode != 'edit'">
            <label for="file">Fichier</label>
            <input @change="handlerChangeFile" type="file" class="form-control" name="file" id="file"/>
          </div>

          <div>
            <label for="dateDeposit">Date de dépôt</label>
            <date-picker v-model="editedDocument.dateDeposit" id="dateDeposit"/>
          </div>

          <div>
            <label for="dateSend">Date d'envoi</label>
            <date-picker v-model="editedDocument.dateSend" id="dateSend"/>
          </div>
        </div>

        <div class="col-md-6">
          <div v-if="mode != 'version'">
            <label for="tabdocument">Onglet</label>
            <div>
              <select name="tabdocument" id="tabdocument" v-model="editedDocument.tabDocument.id"
                      class="form-control">
                <option :value="id" v-for="(tabDoc, id) in tabs" :key="id">{{ tabDoc.label }}</option>
              </select>
            </div>
          </div>

          <div v-if="mode != 'version'">
            <label for="typedocument">Type de document</label>
            <div class="alert alert-warning" v-if="editedDocument.process">
              Vous ne pouvez pas modifier le type d'un document engagé dans un processus de signature.
            </div>
            <div v-else>
              <select class="form-control" name="type" id="typedocument" v-model="editedDocument.category.id">
                <option :value="t.id" v-for="(t, id) in types" :key="t.id"
                        :disabled="t.flow && editedDocument.id > 0">
                  {{ t.label }} {{ t.flow && editedDocument.id ? '(signature)':'' }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="col-md-12">
          <label for="information">Informations</label>
          <textarea v-model="editedDocument.information" id="information" class="form-control"></textarea>
        </div>

        <div class="col-md-6" v-if="mode == 'change'">
          <!-- PRIVE, SI PRIVE AJOUT PERSONNES MODIFICATION DE DOCUMENT -->
          <div class="row">
                <span v-if="editedDocument.private === false">
                  <label for="tabdocument">Onglet document</label>
                  <div>
                    <select name="tabdocument" id="tabdocument" v-model="editedDocument.tabDocument.id"
                            class="form-control">
                      <option :value="id" v-for="(tabDoc, id) in tabs"
                              :key="id">{{ tabDoc.label }}</option>
                    </select>
                  </div>
                </span>
            <div class="col-md-6">
              <label for="private">Document privé</label>
            </div>
            <div class="col-md-6">
              <input type="checkbox" name="private" id="privateModifDoc" class="form-control"
                     v-model="editedDocument.private">
            </div>
          </div>
          <span v-if="editedDocument.private === true">
                 <label>Choix des personnes ayant accès à ce document</label>
                <h3>Ce document sera classé automatiquement dans l'onglet privé</h3>
                  <person-auto-completer @change="handlerSelectPersons"></person-auto-completer>
                  <span v-for="p in editedDocument.persons" :key="p.id" class="cartouche">
                    <i class="icon-cube"></i>
                    <span>{{ p.personName }}</span>
                    <span class="addon">
                      {{ p.affectation }}
                    </span>
                    <i v-if="p.personId !== idCurrentPerson" @click="handlerDeletePerson(p)"
                       class="icon-trash icon-clickable"></i>
                  </span>
              </span>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <nav class="buttons-bar">
            <button class="btn btn-danger" @click="editedDocument = null">
              <i class="icon-cancel-alt"></i> Annuler
            </button>
            <a class="btn btn-success" href="#" @click.prevent="handlerEditConfirm()">
              <i class="icon-valid"></i> Enregistrer
            </a>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE ERRORMESSAGES -->
  <div class="overlay" v-if="signDocument">
    <div class="overlay-content">
      <h2>
        Signature de document numérique
        <span class="overlay-closer" @click="signDocument = null">X</span>
      </h2>

      <div class="alert alert-danger" v-if="!signProcess">
        Aucun processus de signature disponible
      </div>
      <div v-else>
        <nav>
          Sélectionnez une procédure de signature
          <span v-for="p in signProcess" class="btn btn-lg btn-default"
                :class="{'btn-success':selectedSignProcess && selectedSignProcess.id == p.id}"
                @click="handlerSelectProcess(p)">
            <strong>{{ p.label }}</strong><br>
            <em>{{ p.description }}&nbsp;</em>
          </span>
        </nav>

        <section v-if="selectedSignProcess">
          <h3>
            <small>Procédure de signature</small><br>
            <strong>{{ selectedSignProcess.label }}</strong>
          </h3>
          <article v-for="step in selectedSignProcess.steps" class="step"
                   :class="step.missing_recipients ? 'error' : 'ok'">
            <h4>étape {{ step.order }} :<strong>{{ step.label }}</strong></h4>
            <ul class="metas">
              <li class="meta">Parapheur: <strong>{{ step.letterfile_label }}</strong></li>
              <li class="meta">Type: <strong>{{ step.level_label }}</strong></li>
              <li class="meta">Tous signent: <strong>{{ step.allSignToComplete ? 'Oui' : 'non' }}</strong></li>
            </ul>
            <div class="alert alert-danger" v-if="step.missing_recipients">
              Il manque des destinataires pour cette procédure.
            </div>
            <div class="row">
              <div class="col-md-6">
                <h5>Destinataires</h5>
                <div class="recipient" v-for="r in step.recipients">
                  <input type="checkbox" v-if="step.editable" v-model="r.selected"/>
                  <strong class="email">{{ r.email }}</strong>
                  <span class="fullname">{{ r.firstname }} {{ r.lastname }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <h5>Observateurs</h5>
                <div class="recipient" v-for="r in step.observers" v-if="step.observers.length">
                  <input type="checkbox" v-if="step.editable" v-model="r.selected"/>
                  <strong class="email">{{ r.email }}</strong>
                  <span class="fullname">{{ r.firstname }} {{ r.lastname }}</span>
                </div>
                <div class="alert alert-info" v-else>
                  Pas d'observateur pour cette étape
                </div>
              </div>
            </div>
          </article>
        </section>
      </div>

      <div class="alert alert-danger" v-if="signProcessError">
        Signature non-disponible : <strong>{{ signProcessError }}</strong>
      </div>

      <nav class="buttons-bar">
        <button class="btn btn-default" @click="signDocument = null">
          <i class="icon-cancel-alt"></i> Annuler
        </button>
        <button class="btn btn-success" @click="handlerPerformSignDocument(signDocument, selectedSignProcess)"
                :class="{'disabled': handlerPerformSignDocumentDisabled(signDocument, selectedSignProcess)}">
          <i class="icon-cancel-alt"></i> Valider
        </button>
      </nav>
    </div>
  </div>

  <!-- Détails du processus -->
  <div class="overlay" v-if="processDetails">
    <div class="overlay-content" style="max-width: 50%">
      <h2>
        <small><i class="icon-edit"></i>
          Procédure</small> <br><strong>{{ processDetails.label }}</strong> <br>
        <span class="signature-status-101">
          {{ processDetails.status_text }} -
          <em>étape {{ processDetails.current_step }} / {{ processDetails.total_steps }}</em>
        </span>
        <span class="overlay-closer" @click="handlerProcessDetailsOff">X</span>
      </h2>

      <section class="signature" :class="'signature-status-'+s.status" v-for="s in processDetails.steps">
        <h4>
          <small>étape {{ s.order }} : </small>
          <strong>{{ s.label }}</strong>
          <span class="status"> ({{ s.status_text }})</span>
        </h4>

        <ul class="metas">
          <li class="meta">Parapheur <strong>{{ s.letterfile }}</strong></li>
          <li class="meta">Niveau <strong>{{ s.level }}</strong></li>
          <li class="meta">Tous les destinataires signent <strong>{{ s.allSignToComplete ? 'Oui' : 'Non' }}</strong></li>
        </ul>

        <article class="recipient" :class="'signature-status-'+r.status" v-for="r in s.recipients">
          <strong class="fullname">{{ r.fullname }}</strong>
          <em class="email">{{ r.email }}</em>
          <small>{{ $filters.dateFull(r.dateFinished) }}</small>
          <span class="status">
            <span class="status-text">{{ r.status_text }}</span>
          </span>
        </article>
        <strong>Observateurs : </strong>
        <span v-for="o in s.observers" class="observer-inline">
          <small>{{ o.firstname }}</small> <span>{{ o.lastname }}</span>
        </span>
      </section>

      <div class="buttons-bar">
        <button class="btn btn-default" @click="handlerProcessDetailsOff">
          <i class="icon-cancel-outline"></i> Fermer
        </button>
      </div>
    </div>
  </div>
  <article class="card xs" v-for="doc in documents" :key="doc.id" :class="{'private-document': doc.private }">
    <div class="card-title">
      <i class="picto icon-anchor-outline" v-if="doc.location == 'link'"></i>
      <i class="picto icon-doc" :class="'doc' + doc.extension" v-else></i>
      <small class="text-light">{{ doc.category.label }} ~ </small>
      <strong>{{doc.fileName}}</strong>
      <small class="text-light" :title="doc.fileSize + ' octet(s)'" v-if="doc.location != 'url'">&nbsp;
        Version {{ doc.version }}
      </small>
    </div>
    <small>
      <i class="icon-briefcase"></i> Taille <strong>{{ $filters.filesize(doc.fileSize) }}</strong>
      <i class="icon-calendar"></i> Envoyé <strong>{{ $filters.timeAgo(doc.dateSend) }}</strong>
      <i class="icon-calendar"></i> Déposé <strong>{{ $filters.dateFull(doc.dateDeposit) }}</strong>
      <i class="icon-calendar"></i> Uploadé <strong>{{ $filters.dateFull(doc.dateUpload) }}</strong>
      <i class="icon-user"></i> par <strong v-if="doc.uploader">{{ doc.uploader.displayname }}</strong><em v-else>Inconnu</em>
    </small>
    <p>{{ doc.information }}</p>
    <section v-if="doc.private">
      <i class="icon-lock"/>
      Ce document est privé, accessible par :
      <span class="cartouche" v-for="p in doc.persons">
                  {{ p.personName }}
                </span>
    </section>
    <div class="card-content">
<!--      <pre>{{ doc }}</pre>-->
      <section v-if="doc.process" class="alert"
               :class="{'alert-success':doc.process.status == 201,
                              'alert-danger':doc.process.status >= 400,
                              'alert-info':doc.process.status < 200
            }">
        <i class="icon-hammer"></i>
        Procédure de signature <strong>{{ doc.process.label }}</strong> (<em>{{ doc.process.status_text }}</em>
        <span> - étape {{ doc.process.current_step }} / {{ doc.process.total_steps }}</span>)
        <button class="btn btn-xs btn-info" @click="handlerProcessDetailsOn(doc.process)">
          <i class="icon-help-circled"></i>
          Détails
        </button>
        <button class="btn btn-xs btn-danger"  v-if="doc.urlProcessDelete" @click="handlerDeleteProcess(doc)">
          <i class="icon-trash"></i>
          Annuler la procédure
        </button>
        <button class="btn btn-default btn-xs" v-if="doc.urlProcessUpdate" @click="handlerProcessReload(doc)">
          <i class="icon-cw-outline"></i>
          Actualiser
        </button>
      </section>

      <section v-if="displayActivity">
        Activité :
        <span :class="{'link': doc.activity.url_show}" @click="urlShow(doc.activity.url_show)">
              <strong>
                <i class="icon-cube"></i> / {{ doc.activity.num }}
              </strong>&nbsp;
              <em>
                {{ doc.activity.label }}
              </em>
              <small v-if="doc.activity.project_id">
                (<i class="icon-cubes"></i>{{ doc.activity.project_acronym }})
              </small>
            </span>
      </section>

      <div v-if="doc.versions && doc.versions.length">
        <div class="exploder">
          Versions précédentes :
        </div>
        <article v-for="sub in doc.versions" class="subdoc text-highlight">
          <i class="picto icon-doc" :class="'doc' + sub.extension"></i>
          <strong>{{ sub.fileName }}</strong>
          version <em>{{ sub.version }} </em>,
          téléchargé le
          <time>{{ sub.dateUpload | dateFullSort }}</time>
          <span v-if="sub.uploader">
                        par <strong>{{ sub.uploader.displayname }}</strong>
                        </span>

          <a :href="sub.urlDownload">
            <i class="icon-download-outline"></i>
            Télécharger cette version
          </a>
        </article>
      </div>

      <nav class="text-right show-over">
        <a class="btn btn-default btn-xs"
           :href="doc.basename"
           v-if="doc.location == 'url'" target="_blank">
          <i class="icon-link-ext"></i>
          Accéder au lien
        </a>

        <a class="btn btn-default btn-xs"
           href="#" v-if="doc.process_triggerable && displayButtonSign" @click.prevent="handlerProcessInit(doc)">
          <i class="icon-bank"></i>
          Signer ce document
        </a>

        <a class="btn btn-default btn-xs"
           :href="doc.urlDownload" v-if="doc.urlDownload && doc.location != 'url'">
          <i class="icon-upload-outline"></i>
          Télécharger
        </a>

        <button v-on:click="handlerNewVersion(doc)" class="btn btn-default btn-xs"
                v-if="doc.urlReupload">
          <i class="icon-download-outline"></i>
          Nouvelle Version
        </button>

        <a class="btn btn-default btn-xs" @click.prevent="handlerDeleteDocument(doc)" v-if="doc.urlDelete">
          <i class="icon-trash"></i>
          Supprimer
        </a>

        <a class="btn btn-xs btn-default" href="#" @click.prevent="handlerEdit(doc)"
           v-if="doc.urlEdit">
          <i class="icon-pencil"></i>
          Modifier
        </a>
      </nav>
    </div>
  </article>
</template>
<script>
import axios from "axios";
import Datepicker from '../components/Datepicker.vue';
import PersonAutoCompleter from '../components/PersonAutoCompleter.vue';
import AxiosMessage from "../utils/AxiosMessage.js";

export default {
  components: {
    'date-picker': Datepicker,
    'person-auto-completer': PersonAutoCompleter
  },

  props: {
    editable: {default: false},
    displayActivity: {default: false},
    documents: {default: []},
    signProcess: {default: null},
    tabs: {default: []},
    types: {default: []},
    displayButtonSign: {default: true}
  },

  data() {
    return {
      deleteDocument: null,
      signDocument: null,
      signProcessError: "",
      selectedSignProcess: null,
      processDetails: null,
      error: null,
      editedDocument: null,
      mode: null,
      fileToDownload: null
    }
  },

  methods: {
    urlShow(url = null) {
      console.log(url);
      if (url) {
        document.location = url;
      } else {
        return false;
      }
    },

    handlerProcessInit(doc) {
      console.log("Affichage fenêtre de signature");
      this.signDocument = doc;
    },

    /**
     * Permet de calculer si le bouton "Valider" en actif ou pas.
     *
     * @param document
     * @param signedProcess
     * @returns {boolean}
     */
    handlerPerformSignDocumentDisabled(document, signedProcess) {
      if (!signedProcess) return true;
      if (signedProcess.missing_recipients) return true;
      for (let i = 0; i < signedProcess.steps.length; i++) {
        let step = signedProcess.steps[i];
        let editable = step.editable;
        let count = 0;
        for (let j = 0; j < step.recipients.length; j++) {
          let recipient = step.recipients[j];
          if (editable) {
            if (recipient.selected == true) {
              count++;
            }
          } else {
            count++;
          }

        }
        if (count == 0) {
          this.signProcessError = "L'étape " + step.order + " \"" + step.label + "\" n'a pas de destinataire.";
          return true;
        }
      }

      this.signProcessError = "";
      return false;
    },

    handlerSelectProcess(process) {
      this.signProcessError = "";
      this.selectedSignProcess = process;
    },

    handlerPerformSignDocument(document, signedProcess) {
      let formData = new FormData();
      let url = document.urlProcessCreate;

      formData.append('document_id', document.id);
      formData.append('flow_datas', JSON.stringify(signedProcess));

      axios.post(url, formData).then(ok => {
        this.signDocument = null;
        this.selectedSignProcess = null;
        this.$emit('fetch');
      }, ko => {
        this.error = AxiosMessage.error(ko);
      })
      return false;
    },

    /**
     * Affichage des détails d'une procédure de signature
     * @param process
     */
    handlerProcessDetailsOn(process) {
      this.processDetails = process;
    },

    handlerDeleteProcess(doc) {
      console.log('handlerDeleteProcess', doc);
      axios.post(doc.urlProcessDelete).then(ok => {
        this.$emit('fetch');
      }, ko => {
        this.error = AxiosMessage.error(ko);
      })

    },

    handlerDeleteDocument(doc) {
      this.deleteDocument = doc;
    },

    handlerDeleteDocumentConfirm() {
      console.log(this.deleteDocument.urlDelete);
      axios.post(this.deleteDocument.urlDelete).then(ok => {
        this.$emit('fetch');
      }, ko => {
        this.error = AxiosMessage.error(ko);
      });
      this.deleteDocument = null;
    },

    handlerEdit(doc) {
      this.mode = "edit";
      this.editedDocument = doc;
    },

    handlerEditConfirm(){
      let formData = new FormData();
      let url = "";
      formData.append('data', JSON.stringify(this.editedDocument));

      if (this.mode == 'version') {
        formData.append('action', 'version');
        url = this.editedDocument.urlReupload;
      } else if (this.mode == 'new') {
        formData.append('action', 'new');
        url = this.urlUploadNewDoc;
      } else if (this.mode == 'edit') {
        formData.append('action', 'edit');
        url = this.editedDocument.urlEdit;
      }

      if (this.mode != 'edit') {
        if (this.fileToDownload !== null) {
          formData.append('file', this.fileToDownload, this.fileToDownload.name);
        } else {
          this.error = "Aucun fichier sélectionner a téléverser !";
          return;
        }
      }

      axios.post(url, formData).then(ok => {
        this.editedDocument = null;
        this.$emit('fetch');
      }, ko => {
        this.error = AxiosMessage.error(ko);
      })
    },

    handlerChangeFile(evt){
        if (event.target.files.length === 0) {
          return;
        }
        this.fileToDownload = event.target.files[0];
    },

    handlerNewVersion(doc) {
      console.log('handlerNewVersion');
      this.fileToDownload = null;
      this.mode = 'version';
      this.editedDocument = doc;
    },

    /**
     * Masquer les détails d'une procédure de signature
     *
     * @param process
     */
    handlerProcessDetailsOff() {
      this.processDetails = null;
    },

    handlerProcessReload(doc) {
      let formData = new FormData();
      axios.post(doc.urlProcessUpdate, formData).then(ok => {
        this.$emit('fetch');
      }, ko => {
        this.error = AxiosMessage.error(ko);
      })
    }

  }
}
</script>

<style scoped>
.step-content {
  border: thin solid #aaa;
  border-top: none;
  padding: 1em;
}

.card .alert {
  padding: 0 .5em;
  margin: .1em;
}

.step {
  flex: 1;
  padding: .25em 1em 1em;
  text-align: left;
  border: 1px solid #aaa;
  border-left: 4px solid #aaa;
  margin: .5em;
}

.step.error {
  border-color: #990000;
}

.step.ok {
  /*border-color: #339900;*/
}

.step .alert {
  margin: 0;
  padding: .25em 1em;
}

.current {
  color: #333;
  background: white;
  border-bottom-color: white;
}

.private {
  color: #777;
  text-shadow: 1px -1px 1px rgba(255, 255, 255, .3);
  font-weight: 100;
}

.stepHandler {
  border-radius: 18px;
  outline: 0;
  padding: 8px 12px;
  text-align: center;
  transition: all 0.3s ease-out;
  display: inline-block;
  margin-left: 100px;
}

.stepHandler:before {
  font-family: "fontello";
  content: "";
  /*content: "♥";*/
}

.stepHandler:hover,
.stepHandler:focus {
  color: #333;
  background: #8E969F;
}

.process, .signature {
  background: white;
  padding: .5em 1em;
  margin: 1em;
  border: thin solid #92b2ae;
  border-left-width: 8px
}

.signature h2 {
  font-size: 1.5em;
  margin: 0;
  border-bottom: solid thin #c5c5c5;
  padding: .1em 0;
  display: flex
}

.signature h2 span {
  flex: 1 1 auto
}

.signature h2 .status-flag {
  flex: 0 0 6em;
  text-align: right
}

.buttons-bar {
  padding: .5em
}

.recipient {
  border: solid thin #92b2ae;
  border-left-width: 8px;
  display: flex;
  text-shadow: 1px -1px 0 rgb(255, 255, 255);
  padding: .25em 0 .25em .5em;
  margin-right: .5em
}

.recipient .email, .recipient .fullname {
  flex: 1
}

.recipient .status-flag {
  flex: 0 0 6em
}

.metas {
  display: inline-block;
  margin: 0;
  padding: .5em 0
}

.metas .meta {
  display: inline-block;
  background: #d4dedd;
  padding: 0 .5em;
  border-radius: 8px
}

.recipient-signature {
  background: white;
  margin: .5em 0;
  padding: .7em 1em .1em;
  border-left: solid 4px #666666
}

.observer-inline {
  background: #e1ece6;
  display: inline-block;
  border-radius: 4px;
  padding: 0 .5em;
  margin: 0 .3em;
}

.recipient-signature h3 {
  margin: 0
}

.status:hover .status-text {
  opacity: 1
}

.status .status-text {
  transition: opacity .3s;
  font-weight: 700;
  color: #fff;
  text-shadow: -1px 1px 0 rgba(0, 0, 0, .5);
  margin: 0;
  padding: 0 .25em;
  font-size: .75em
}

.status-flag {
  display: none
}

.signature-status-501 {
  border-color: #83243a
}

.signature-status-501 .status-text, .signature-status-501 .status-flag {
  background-color: #83243a
}

.signature-status-501 .status-flag:before {
  content: ""
}

.signature-status-401 {
  border-left-color: #8d7260
}

.signature-status-401 .status-text, .signature-status-401 .status-flag {
  background-color: #8d7260
}

.signature-status-401 .status-flag:before {
  content: ""
}

.signature-status-105 {
  border-left-color: #8eb4c9
}

.signature-status-105 .status-text, .signature-status-105 .status-flag {
  background-color: #8eb4c9
}

.signature-status-105 .status-flag:before {
  content: ""
}

.signature-status-101 {
  border-left-color: #92b2ae
}

.signature-status-101 .status-text, .signature-status-101 .status-flag {
  background-color: #92b2ae
}

.signature-status-101 .status-flag:before {
  content: ""
}

.signature-status-201 {
  border-left-color: #447c44
}

.signature-status-201 .status-text, .signature-status-201 .status-flag {
  background-color: #447c44
}

.signature-status-201 .status-flag:before {
  content: ""
}

</style>