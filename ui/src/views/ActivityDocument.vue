<template>
  <!-- ERREUR -->
  <div class="overlay" v-if="error" style="z-index: 101">
    <div class="overlay-content" style="max-width: 50%">
      <h2>
        Erreur documents
        <span class="overlay-closer" @click="error = null">X</span>
      </h2>
      <p class="alert-danger alert">
        <i class="icon-attention-1"></i>{{ error }}
      </p>

      <button class="btn btn-default" @click="error = null">
        <i class="icon-cancel-outline"></i> Fermer
      </button>
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
          <small>Étape {{ s.order }} : </small>
          <strong>{{ s.label }}</strong>
          <span class="status"> ({{ s.status_text }})</span>
        </h4>

        <ul class="metas">
          <li class="meta">Parapheur <strong>{{ s.letterfile }}</strong></li>
          <li class="meta">Niveau <strong>{{ s.level }}</strong></li>
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
        <span v-for="o in s.observers">
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

  <!-- Modal de suppression -->
  <section style="position: relative; min-height: 100px">
    <div class="overlay" v-if="deleteData">
      <div class="overlay-content">
        <h2>
          Suppression du fichier ?
          <span class="overlay-closer" @click="deleteData = null">X</span>
        </h2>
        <p class="alert-danger alert">
          <i class="icon-attention-1"></i>
          Souhaitez-vous supprimer le fichier <strong>{{ deleteData.fileName }}</strong> ?
        </p>

        <button class="btn btn-danger" @click="deleteData = null">
          <i class="icon-cancel-alt"></i> Annuler
        </button>
        <a class="btn btn-success" :href="deleteData.urlDelete">
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
        <!--        <pre style="font-size: .7em">{{ editedDocument }}</pre>-->
        <div class="row">

          <div class="col-md-6">
            <div v-if="mode != 'edit'">
              <label for="file">Fichier</label>
              <input @change="uploadFile" type="file" class="form-control" name="file" id="file"/>
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
                  <option :value="id" v-for="(tabDoc, id) in tabsWithDocuments" :key="id">{{ tabDoc.label }}</option>
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
                  <option :value="t.id" v-for="(t, id) in typesDocuments" :key="t.id"
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
                      <option :value="id" v-for="(tabDoc, id) in tabsWithDocuments"
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
              <a class="btn btn-success" href="#" @click.prevent="applyEdit()">
                <i class="icon-valid"></i> Enregistrer
              </a>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE ERRORMESSAGES -->
    <div class="overlay" v-if="errorMessages.length !==0">
      <div class="overlay-content">
        <h2>
          <span class="overlay-closer" @click="errorMessages = []">X</span>
        </h2>
        <ul>
          <li v-for="message in errorMessages">
            {{ message }}
          </li>
        </ul>
        <button class="btn btn-danger" @click="errorMessages = []">
          <i class="icon-cancel-alt"></i> Retour
        </button>
      </div>
    </div>

    <!-- ############################### TAB : INFORMATIONS PAR DOCUMENT LISTING PAR ONGLET ASSOCIÉ ######################################################-->
    <section class="documents-content">
      <div class="tabs">
        <div class="tab" :class="{'selected': selectedTabId === tab.id }"
             v-for="tab in packedDocuments"
             @click.prevent="handlerSelectTab(tab)">
          {{ tab.label }}
          <sup class="label label-default">{{ tab.total }}</sup>
        </div>
        <div class="tab" :class="{'selected': displayComputed }" @click.prevent="handlerSelectTab('computed')">
          Documents générés
        </div>
      </div>

      <div class="tab-content" v-show="displayComputed">
        <article class="card xs" v-for="doc in computedDocuments" :key="doc.key">
          <div class="">
            <i class="picto icon-doc"></i>
            <strong>{{doc.label}}</strong>
            <small class="text-light">&nbsp;
              (Document généré automatiquement)
            </small>
          </div>

          <nav class="text-right show-over">
            <a class="btn btn-default btn-xs" :href="doc.url">
              <i class="icon-upload-outline"></i>
              Télécharger
            </a>
          </nav>
        </article>
      </div>
      <div class="tab-content" v-for="tab in packedDocuments" v-show="selectedTabId === tab.id">
        <nav v-if="tab.manage" class="text-right">
          <button v-on:click="handlerNew(tab.id)" class="btn btn-xs btn-default" v-if="tab.manage">
            <i class="icon-download"></i>
            Téléverser un document
          </button>
        </nav>
        <document-list
            :documents="tab.documents"
            :tabs="tabsWithDocuments"
            :types="typesDocuments"
            :sign-process="signProcess"
            :display-activity="false"
            @fetch="fetch"
        />
      </div>
    </section>
  </section>
</template>
<script>

