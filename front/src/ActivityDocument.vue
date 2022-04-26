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
    <!--###################################################-->

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
                  <option :value="id" v-for="(t, id) in documentTypes" :key="id">{{ t }}</option>
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
                    <option :value="id" v-for="(tabDoc, id) in tabsWithDocuments" :key="id">{{ tabDoc.label }}</option>
                  </select>
                </div>
              </span>
                <div class="col-md-6">
                  <label for="private">Document privé</label>
                </div>
                <div class="col-md-6">
                  <input type="checkbox" name="private" id="privateModifDoc" class="form-control" v-model="editData.private">
                </div>
              </div>
              <span v-if="editData.private === true">
                 <label>Choix des personnes ayant accès à ce document</label>
                <h3>Ce document sera classé automatiquement dans l'onglet privé</h3>
                  <person-auto-completer @change="handlerSelectPersons"></person-auto-completer>
                  <span v-if="persons.length !== 0" v-for="p in persons" :key="p.personId" class="cartouche">
                    <i class="icon-cube"></i>
                    <span>{{ p.personName }}</span>
                    <span v-if="p.affectation.trim() !=''" class="addon">
                      {{ p.affectation }}<i @click="handlerDeletePerson(p)" class="icon-trash icon-clickable"></i>
                    </span>
                    <i v-if="p.affectation.trim() ===''" @click="handlerDeletePerson(p)" class="icon-trash icon-clickable"></i>
                  </span>
              </span>
            </div>
          </div>
        <!--
        <label for="filename">Nom du fichier</label>
        <p class="help">
        Il s'agit du nom du fichier par défaut lors du téléchargement. Le nom d'archivage ne sera pas modifié.
        </p>
        <input type="text" id="filename" class="form-control" v-model="editData.basename" />
        -->
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
    <!-- ################################################### -->

    <!-- MODAL DE TÉLÉVERSEMENT D'UN NOUVEAU DOCUMENT -->
    <div class="overlay" v-if="uploadDoc">
      <div class="overlay-content">
        <h2>
          Téléverser un nouveau document
          <span class="overlay-closer" @click="uploadDoc = null">X</span>
        </h2>
        <div>
          <div class="row">
            <div class="col-md-6">
              <!-- Fichier upload -->
              <label for="file">Fichier</label>
              <input @change="uploadFile" type="file" class="form-control" name="file" id="file"/>
              <!-- Date de dépot -->
              <label>Date de dépôt</label>
              <p class="help">Date à laquelle le fichier a été reçu</p>
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
              <div class="row" style="margin-top: 20px;">
                <div class="col-md-6">
                  <label for="private">Document privé</label>
                </div>
                <div class="col-md-6">
                  <input type="checkbox" name="private" id="private" class="form-control" v-model="privateDocument">
                </div>
              </div>
              <span v-if="privateDocument === true">
                <h4>Ce document sera classé automatiquement dans l'onglet privé</h4>
                 <label>Choix des personnes ayant accès à ce document</label>
                  <person-auto-completer @change="handlerSelectPersons"></person-auto-completer>
                  <span v-if="persons.length !== 0" v-for="p in persons" :key="p.personId" class="cartouche">
                    <i class="icon-cube"></i>
                    <span>{{ p.personName }}</span>
                    <span v-if="p.affectation.trim() !=''" class="addon">
                      {{ p.affectation }}<i @click="handlerDeletePerson(p)" class="icon-trash icon-clickable"></i>
                    </span>
                    <i v-if="p.affectation.trim() ===''" @click="handlerDeletePerson(p)" class="icon-trash icon-clickable"></i>
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
            <button class="btn btn-danger" @click="uploadDoc = null">
              <i class="icon-cancel-alt"></i> Annuler
            </button>
            <a class="btn btn-success" href="#" @click.prevent="performUpload()">
              <i class="icon-valid"></i> Enregistrer
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE MESSAGE D'ERREUR NOUVEAU DOCUMENT -->
    <div class="overlay" v-if="message">
      <div class="overlay-content">
        <h2>
          <span class="overlay-closer" @click="message = null">X</span>
        </h2>
        <h3>
          {{ message }}
        </h3>
        <button class="btn btn-danger" @click="message = null">
          <i class="icon-cancel-alt"></i> Annuler
        </button>
      </div>
    </div>


    <!-- Barre de tri des documents -->
    <div>
      <div class="oscar-sorter">
        <i class=" icon-sort"></i>
        Tier les résultats par :
        <a @click.prevent="order('dateUpload')" href="#" :class="cssSort('dateUpload')" class="oscar-sorter-item">
          Date d'upload
          <i class="icon-angle-down" v-show="sortDirection == 1"></i>
          <i class="icon-angle-up" v-show="sortDirection == -1"></i>
        </a>
        <a @click.prevent="order('fileName')" href="#" :class="cssSort('fileName')" class="oscar-sorter-item">
          Nom du fichier
          <i class="icon-angle-down" v-show="sortDirection == 1"></i>
          <i class="icon-angle-up" v-show="sortDirection == -1"></i>
        </a>
        <a @click.prevent="order('categoryText')" href="#" :class="cssSort('categoryText')" class="oscar-sorter-item">
          Type de document
          <i class="icon-angle-down" v-show="sortDirection == 1"></i>
          <i class="icon-angle-up" v-show="sortDirection == -1"></i>
        </a>
      </div>
    </div>


    <!-- ############################### Informations par document / Onglet associé ############################### -->
    <div v-for="tab in tabsWithDocuments" :key="tab.id">
      <nav class="admin-bar">
          <span style="cursor:pointer;" v-on:click="activeTab(tab.id)" class="cartouche primary">
              <i class="picto icon-doc"></i>{{ tab.label }}
              <span v-on:click="handlerUploadNewDoc(tab.id)" class="addon">
                 Téléverser un document<i class="icon-book icon-clickable"></i>
              </span>
            <span v-if="isTabActive === tab.id">
              &nbsp;<i class="icon-flag"></i>
            </span>
          </span>
      </nav>
      <!--v-for="document in documentsPacked" :key="document.id">-->
      <article v-if="isTabActive === tab.id" class="card xs" v-for="doc in tab.documents" :key="doc.id">
        <div class="card-title">
          <i class="picto icon-doc" :class="'doc' + doc.extension"></i>
          <small class="text-light">{{ doc.categoryText }} ~ </small>
          <strong>{{doc.fileName}}</strong>
          <small class="text-light" :title="doc.fileSize + ' octet(s)'">&nbsp;({{doc.fileSize | filesize}})</small>
        </div>
        <p>
          {{ doc.information }}
        </p>
        <div class="card-content">
          <p class="text-highlight">
            Fichier <strong>{{ doc.extension}}</strong>
            version {{ doc.version }},
            téléversé le
            <time>{{ doc.dateUpload | dateFull }}</time>
            <span v-if="doc.uploader"> par <strong>{{ doc.uploader.displayname }}</strong></span>
          </p>
          <!--
          <div class="exploder" v-if="doc.previous.length" @click="doc.explode = !doc.explode">
            Versions précédentes <i class="icon-angle-down" v-show="!doc.explode"></i>
            <i class="icon-angle-up" v-show="doc.explode"></i>
          </div>

          <div v-if="doc.previous.length" v-show="doc.explode">
            <article v-for="sub in doc.previous" class="subdoc text-highlight" :key="sub.id">
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
          -->
          <nav class="text-right show-over">
            <a class="btn btn-default btn-xs" :href="doc.urlDownload" v-if="doc.urlDownload">
              <i class="icon-upload-outline"></i>
              Télécharger
            </a>

            <a class="btn btn-default btn-xs" :href="doc.urlReupload" v-if="doc.urlReupload">
              <i class="icon-download-outline"></i>
              Nouvelle version
            </a>

            <a class="btn btn-default btn-xs" @click.prevent="deleteDocument(doc)">
              <i class="icon-trash"></i>
              Supprimer
            </a>
            <a class="btn btn-xs btn-default" href="#" @click.prevent="handlerEdit(doc)">
              <i class="icon-pencil"></i>
              Modifier
            </a>
          </nav>
        </div>
      </article>
    </div>

    <!-- TODO travail en cours sur la partie documents privés -->

    <div class="steps-bar">
      <div style="cursor: pointer;" v-if="tabPrivate" @click="openTabPrivate = !openTabPrivate" class="step done"> Documents Privés </div>
      <!--<div class="step current"> Vérifier les affectations </div>-->
    </div>
    <div class="step-content" v-if="openTabPrivate === true">
      <article class="card xs" v-for="docP in tabPrivate.documents" :key="docP.id">
        <!--v-for="document in documentsPacked" :key="document.id">-->
        <!--<article v-if="isTabActive === tp.id" class="card xs" v-for="docP in tp.documents" :key="docP.id">-->
          <div class="card-title">
            <i class="picto icon-doc" :class="'doc' + docP.extension"></i>
            <small class="text-light">{{ docP.categoryText }} ~ </small>
            <strong>{{docP.fileName}}</strong>
            <small class="text-light" :title="docP.fileSize + ' octet(s)'">&nbsp;({{docP.fileSize | filesize}})</small>
          </div>
          <p>
            {{ docP.information }}
          </p>
        <h5 v-if="docP.persons.length > 0">Personnes accédants à ces documents</h5>
        <span class="cartouche"  v-for="person in docP.persons" :key="person.id">
            {{ person.fullName }}
        </span>

        <div class="card-content">
          <p class="text-highlight">
            Fichier <strong>{{ docP.extension}}</strong>
            version {{ docP.version }},
            téléversé le
            <time>{{ docP.dateUpload | dateFull }}</time>
            <span v-if="docP.uploader"> par <strong>{{ docP.uploader.displayname }}</strong></span>
          </p>
        </div>
      </article>
    </div>

    <!-- Section boucle documents Originelle JACK -->
    <!--
      <article class="card xs" v-for="document in documentsPacked" :key="document.id">
          <div class="card-title">
              <i class="picto icon-doc" :class="'doc' + document.extension"></i>
              <small class="text-light">{{ document.categoryText }} ~ </small>
              <strong>{{document.fileName}}</strong>
              <small class="text-light" :title="document.fileSize + ' octet(s)'">&nbsp;({{document.fileSize | filesize}})</small>
          </div>
          <p>
              {{ document.information }}
          </p>
          <div class="card-content">
              <p class="text-highlight">
                  Fichier <strong>{{ document.extension}}</strong>
                  version {{ document.version }},
                  téléversé le <time>{{ document.dateUpload | dateFull }}</time>
                  <span v-if="document.uploader">
                      par <strong>{{ document.uploader.displayname }}</strong>
                  </span>
              </p>
              <div class="exploder" v-if="document.previous.length" @click="document.explode = !document.explode">
                  Versions précédentes <i class="icon-angle-down" v-show="!document.explode"></i>
                  <i class="icon-angle-up" v-show="document.explode"></i>
              </div>
              <div v-if="document.previous.length" v-show="document.explode">
                  <article v-for="sub in document.previous" class="subdoc text-highlight" :key="sub.id">
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
                  <a class="btn btn-default btn-xs" :href="document.urlDownload" v-if="document.urlDownload">
                      <i class="icon-upload-outline"></i>
                      Télécharger
                  </a>

                  <a class="btn btn-default btn-xs" :href="document.urlReupload" v-if="document.urlReupload">
                      <i class="icon-download-outline"></i>
                      Nouvelle version
                  </a>

                  <a class="btn btn-default btn-xs" @click.prevent="deleteDocument(document)">
                      <i class="icon-trash"></i>
                      Supprimer
                  </a>
                  <a class="btn btn-xs btn-default" href="#" @click.prevent="handlerEdit(document)">
                      <i class="icon-pencil"></i>
                      Modifier
                  </a>
              </nav>
          </div>
      </article>
      -->
  </section>
