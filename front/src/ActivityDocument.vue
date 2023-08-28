<template>
<!-- MODAL DE SUPPRESSION DE DOCUMENT -->
  <section style="position: relative; min-height: 100px">
    <!-- Composant affichage erreurs appels retour Ajax -->
    <ajax-oscar :oscar-remote-data="remoterState"/>

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

<!-- MODAL DE MODIFICATIONS DU TYPE DE DOCUMENT -->
    <div class="overlay" v-if="editData">
      <div class="overlay-content">
        <h2>
          Modification du document : <i class="icon-doc"></i> {{ editData.basename }}
          <span class="overlay-closer" @click="editData = null">X</span>
        </h2>
        <div class="row">
          <div class="col-md-6">
            <label for="typedocument">Type de document</label>
            <div>
              <select name="type" id="typedocument" v-model="editData.documentype_id">
                <option :selected="editData.documentype_id == id" :value="id" v-for="(t, id) in documentTypes" :key="id">{{ t }}</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <!-- PRIVE, SI PRIVE AJOUT PERSONNES MODIFICATION DE DOCUMENT -->
            <div class="row" style="margin-top: 20px;">
                <span v-if="editData.private === false">
                <label for="tabdocument">Onglet document</label>
                <div>
                  <select name="tabdocument" id="tabdocument" v-model="editData.tabDocument_id">
                    <option v-if="tabDoc.manage === true && tabDoc.id !='private'" :selected="editData.tabDocument_id == id" :value="id" v-for="(tabDoc, id) in tabsWithDocuments" :key="id">{{ tabDoc.label }}</option>
                  </select>
                </div>
              </span>
              <div class="col-md-6">
                <label for="private">Document privé</label>
              </div>
              <div class="col-md-6">
                <input type="checkbox" name="private" id="privateModifDoc" class="form-control"
                       v-model="editData.private">
              </div>
            </div>
            <span v-if="editData.private === true">
                 <label>Choix des personnes ayant accès à ce document</label>
                <h3>Ce document sera classé automatiquement dans l'onglet privé</h3>
                  <person-auto-completer @change="handlerSelectPersons"></person-auto-completer>
                  <span v-if="persons.length !== 0" v-for="p in persons" :key="p.personId" class="cartouche">
                    <i class="icon-cube"></i>
                    <span>{{ p.personName }}</span>
                    <span v-if="p.affectation.trim() !==''" class="addon">
                      {{ p.affectation }}
                    </span>
                    <i v-if="p.personId !== idCurrentPerson" @click="handlerDeletePerson(p)" class="icon-trash icon-clickable"></i>
                  </span>
              </span>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <button class="btn btn-danger" @click="editData = null">
              <i class="icon-cancel-alt"></i> Annuler
            </button>
            <a class="btn btn-success" href="#" @click.prevent="performEdit()">
              <i class="icon-valid"></i> Enregistrer
            </a>
          </div>
        </div>
      </div>
    </div>

<!-- MODAL DE TÉLÉVERSEMENT D'UN NOUVEAU DOCUMENT OU NOUVELLE VERSION -->
    <div class="overlay" v-if="uploadDoc">
      <div class="overlay-content">
        <h2>
          Téléverser un nouveau document
          dans <strong>{{ selectedTab.label }}</strong>
          <span class="overlay-closer" @click="uploadDoc = null">X</span>
        </h2>
        <div class="alert">
          <label for="switch_mode">
            Cochez cette case si le fichier est un URL
            <input type="checkbox" name="switch_mode" v-model="mode_url">
          </label>
        </div>
        <div style="width: 90%; margin-left: 5%">
          <div class="row">
            <div class="col-md-6">

              <div v-if="mode_url">
                <!-- Fichier upload -->
                <label for="url">URL du fichier</label>
                <input type="text" class="form-control" name="url" id="url"
                       placeholder="Lien vers la ressource"
                       v-model="fileUrl" />
                <label for="label">Description de l'URL</label>
                <input type="text" class="form-control" name="label" id="label"
                       placeholder="Description rapide de la ressource"
                       v-model="fileUrlLabel" />
              </div>
              <div v-else>
                <!-- Fichier upload -->
                <label for="file">Fichier</label>
                <input @change="uploadFile" type="file" class="form-control" name="file" id="file"/>
              </div>
              <!-- Date de dépot -->
              <label>Date de dépôt</label>
              <p class="help">Date à laquelle le fichier a été reçu</p>
              <code>{{ dateDeposit }}</code>
              <date-picker v-model="dateDeposit" :moment="moment"></date-picker>
              <!-- Date d'envoi' -->
              <label>Date d'envoi</label>
              <p class="help">Date à laquelle le fichier a été envoyé</p>
              <date-picker v-model="dateSend" :moment="moment"></date-picker>
            </div>

            <!-- TYPE DE DOCUMENT -->
            <div class="col-md-6">
              <label for="type">Type de document</label>
              <select v-model="selectedIdTypeDocument" name="type" id="type" class="form-control">
                <option v-for="(label, id) in documentTypes" :value="id" :key="id">
                  {{ label }}
                </option>
              </select>