import axios from 'axios';
import Datepicker from '../components/Datepicker.vue';
import PersonAutoCompleter from '../components/PersonAutoCompleter.vue';
import moment from 'moment';
import 'moment/locale/fr';
import DocumentsList from "./DocumentsList.vue";

// Traitement spécifique de l'onglet Privé
const PRIVATE = "private";

// ok
export default {

  components: {
    "document-list": DocumentsList,
    "date-picker": Datepicker,
    "person-auto-completer": PersonAutoCompleter
  },

  props: {
    urlUploadNewDoc: {required: true},
    urlSignDocument: {required: false},
    url: {required: true}
  },

  data() {
    return {
      idCurrentPerson: null,
      // Formulaire Upload champs de formulaire
      persons: [],
      computedDocuments: [],
      dateDeposit: '',
      dateSend: '',
      fileUrl: '',
      fileUrlLabel: '',
      privateDocument: false,
      selectedIdTypeDocument: null,
      selectedIdTabDocument: null,
      informationsDocument: '',
      fileToDownload: null,
      // Objet hydraté selon contexte et envoyé lors d'un téléversement d'un nouveau document
      uploadNewDocData: {
        'dateDeposit': this.dateDeposit,
        'dateSend': this.dateSend,
        'private': this.privateDocument,
        'type': this.selectedIdTypeDocument,
        'tab': this.selectedIdTabDocument,
        'informations': this.informationsDocument,
        'persons': this.persons,
        'baseUrlUpload': this.urlUploadNewDoc,
        'url': '',
        'init': false
      },
      // Message boite modal pour l'utilisateur (erreurs pour exemple)
      errorMessages: [],
      // Onglet sélectionné
      tabId: null,
      // Données des documents par ID_onglets (idTab retour Json)
      tabsWithDocuments: null,
      // Formulaire de soumission téléversement nouveau document
      formData: null,
      error: null,
      deleteData: null,
      editData: null,
      uploadDoc: null,
      documents: [],
      loading: true,
      sortField: 'dateUpload',
      sortDirection: -1,
      editable: true,
      remoterState: null,
      displayComputed: false,
      signDocument: null,

      signProcess: null,
      selectedSignProcess: null,
      signProcessError: "",

      editedDocument: null,
      mode: null,

      // Details
      processDetails: null,

      mode_url: false,
      typesDocuments: [],

      // Onglet active
      selectedTab: null,
      selectedTabId: null
    }
  },

  computed: {
    currentFlow() {
      if (this.editedDocument.category.id) {
        let category = this.typesDocuments.find(i => i.id == this.editedDocument.category.id);
        if (category.flow) {
          return category.flow.signatureflow;
        }
      }
      return false;
    },
    selectedTypeDocument() {
      if (this.selectedIdTypeDocument) {
        return this.typesDocuments.find(item => item.id == this.selectedIdTypeDocument);
      }
      return null;
    },
    /**
     * Retourne les documents triés A REVOIR ENTIEREMENT.
     * @returns {Array}
     */
    documentsPacked() {
      let out = [];
      if (this.documents) {
        let documents = this.documents;
        out = documents.sort(function (a, b) {
          if (a[this.sortField] < b[this.sortField])
            return -1 * this.sortDirection;
          if (a[this.sortField] > b[this.sortField])
            return 1 * this.sortDirection;
          return 0;
        }.bind(this));
      }
      ;
      return out;
    },


    packedDocuments() {
      let packed = {};

      if (this.tabsWithDocuments) {
        for (const [i, tab] of Object.entries(this.tabsWithDocuments)) {
          let documents = {};
          for (const [j, doc] of Object.entries(tab.documents)) {
            let docKey = doc.fileName;
            if (!documents.hasOwnProperty(docKey)) {
              doc.versions = [];
              documents[docKey] = doc;
            } else {
              documents[docKey].versions.push(doc);
            }
          }

          packed[i] = {
            id: tab.id,
            label: tab.label,
            total: tab.total,
            description: tab.description,
            manage: tab.manage,
            roles: tab.roles,
            documents,
          }
        }
      }

      return packed;
    }
  },

  methods: {

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
          if( editable ){
            if (recipient.selected == true) {
              count++;
            }
          } else {
            count++;
          }

        }
        if (count == 0) {
          this.signProcessError = "L'étape " + step.order + " \""+ step.label +"\" n'a pas de destinataire.";
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
      let url = this.urlSignDocument;

      formData.append('document_id', document.id);
      formData.append('flow_datas', JSON.stringify(signedProcess));

      axios.post(url, formData).then(ok => {
        this.signDocument = null;
        this.selectedSignProcess = null;
        this.fetch();
      }, ko => {
        this.error = ko.response.data ? ko.response.data : ko.message;
      })
      return false;
    },

    renderDate(date) {
      if (!date) {
        return "non précisé"
      } else {
        return moment(date).fromNow()
      }
    },

    /**
     * Affichage des détails d'une procédure de signature
     * @param process
     */
    handlerProcessDetailsOn(process) {
      this.processDetails = process;
    },

    /**
     * Masquer les détails d'une procédure de signature
     *
     * @param process
     */
    handlerProcessDetailsOff() {
      this.processDetails = null;
    },

    /**
     * Changement d'onglet
     *
     * @param tabId
     */
    activeTab(tabId) {
      // Affectation valeur du tab dans lequel on se trouve
      this.tabId = tabId;
    },

    /**
     * Suppression de document (écran de confirmation)
     *
     * @param document
     */
    deleteDocument(document) {
      this.deleteData = document;
    },

    handlerSignDocument(document) {
      this.signDocument = document;
    },

    /**
     * TODO
     * @param field
     */
    order: function (field) {
      if (this.sortField == field) {
        this.sortDirection *= -1;
      } else {
        this.sortField = field;
      }
    },

    cssStepCurrent(tabId) {
      return tabId === this.tabId ? "current" : "";
    },

    cssSort: function (compare) {
      return compare === this.sortField ? "active" : "";
    },

    /**
     * Modification d'un document
     *
     * @param document
     */
    handlerEdit(document) {
      this.editedDocument = document;
      this.mode = 'edit';
    },

    /**
     * Selection d'une personne à ajouter pour l'accès privé.
     *
     * @param person
     */
    handlerSelectPersons(person) {
      if (person.id) {
        let personSelected = {
          "personName": person.displayname,
          "personId": person.id,
          "affectation": person.affectation
        };
        let comparePersonId = person.id;
        let isPresent = false;
        this.persons.forEach(function (person) {
          if (person.personId === comparePersonId) {
            isPresent = true;
          }
        });
        if (false === isPresent) {
          if (this.editedDocument) {
            if (!this.editedDocument.persons) this.editedDocument.persons = [];
            this.editedDocument.persons.push(personSelected);
          } else {
            this.persons.push(personSelected);
          }
        }
      }
    },

    /**
     * Suppression de la personne du mode privé
     *
     * @param person
     */
    handlerDeletePerson(person) {
      this.persons.splice(this.persons.indexOf(person), 1);
    },


    /**
     Déclenche ouverture Modal Upload nouveau document initialise datas/reset et affectation de base
     Important surtout dans le scénario de l'ouverture modal avec modification datas et fermeture de la modale,
     réouverture de cette modale sans avoir soumis la première fois
     */
    handlerNew(tabId) {
      this.mode = 'new';
      this.editedDocument = {
        "id": -1,
        "version": 1,
        "information": "",
        "fileName": "",
        "process": false,
        "process_sendable": null,
        "fileSize": 0,
        "typeMime": null,
        "dateUpload": null,
        "dateDeposit": null,
        "dateSend": null,
        "extension": null,
        "category": {"id": null},
        "tabDocument": this.getTabById(tabId),
        "private": false,
        "persons": [],
        "location": "local",
        "urlDelete": "",
        "urlDownload": "",
        "urlReupload": "",
        "uploader": null,
        "urlPerson": false
      };
    },

    /**
     * Nouvelle version du document.
     *
     * @param document
     */
    handlerNewVersion(document) {
      this.mode = 'version';
      this.editedDocument = document;
    },

    /**
     * Upload d'un fichier (on conserve le fichier)
     *
     * @param event
     */
    uploadFile(event) {
      if (event.target.files.length === 0) {
        return;
      }
      this.fileToDownload = event.target.files[0];
    },

    handlerProcessReload(doc) {
      let formData = new FormData();
      axios.post(doc.manage_process, formData).then(ok => {
        this.fetch();
      }, ko => {
        this.error = ko.response && ko.response.data ? ko.response.data : ko;
      })
    },


    applyEdit() {
      let formData = new FormData();
      let url = "";
      formData.append('data', JSON.stringify(this.editedDocument));

      if (this.mode === 'version') {
        formData.append('action', 'version');
        url = this.editedDocument.urlReupload;
      } else if (this.mode === 'new') {
        formData.append('action', 'new');
        formData.append('flow', ''); //JSON.stringify(this.currentFlow));
        url = this.urlUploadNewDoc;
      } else if (this.mode === 'edit') {
        formData.append('action', 'edit');
        url = this.editedDocument.urlReupload;
      }

      if (this.mode !== 'edit') {
        if (this.fileToDownload !== null) {
          formData.append('file', this.fileToDownload, this.fileToDownload.name);
        } else {
          this.error = "Aucun fichier sélectionner a téléverser !";
          return;
        }
      }

      axios.post(url, formData).then(ok => {
        this.editedDocument = null;
        this.fetch();
      }, ko => {
        this.error = ko.response && ko.response.data ? ko.response.data : ko;
      })
    },

    // Modification du type de document / changement onglet, privé ou pas, personnes ou pas
    performEdit() {
      // Fenêtre messages d'erreurs avant soumission Form
      this.errorMessages = [];
      // Personnes éventuellement associés
      let persons = [];
      // Onglet pour le document
      let newTabDoc = "";
      /**
       Document privé ou non
       Conversion, envoie post 0 ou 1 ("true" ou "false" sont transmis en tant que chaine en http(s))
       */
      let privateBool = true;
      if (this.editData.private === true) {
        privateBool = 1;
        if (this.persons.length !== 0) {
          this.persons.forEach((p) => {
            persons.push(p.personId);
          });
        }
      } else {
        //console.log("VALEUR this.editData.tabDocument_id : ",this.editData.tabDocument_id);
        privateBool = 0;
        // Mauvais statut Onglet (soit statut défaut soit aucune valeur)
        if (this.editData.tabDocument_id === PRIVATE || this.editData.tabDocument_id === "") {
          this.errorMessages.push("Vous devez sélectionner un onglet pour la modification (ce n'est pas un document qualifié privé)");
        } else {
          newTabDoc = this.editData.tabDocument_id;
        }
      }
      // Fenêtre de message d'erreur
      if (this.errorMessages.length !== 0) {
        return;
      }
      // Id du doc
      let documentId = this.editData.document.id;
      // Category du document (type)
      let newType = this.editData.documentype_id;
      // Initialisation des données de Vue
      this.editData = null;
      this.persons = [];

      let formData = new FormData();
      formData.append('documentId', documentId);
      formData.append('type', newType);
      formData.append('tabDocument', newTabDoc);
      formData.append('private', privateBool);
      formData.append('persons', persons);
    },

    getTabById(tabId) {
      let tab = this.tabsWithDocuments[tabId];
      return {
        "id": tab.id,
        "label": tab.label
      }
    },

    // Méthode appelée lors de l'appel via la méthode fetch démarrage du module
    handlerSuccess(success) {
      try {
        if (!success.data) {
          throw "Impossible de charger les documents, vérifiez que vous êtes toujours connecté";
        }
        this.idCurrentPerson = success.data.idCurrentPerson;
        let documents = Array.isArray(success.data.tabsWithDocuments) ? {} : success.data.tabsWithDocuments;
        let defaultTab = null;
        let selectedTab = null;

        if (!documents) {
          throw new Error("Vous n'avez pas accès aux documents");
        } else {
          Object.keys(documents).forEach((item) => {
            let tab = documents[item];
            tab.total = tab.documents.length;
            tab.documents.sort((x, y) => y.version - x.version);
            tab.documents.forEach(item => {
              item.explode = true;
            })
            if (defaultTab == null) defaultTab = tab.id;
            if (selectedTab == null && tab.documents.length > 0) {
              selectedTab = tab.id;
              //browsers.sort((x, y) => x.year - y.year);
            }
          });
          this.tabsWithDocuments = documents;
          this.computedDocuments = success.data.computedDocuments;
          this.signProcess = success.data.process_datas;
          this.typesDocuments = success.data.typesDocuments;
          if (this.selectedTabId == null) {
            this.selectedTabId = selectedTab ? selectedTab : defaultTab;
          }
          //   // if( this.tabsWithDocuments[i].documents.length ){
          //   //   this.selectedTabId = this.tabsWithDocuments[i].id;
          //   // }
          // }

          if (this.tabsWithDocuments.unclassified && this.tabsWithDocuments.unclassified.documents.length) {
            this.selectedTab = this.tabsWithDocuments.unclassified;
          } else {
            let keys = Object.keys(this.tabsWithDocuments)[0];
            if (keys.length) {
              this.selectedTab = this.tabsWithDocuments[keys[0]];
            }
          }
        }
      } catch (err) {
        this.error = err;
      }
    },

    handlerSelectTab(tab) {
      if (tab == "computed") {
        this.displayComputed = true;
        this.selectedTab = null;
        this.selectedTabId = null;
      } else {
        this.displayComputed = false;
        this.selectedTab = tab;
        this.selectedTabId = tab.id;
      }
    },

    // Recup datas Docs
    fetch() {
      axios.get(this.url).then(ok => {
        this.handlerSuccess(ok)
      }, ko => {
        this.error = ko.response.data ? ko.response.data : ko;
      });
    }
  },

  mounted() {
    // Au chargement du module dans la page appel méthode initialisation -> fetch()
    // Récupération des données documents par rapport à l'id de l'activité
    this.fetch();
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

</style>