</template>
<script>

/******************************************************************************************************************/
/* ! DEVELOPPEURS

Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_modules/.bin/gulp activityDocumentWatch
node node_modules/.bin/vue-cli-service build --name ActivityDocument --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib ./src/ActivityDocument.vue --watch

Pour compiler :
node node_modules/.bin/gulp activityDocument
 */

import Datepicker from "./components/Datepicker";
import AjaxOscar from "./remote/AjaxOscar";
import OscarRemoteData from "./remote/OscarRemoteData";
import PersonAutoCompleter from "./components/PersonAutoCompleter";
import axios from "axios";

let oscarRemoteData = new OscarRemoteData();

function flashMessage() {
  // TODO pas implémenté ? HM => SB ça sert à rien ça Jack ?
}

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
    moment: {require: true}
  },

  data() {
    return {
      // Formulaire Upload champs de formulaire
      persons: [],
      dateDeposit: '',
      dateSend: '',
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
        'init': false
      },
      // Documents privés
      tabPrivate: null,
      openTabPrivate:false,
      // Message boite modal pour l'utilisateur (erreurs pour exemple)
      message: null,
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
      remoterState: oscarRemoteData.state
    }
  },

  computed: {
    /**
     * Retourne les documents triés.
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
    // Pour afficher les documents selon IdOnglet (idTab)
    isTabActive() {
      this.openTabPrivate = false;
      return this.tabId;
    }
  },

  methods: {
    activeTab(tabId) {
      // Affectation valeur du tab dans lequel on se trouve
      this.tabId = tabId;
    },

    deleteDocument(document) {
      this.deleteData = document;
    },

    order: function (field) {
      if (this.sortField == field) {
        this.sortDirection *= -1;
      } else {
        this.sortField = field;
      }
    },

    cssSort: function (compare) {
      return compare == this.sortField ? "active" : "";
    },

    handlerEdit(document) {
      console.log(document);
      this.editData = {
        'documentype_id': document.category.id,
        'basename': document.basename,
        'document': document,
        'tabDocument_id': document.tabDocument.id,
        'private': document.private
      };
    },

    // Event Change sur composant pour hydrater tableau de la liste des personnes pour document privé
    handlerSelectPersons(person) {
      let personSelected = {
        "personName": person.displayname,
        "personId": person.id,
        "affectation": person.affectation
      };
      let comparePersonId = person.id;
      let isPresent = false;
      this.persons.forEach(function(person){
        if(person.personId === comparePersonId){
          isPresent = true;
        }
      });
      if (false === isPresent){
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
      this.dateDeposit = '';
      this.dateSend = '';
      this.privateDocument = false;
      this.selectedIdTypeDocument = null;
      this.informationsDocument = '';
      this.persons = [];
      // initialise objet de base
      this.uploadNewDocData.init = true;
      // Affectation valeur par défaut champ fichier lié au contexte de l'onglet choisi (tab)
      this.fileToDownload = null;
      // Tab choisis pour upload document (TabId est égal id onglet)
      this.uploadNewDocData.tab = tabId;
      // Hydratation de l'url de soumission complétée (propre à cet objet)
      this.uploadNewDocData.baseUrlUpload = this.urlUploadNewDoc + '/' + tabId;
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
      // Binding datas de l'objet pour le formulaire Upload
      this.uploadNewDocData.dateDeposit = this.dateDeposit;
      this.uploadNewDocData.dateSend = this.dateSend;
      this.uploadNewDocData.private = (this.privateDocument === true)?"1":"0";
      this.uploadNewDocData.type = this.selectedIdTypeDocument;
      this.uploadNewDocData.informations = this.informationsDocument;

      let idsPersons = [];
      if (this.persons.length !== 0){
        this.persons.forEach(function(p){
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
      // Document file
      if (this.fileToDownload !== null){
        fd.append('file', this.fileToDownload, this.fileToDownload.name);
      }else{
        this.message = "Aucun fichier sélectionner a téléverser !";
        return;
      }
      if (this.uploadNewDocData.type === null){
        this.message = "Vous devez qualifier le type de votre document !";
        return;
      }
      this.uploadDoc = null;
      // Objet JS Appel Ajax
      oscarRemoteData
          .setPendingMessage("Téléversement nouveau document")
          .setErrorMessage("Impossible de téléverser le document")
          .performPost(
              this.uploadNewDocData.baseUrlUpload,
              fd,
              (response) => {
                this.fetch();
              });
    },

    // Modification du type de document / changement onglet
    performEdit() {
      let documentId = this.editData.document.id;
      let newType = this.editData.documentype_id;
      let newTabDoc = this.editData.tabDocument_id;
      this.editData = null;
      let formData = new FormData();
      formData.append('documentId', documentId);
      formData.append('type', newType);
      formData.append('tabDocument', newTabDoc);
      oscarRemoteData
          .setPendingMessage("Modification du type de document")
          .setErrorMessage("Impossible de modifier le type de document")
          .performPost(this.urlDocumentType, formData, (response) => {
            this.fetch();
          });
    },

    // Méthode appelée lors de l'appel via la méthode fetch démarrage du module
    handlerSuccess(success) {
      this.tabPrivate = success.data.tabPrivate;
      this.tabsWithDocuments = success.data.tabsWithDocuments;
      let data = success.data.datas;
      let documentsOrdered = [];
      let documents = {};

      data.forEach(function (doc) {
        doc.categoryText = doc.category ? doc.category.label : "";
        doc.explode = true;
        var filename = doc.fileName;
        if (!documents[filename]) {
          documents[filename] = doc;
          documents[filename].previous = [];
          documentsOrdered.push(doc);
        } else {
          documents[filename].previous.push(doc);
        }
      });
      this.documents = documentsOrdered;
      //console.log(this.documents);
    },

    fetch() {
      // Object JS Ajax
      oscarRemoteData
          .setPendingMessage("Chargement des documents")
          .setErrorMessage("Impossible de charger les documents")
          .performGet(this.url, this.handlerSuccess);
      console.log("Je suis appelé méthode fetch");
    }
  },

  mounted() {
    // Au chargement du module dans la page appel méthode initialisation -> fetch()
    this.fetch();
  }
}
</script>

<style scoped>

</style>