<!-- PRIVE, SI PRIVE AJOUT PERSONNES -->
              <div class="row" style="margin-top: 20px;" v-if="mode_url != true">
                <div class="col-md-6">
                  <label for="private">Document privé</label>
                </div>
                <div class="col-md-6">
                  <input type="checkbox" name="private" id="private" class="form-control" v-model="privateDocument">
                </div>
              </div>
              <div v-else class="alert alert-info">
                Les URL ne peuvent pas être définie comme privée dans Oscar
              </div>
              <span v-if="privateDocument === true">
                <h4>Ce document sera classé automatiquement dans l'onglet privé</h4>
                 <label>Choix des personnes ayant accès à ce document</label>
                  <person-auto-completer @change="handlerSelectPersons"></person-auto-completer>
                  <span v-if="persons.length !== 0" v-for="p in persons" :key="p.personId" class="cartouche">
                    <i class="icon-cube"></i>
                    <span>{{ p.personName }}</span>
                    <span v-if="p.affectation.trim() !=''" class="addon">
                      {{ p.affectation }}
                    </span>
                    <i v-if="p.affectation.trim() ===''" @click="handlerDeletePerson(p)"
                       class="icon-trash icon-clickable"></i>
                  </span>
              </span>
            </div>

<!-- INFORMATIONS COMPLEMENTAIRES -->
            <div class="row">
              <div class="col-md-12">
                <label for="informations">Note</label>
                <textarea v-model="informationsDocument" name="informations" id="informations" class="form-control"
                          cols="30" rows="10"></textarea>
              </div>
            </div>
            <hr>
            <nav class="buttons text-center">
              <button class="btn btn-danger" @click="uploadDoc = null">
                <i class="icon-cancel-alt"></i> Annuler
              </button>
              <button class="btn btn-success" href="#" @click.prevent="performUpload()">
                <i class="icon-valid"></i> Enregistrer
              </button>
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
        <div class="tab"  :class="{'selected': displayComputed }" @click.prevent="handlerSelectTab('computed')">
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
          <button v-on:click="handlerUploadNewDoc(tab.id)" class="btn btn-xs btn-default" v-if="tab.manage">
            <i class="icon-download"></i>
            Téléverser un document
          </button>
        </nav>
        <hr>
        <article class="card xs" v-for="doc in tab.documents" :key="doc.id" :class="{'private-document': doc.private }">
          <div class="">
            <i class="picto icon-anchor-outline" v-if="doc.location == 'link'"></i>
            <i class="picto icon-doc" :class="'doc' + doc.extension" v-else></i>
            <small class="text-light">{{ doc.category.label }} ~ </small>
            <strong>{{doc.fileName}}</strong>
            <small class="text-light" :title="doc.fileSize + ' octet(s)'" v-if="doc.location != 'url'">&nbsp;
              ({{doc.fileSize | filesize}}) - Version {{ doc.version }}
            </small>
          </div>
          <section v-if="doc.private">
            <i class="icon-lock" />
            Ce document est privé, accessible par :
            <span class="cartouche" v-for="p in doc.persons">
                  {{ p.personName }}
                </span>
          </section>
          <p>
            {{ doc.information }}
          </p>
          <div class="card-content">
              <div v-if="doc.versions.length">
                <div class="exploder">
                  Versions précédentes :
                </div>
                <article v-for="sub in doc.versions" class="subdoc text-highlight">
                  <i class="picto icon-doc" :class="'doc' + sub.extension"></i>
                  <strong>{{ sub.fileName }}</strong>
                  version <em>{{ sub.version }} </em>,
                  téléchargé le <time>{{ sub.dateUpload | dateFullSort }}</time>
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
                 :href="doc.urlDownload" v-if="doc.urlDownload && doc.location != 'url'">
                <i class="icon-upload-outline"></i>
                Télécharger
              </a>
              <!--
              <button v-on:click="handlerUploadNewVersionDoc(tab.id, doc.urlReupload, doc.category.id)" class="btn btn-default btn-xs"  v-if="tab.manage">
                <i class="icon-download-outline"></i>
                Nouvelle Version
              </button>
              -->
              <button v-on:click="handlerNewVersion(doc)" class="btn btn-default btn-xs"  v-if="tab.manage && doc.location != 'url'">
                <i class="icon-download-outline"></i>
                Nouvelle Version
              </button>
              <a class="btn btn-default btn-xs" @click.prevent="deleteDocument(doc)" v-if="tab.manage">
                <i class="icon-trash"></i>
                Supprimer
              </a>
              <a class="btn btn-xs btn-default" href="#" @click.prevent="handlerEdit(doc)" v-if="tab.manage && doc.location != 'url'">
                <i class="icon-pencil"></i>
                Modifier
              </a>
            </nav>
          </div>
        </article>
      </div>
    </section>
  </section>
