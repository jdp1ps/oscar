<template>
  <!-- MODAL DE SUPPRESSION DE DOCUMENT -->
  <section style="position: relative; min-height: 100px">
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
          Modification du document
          <span class="overlay-closer" @click="editData = null">X</span>
        </h2>

        <label for="typedocument">Type de document</label>
        <div>
          <select name="type" id="typedocument" v-model="editData.documentype_id">
            <option :value="id" v-for="(t, id) in documentTypes" :key="id">{{ t }}</option>
          </select>
        </div>
        <!--
        <label for="filename">Nom du fichier</label>
        <p class="help">
            Il s'agit du nom du fichier par défaut lors du téléchargement. Le nom d'archivage ne sera pas modifié.
        </p>
        <input type="text" id="filename" class="form-control" v-model="editData.basename" />
        -->
        <button class="btn btn-danger" @click="editData = null">
          <i class="icon-cancel-alt"></i> Annuler
        </button>

        <a class="btn btn-success" href="#" @click.prevent="performEdit()">
          <i class="icon-valid"></i> Enregistrer
        </a>
      </div>
    </div>
    <!-- ################################################### -->

    <!-- MODAL DE TÉLÉVERSEMENT D'UN NOUVEAU DOCUMENT TODO WORK IN PROGRESS -->
    <div class="overlay" v-if="uploadNewDocData.init">
      <div class="overlay-content">
        <h1>
          <!-- Informations de débug -->
          {{ uploadNewDocData }}
        </h1>
        <h2>
          Téléverser un nouveau document
          <span class="overlay-closer" @click="uploadNewDocData.init = null">X</span>
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

            <!-- INFORMATIONS COMPLEMENTAIRES -->
            <div class="row">
              <div class="col-md-12">
                <label for="informations">Note</label>
                <textarea v-model="informationsDocument" name="informations" id="informations" class="form-control"
                          cols="30" rows="10"></textarea>
              </div>
            </div>
            <button class="btn btn-danger" @click="uploadNewDocData.init = null">
              <i class="icon-cancel-alt"></i> Annuler
            </button>
            <a class="btn btn-success" href="#" @click.prevent="performUpload()">
              <i class="icon-valid"></i> Enregistrer
            </a>
          </div>
        </div>
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
/* ! DEVELOPPEUR

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

// test ? TODO HM C'est quoi ça Jack ?
let oscarRemoteData = new OscarRemoteData();

function flashMessage() {
  // TODO pas implémenté ? HM ça sert à rien ça Jack ?
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
      fileDocument: null,
      // Objet hydraté selon contexte et envoyé lors d'un téléversement d'un nouveau document
      uploadNewDocData: {
        'dateDeposit': this.dateDeposit,
        'dateSend': this.dateSend,
        'private': this.privateDocument,
        'type': this.selectedIdTypeDocument,
        'tab': this.selectedIdTabDocument,
        'informations': this.informationsDocument,
        'file': this.fileDocument,
        'persons': this.persons,
        'baseUrlUpload': this.urlUploadNewDoc,
        'init': false
      },

      tabId: null,
      // Données des documents par ID_onglets (idTab)
      tabsWithDocuments: null,
      // Formulaire de soumission téléversement nouveau document
      formData: null,
      error: null,
      deleteData: null,
      editData: null,
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
      return this.tabId;
    }
  },

  methods: {
    // Event onChange sur le champ INPUT FILE
    uploadFile(event) {
      if (event.target.files.length === 0) {
        //console.log("Pas de fichier pour l'upload return ", event.target.files.length);
        return;
      }
      this.fileDocument = event.target.files[0];
      //console.log("Fichier affecté variable this.fileDocument !", this.fileDocument);
      this.uploadNewDocData.file = this.fileDocument;
      //console.log("Passage en référence à la propriété de l'objet global JSON qui servira lors de la soumission du Form upload", this.uploadNewDocData.file);
    },

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
      //console.log(document);
      this.editData = {
        'documentype_id': document.category.id,
        'basename': document.basename,
        'document': document
      };
    },

    // Event Change sur composant pour hydrater tableau de la liste des personnes pour document privé
    handlerSelectPersons(person) {
      //console.log(person);
      //console.log(arguments);
      //console.log(person.displayname);
      //console.log(person.id);
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
      //console.log("personne : ", p);
      this.persons.splice(this.persons.indexOf(person), 1);
      //console.log("values this.persons : ", this.persons);
    },

    /**
     Déclenche ouverture Modal Upload nouveau document initialise datas/reset et affectation de base
     Important surtout dans le scénario de l'ouverture modal avec modification datas et fermeture de la modal,
     réouverture de cette modal sans avoir soumis la première fois
    */
    handlerUploadNewDoc(tabId) {
      this.dateDeposit = '';
      this.dateSend = '';
      this.privateDocument = false;
      this.selectedIdTypeDocument = null;
      this.informationsDocument = '';
      this.fileDocument = null;
      this.persons = [];
      // Permet affichage Modal test booléen et initialise objet de base
      this.uploadNewDocData.init = true;
      // Affectation valeur par défaut champ fichier lié au contexte de l'onglet choisi (tab)
      this.uploadNewDocData.file = null;
      // Tab choisis pour upload document (TabId est égal id onglet)
      this.uploadNewDocData.tab = tabId;
      // Hydratation de l'url de soumission complétée (propre à cet objet)
      this.uploadNewDocData.baseUrlUpload = this.urlUploadNewDoc + '/' + tabId;
    },

    // Méthode Upload soumission formulaire téléversement nouveau Document ("submit button")
    performUpload() {
      // Binding datas de l'objet pour le formulaire Upload
      this.uploadNewDocData.dateDeposit = this.dateDeposit;
      this.uploadNewDocData.dateSend = this.dateSend;
      this.uploadNewDocData.private = this.privateDocument;
      this.uploadNewDocData.type = this.selectedIdTypeDocument;
      this.uploadNewDocData.file = this.fileDocument;
      this.uploadNewDocData.informations = this.informationsDocument;

      // Téléversement Nouveau Document Formulaire JS
      let formData = new FormData();
      // Hydratation formulaire (clef/valeurs)
      for (let key in this.uploadNewDocData) {
        let value = this.uploadNewDocData[key];
        formData.append(key, value);
      }

      // TODO à supprimer, affichage pour débug des paires clefs/valeurs du Form
      for (let pair of formData.entries()) {
        let key = pair[0];
        let valueObject = pair[1];
        console.log("################################ DEBUT CLEFS/VALEURS FORM " + key + " ###################################");
        console.log("Clef form : ", key);
        console.log("Valeur form : ", valueObject);
        console.log("################################# END CLEFS/VALEURS FORM " + valueObject + " ####################################");
      }
      console.log("################################ URL DE SOUMISSION DU FORMULAIRE ###################################");
      console.log("URL DE SOUMISSION = ", this.uploadNewDocData.baseUrlUpload);
      // ################# DEV en cours ############################"
      // Objet JS Appel Ajax
      /*oscarRemoteData
          .setPendingMessage("Téléversement nouveau document")
          .setErrorMessage("Impossible de téléverser le document")
          .performPost(
              this.urlUploadNewDoc,
              formData,
              (response) => {
              this.fetch();
          });*/
    },

    // Modification du type de document
    performEdit() {
      let documentId = this.editData.document.id;
      let newType = this.editData.documentype_id;
      this.editData = null;
      let formData = new FormData();
      formData.append('documentId', documentId);
      formData.append('type', newType);
      oscarRemoteData
          .setPendingMessage("Modification du type de document")
          .setErrorMessage("Impossible de modifier le type de document")
          .performPost(this.urlDocumentType, formData, (response) => {
            this.fetch();
          });
    },

    // Méthode appelée lors de l'appel via la méthode fetch démarrage du module
    handlerSuccess(success) {
      let data = success.data.datas;
      let tabsObjectsDocuments = success.data.tabsWithDocuments;
      this.tabsWithDocuments = tabsObjectsDocuments;
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