</template>
<script>

/******************************************************************************************************************/
/* ! DEVELOPPEURS
Depuis la racine OSCAR :
cd front

Pour compiler en temps réél :
node node_modules/.bin/vue-cli-service build --name ActivityDocument --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib ./src/ActivityDocument.vue --watch
 */

import Datepicker from "./components/Datepicker";
import AjaxOscar from "./remote/AjaxOscar";
import OscarRemoteData from "./remote/OscarRemoteData";
import PersonAutoCompleter from "./components/PersonAutoCompleter";
import axios from "axios";

let oscarRemoteData = new OscarRemoteData();
// Traitement spécifique de l'onglet Privé
const PRIVATE = "private";

export default {

  components: {
    "ajax-oscar": AjaxOscar,
    "date-picker": Datepicker,
    "person-auto-completer": PersonAutoCompleter
  },

  props: {
    urlUploadNewDoc: {required: true},
    url: {required: true},
    documentTypes: {required: true},
    urlDocumentType: {required: true},
    moment: {require: true},
    computedDocuments: { requires: false, default: [] }
  },

  data() {
    return {
      idCurrentPerson : null,
      // Formulaire Upload champs de formulaire
      persons: [],
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
      remoterState: oscarRemoteData.state,
      displayComputed: false,

      mode_url: false,

      // Onglet active
      selectedTab: null,
      selectedTabId: null
    }
  },

  computed: {
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
      };
      return out;
    },


    packedDocuments(){
      let packed = {};

      if( this.tabsWithDocuments ){
        for( const [i, tab] of Object.entries(this.tabsWithDocuments) ){
          let documents = {};
          for( const [j, doc] of Object.entries(tab.documents) ){
            let docKey = doc.fileName;
            if( !documents.hasOwnProperty(docKey) ){
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
    activeTab(tabId) {
      // Affectation valeur du tab dans lequel on se trouve
      this.tabId = tabId;
    },
    // Suppression Doc
    deleteDocument(document) {
      this.deleteData = document;
    },
  // TODO ordre des docs revoir avec Jack
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

    // Modification d'un document
    handlerEdit(document) {
      console.log(JSON.parse(JSON.stringify(document)));
      let valueTabDocument = document.tabDocument;
      if (valueTabDocument === null || valueTabDocument === undefined || valueTabDocument.trim === ''){
        valueTabDocument = PRIVATE;
      }else{
        valueTabDocument = valueTabDocument.id;
      }
      this.persons = [];
      document.persons.forEach( (p) =>{
        this.persons.push(p);
      });
      this.editData = {
        'documentype_id': document.category.id,
        'basename': document.basename,
        'document': document,
        'tabDocument_id': valueTabDocument,
        'private': document.private
      };
    },

    // Event Change sur composant "person-auto-completer",
    // pour hydrater tableau de la liste des personnes pour document privé
    handlerSelectPersons(person) {
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
        this.persons.push(personSelected);
      }
    },

    // Suppression de la personne dans le tableau des personnes
    handlerDeletePerson(person) {
      this.persons.splice(this.persons.indexOf(person), 1);
    },



    /**
     Déclenche ouverture Modal Upload nouveau document initialise datas/reset et affectation de base
     Important surtout dans le scénario de l'ouverture modal avec modification datas et fermeture de la modal,
     réouverture de cette modal sans avoir soumis la première fois
     */
    handlerUploadNewDoc(tabId) {
      this.uploadDoc = true;
      //console.log ("JE PASSE PAR VERSION INITIALE UPLOAD : handlerUploadNewDoc(tabId)");
      //Hydratation de l'url de soumission complétée (propre à cet objet)
      this.uploadNewDocData.baseUrlUpload = this.urlUploadNewDoc + '/' + tabId;
      //Datas communes sous traite à une méthode commune (méthode "privée" nb : pas possible en JS)
      this.initUploadDatas(tabId);
    },


    handlerNewVersion( document ){
      console.log(JSON.parse(JSON.stringify(document)));

      this.uploadDoc = true;
      this.uploadNewDocData.baseUrlUpload =  document.urlReupload;
      this.selectedIdTypeDocument = this.uploadNewDocData.selectedIdTypeDocument =  document.category.id;
      this.uploadNewDocData.tab = document.tabDocument.id;
      this.privateDocument = this.uploadNewDocData.private = document.private;
      this.informationsDocument = '';
      this.persons = [];
      document.persons.forEach(p => {
        this.persons.push(p);
      })
      this.uploadNewDocData.init = true;
      this.fileToDownload = null;
    },

    /**
     Déclenche ouverture Modal Upload nouvelle version document initialise datas/reset et affectation de base différentes
     *
     * @param tabId
     * @param urlReupload
     */
    handlerUploadNewVersionDoc(tabId, urlReupload, typeId) {
      //console.log ("JE PASSE PAR NOUVELLE VERSION DE DOCUMENT : handlerUploadNewVersionDoc(tabId, urlReupload)");
      this.uploadDoc = true;
      //Hydratation de l'url de soumission complétée (propre à cet objet)
      this.uploadNewDocData.baseUrlUpload =  urlReupload;
      this.selectedIdTypeDocument = typeId;

      //Datas communes sous traite à une méthode commune (méthode "privée" nb : pas possible en JS)
      this.initUploadDatas(tabId, typeId);
    },

    // FAIT OFFICE DE METHODE PSEUDO PRIVEE TRAITEMENT SIMILAIRE (nouveau doc, nouvelle version de doc)
    initUploadDatas(tabId, typeId = null){
      this.dateDeposit = '';
      this.dateSend = '';
      let privateTab = false;
      if (tabId === PRIVATE){
        privateTab = true;
      }
      this.privateDocument = privateTab;
      this.selectedIdTypeDocument = typeId;
      this.informationsDocument = '';
      this.fileUrl = this.uploadNewDocData.url = '';
      this.fileUrlLabel = '';
      this.persons = [];
      // initialise objet de base
      this.uploadNewDocData.init = true;
      // Affectation valeur par défaut champ fichier lié au contexte de l'onglet choisi (tab)
      this.fileToDownload = null;
      // Tab choisis pour upload document (TabId est égal id onglet)
      this.uploadNewDocData.tab = tabId;
    },

    // Event onChange sur le champ INPUT FILE
    uploadFile(event) {
      if (event.target.files.length === 0) {
        return;
      }
      this.fileToDownload = event.target.files[0];
    },

    // Méthode Upload soumission formulaire téléversement nouveau Document ("submit button")
    performUpload() {
      // Match datas de l'objet pour le formulaire Upload
      this.uploadNewDocData.dateDeposit = this.dateDeposit;
      this.uploadNewDocData.dateSend = this.dateSend;
      this.uploadNewDocData.private = (this.privateDocument === true) ? "1" : "0";
      this.uploadNewDocData.type = this.selectedIdTypeDocument;
      this.uploadNewDocData.informations = this.informationsDocument;

      let idsPersons = [];
      if (this.persons.length !== 0) {
        this.persons.forEach(function (p) {
          idsPersons.push(p.personId);
        });
      }
      this.uploadNewDocData.persons = idsPersons;

      // Téléversement Nouveau Document Formulaire JS
      const fd = new FormData();
      // Hydratation formulaire (clef/valeurs) de base
      for (let key in this.uploadNewDocData) {
        let value = this.uploadNewDocData[key];
        fd.append(key, value);
      }

      if( this.mode_url ){
        fd.append('url', this.fileUrl);
        fd.append('label_url', this.fileUrlLabel);
      } else {
        // Document file
        if (this.fileToDownload !== null) {
          fd.append('file', this.fileToDownload, this.fileToDownload.name);
        } else {
          this.errorMessages.push("Aucun fichier sélectionner a téléverser !");
        }
      }

      if (this.uploadNewDocData.type === null) {
        this.errorMessages.push("Vous devez qualifier le type de votre document !");
      }
      if(this.errorMessages.length !==0){
        return;
      }
      this.uploadDoc = null;
      this.persons = [];
      // Objet JS Appel Ajax
      oscarRemoteData
          .setPendingMessage("Téléversement nouveau document")
          .setErrorMessage("Impossible de téléverser le document")
          .performPost(
              this.uploadNewDocData.baseUrlUpload,
              fd,
              (response) => {
                this.persons = [];
                this.fetch();
              });
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
      if(this.editData.private === true){
        privateBool = 1;
        if (this.persons.length !== 0){
          this.persons.forEach( (p)=>{
            persons.push(p.personId);
          });
        }
      }else {
        //console.log("VALEUR this.editData.tabDocument_id : ",this.editData.tabDocument_id);
        privateBool = 0;
        // Mauvais statut Onglet (soit statut défaut soit aucune valeur)
        if (this.editData.tabDocument_id === PRIVATE || this.editData.tabDocument_id === ""){
          this.errorMessages.push("Vous devez sélectionner un onglet pour la modification (ce n'est pas un document qualifié privé)");
        }else{
          newTabDoc = this.editData.tabDocument_id;
        }
      }
      // Fenêtre de message d'erreur
      if (this.errorMessages.length !== 0){
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
      // Objet JS Appel Ajax
      oscarRemoteData
          .setPendingMessage("Modification du type de document")
          .setErrorMessage("Impossible de modifier le type de document")
          .performPost(this.urlDocumentType, formData, (response) => {
            this.fetch();
          });
    },

    // Méthode appelée lors de l'appel via la méthode fetch démarrage du module
    handlerSuccess(success) {
      this.idCurrentPerson = success.data.idCurrentPerson;
      let documents = success.data.tabsWithDocuments;
      let defaultTab = null;
      let selectedTab = null;

      Object.keys(documents).forEach( (item) => {
        let tab = documents[item];
        tab.total = tab.documents.length;
        tab.documents.sort( (x,y) => y.version - x.version );
        tab.documents.forEach(item => {
          console.log(item.fileName, item.version);
          item.explode = true;
        })
        if( defaultTab == null ) defaultTab = tab.id;
        if( selectedTab == null && tab.documents.length > 0 ){
          selectedTab = tab.id;
          //browsers.sort((x, y) => x.year - y.year);
        }
      });
      this.tabsWithDocuments = documents;
      if( this.selectedTabId == null ){
        this.selectedTabId = selectedTab ? selectedTab : defaultTab;
      }
      //   // if( this.tabsWithDocuments[i].documents.length ){
      //   //   this.selectedTabId = this.tabsWithDocuments[i].id;
      //   // }
      // }

      if( this.tabsWithDocuments.unclassified && this.tabsWithDocuments.unclassified.documents.length ){
        this.selectedTab = this.tabsWithDocuments.unclassified;
      }
      else {
        let keys = Object.keys(this.tabsWithDocuments)[0];
        if( keys.length ){
          this.selectedTab = this.tabsWithDocuments[keys[0]];
        }
      }
      // Voir avec Jack est-ce que l'on remet l'ordre des documents ? Et si oui comment ?
      /*
      let data = success.data.datas;
      let documentsOrdered = [];
      let documents = {};

      data.forEach(function (doc) {
        doc.categoryText = doc.category ? doc.category.label : "";
        doc.explode = true;
        //var filename = doc.fileName;
        let filename = doc.fileName;
        if (!documents[filename]) {
          documents[filename] = doc;
          documents[filename].previous = [];
          documentsOrdered.push(doc);
        } else {
          documents[filename].previous.push(doc);
        }
      });
      this.documents = documentsOrdered;
      */
    },

    handlerSelectTab(tab){
      if( tab == "computed" ){
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
      // Object JS Ajax
      oscarRemoteData
          .setPendingMessage("Chargement des documents")
          .setErrorMessage("Impossible de charger les documents")
          .performGet(this.url, this.handlerSuccess);
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

.step {
  flex: 1;
  padding: 1em;
  text-align: left;
  border: thin solid #aaa;
}

.step:first-child {
  border-radius: .5em 0 0 0;
}

.step:last-child {
  border-radius: 0 .5em 0 0;
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

.stepHandler{
  border-radius: 18px;
  outline: 0;
  padding: 8px 12px;
  text-align: center;
  transition: all 0.3s ease-out;
  display: inline-block;
  margin-left: 100px;
}

.stepHandler:before{
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
